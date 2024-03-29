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
use Innmind\Stream\Bidirectional;
use Innmind\Immutable\{
    Maybe,
    Sequence,
    Str,
    Predicate\Instance,
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
            $this->config->streamCapabilities(),
            $this->config->io(),
        )
            ->withCaseSensitivity(
                $this->config->filesystemCaseSensitivity(),
            );
        $this->mounted[$adapter] = $path->toString();

        return $adapter;
    }

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

    public function watch(Path $path): Ping
    {
        return ($this->watch)($path);
    }

    public function temporary(Sequence $chunks): Maybe
    {
        $temporary = $this
            ->config
            ->streamCapabilities()
            ->temporary()
            ->new();

        $temporary = $chunks->reduce(
            Maybe::just($temporary),
            static fn(Maybe $temporary, $chunk) => Maybe::all($temporary, $chunk)->flatMap(
                static fn(Bidirectional $temporary, Str $chunk) => $temporary
                    ->write($chunk->toEncoding(Str\Encoding::ascii))
                    ->maybe()
                    ->keep(Instance::of(Bidirectional::class)),
            ),
        );

        return $temporary
            ->map($this->config->io()->readable()->wrap(...))
            ->map(Content::io(...));
    }
}
