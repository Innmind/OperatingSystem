<?php
declare(strict_types = 1);

namespace Innmind\OperatingSystem\Filesystem;

use Innmind\OperatingSystem\{
    Filesystem,
    Config,
};
use Innmind\Filesystem\Adapter;
use Innmind\Url\Path;
use Innmind\Server\Control\Server\Processes;
use Innmind\TimeWarp\Halt;
use Innmind\FileWatch\{
    Ping,
    Factory,
    Watch,
};
use Innmind\Immutable\Maybe;

final class Generic implements Filesystem
{
    private Watch $watch;
    private Config $config;
    /** @var \WeakMap<Adapter, string> */
    private \WeakMap $mounted;

    private function __construct(
        Processes $processes,
        Halt $halt,
        Config $config,
    ) {
        $this->watch = Factory::build($processes, $halt);
        $this->config = $config;
        /** @var \WeakMap<Adapter, string> */
        $this->mounted = new \WeakMap;
    }

    public static function of(
        Processes $processes,
        Halt $halt,
        Config $config,
    ): self {
        return new self($processes, $halt, $config);
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

        $adapter = Adapter\Filesystem::mount($path, $this->config->streamCapabilities())
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
}
