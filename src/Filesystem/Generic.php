<?php
declare(strict_types = 1);

namespace Innmind\OperatingSystem\Filesystem;

use Innmind\OperatingSystem\{
    Filesystem,
    Config,
};
use Innmind\Filesystem\{
    Adapter,
    File\Content,
};
use Innmind\Url\Path;
use Innmind\Server\Control\Server\Processes;
use Innmind\FileWatch\{
    Ping,
    Factory,
    Watch,
};
use Innmind\Immutable\{
    Maybe,
    Attempt,
    Sequence,
};

final class Generic implements Filesystem
{
    private Watch $watch;
    private Config $config;
    /** @var \WeakMap<Adapter, string> */
    private \WeakMap $mounted;

    private function __construct(Processes $processes, Config $config)
    {
        $this->watch = Factory::build($processes, $config->halt());
        $this->config = $config;
        /** @var \WeakMap<Adapter, string> */
        $this->mounted = new \WeakMap;
    }

    /**
     * @internal
     */
    public static function of(Processes $processes, Config $config): self
    {
        return new self($processes, $config);
    }

    #[\Override]
    public function mount(Path $path): Adapter
    {
        /**
         * @var Adapter $adapter
         * @var string $mounted
         */
        foreach ($this->mounted as $adapter => $mounted) {
            if ($path->toString() === $mounted) {
                return $adapter;
            }
        }

        $adapter = Adapter\Filesystem::mount(
            $path,
            $this->config->io(),
        )
            ->withCaseSensitivity(
                $this->config->filesystemCaseSensitivity(),
            );
        $this->mounted[$adapter] = $path->toString();

        return $adapter;
    }

    #[\Override]
    public function contains(Path $path): bool
    {
        if (!\file_exists($path->toString())) {
            return false;
        }

        if ($path->directory() && !\is_dir($path->toString())) {
            return false;
        }

        return true;
    }

    #[\Override]
    public function require(Path $path): Maybe
    {
        $path = $path->toString();

        if (!\file_exists($path) || \is_dir($path)) {
            /** @var Maybe<mixed> */
            return Maybe::nothing();
        }

        /**
         * @psalm-suppress UnresolvableInclude
         * @psalm-suppress MixedArgument
         * @var Maybe<mixed>
         */
        return Maybe::just(require $path);
    }

    #[\Override]
    public function watch(Path $path): Ping
    {
        return ($this->watch)($path);
    }

    #[\Override]
    public function temporary(Sequence $chunks): Maybe
    {
        return Attempt::of(
            fn() => $this
                ->config
                ->io()
                ->files()
                ->temporary(
                    $chunks->map(
                        static fn($chunk) => $chunk
                            ->attempt(static fn() => new \RuntimeException('Failed to load chunk'))
                            ->unwrap(),
                    ),
                )
                ->memoize() // to make sure writing the chunks has been done
                ->map(static fn($tmp) => $tmp->read())
                ->map(Content::io(...))
                ->unwrap(),
        )->maybe();
    }
}
