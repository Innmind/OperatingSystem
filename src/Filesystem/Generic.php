<?php
declare(strict_types = 1);

namespace Innmind\OperatingSystem\Filesystem;

use Innmind\OperatingSystem\Filesystem;
use Innmind\Filesystem\Adapter;
use Innmind\Url\Path;
use Innmind\Server\Control\Server\Processes;
use Innmind\TimeWarp\Halt;
use Innmind\TimeContinuum\Clock;
use Innmind\FileWatch\{
    Ping,
    Factory,
};
use Innmind\Immutable\Maybe;

final class Generic implements Filesystem
{
    private Processes $processes;
    private Halt $halt;
    private Clock $clock;

    public function __construct(
        Processes $processes,
        Halt $halt,
        Clock $clock,
    ) {
        $this->processes = $processes;
        $this->halt = $halt;
        $this->clock = $clock;
    }

    public function mount(Path $path): Adapter
    {
        return Adapter\Filesystem::mount($path);
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
        return Factory::build($this->processes, $this->halt)($path);
    }
}
