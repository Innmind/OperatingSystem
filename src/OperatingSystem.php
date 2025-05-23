<?php
declare(strict_types = 1);

namespace Innmind\OperatingSystem;

use Innmind\OperatingSystem\OperatingSystem\{
    Implementation,
    Unix,
};
use Innmind\Server\Status\Server as ServerStatus;
use Innmind\Server\Control\Server as ServerControl;
use Innmind\TimeContinuum\Clock;

final class OperatingSystem
{
    private function __construct(
        private Implementation $implementation,
    ) {
    }

    public static function new(?Config $config = null): self
    {
        return new self(Unix::of($config));
    }

    /**
     * This method allows to change the underlying OS implementation while being
     * able to keep any decorators on top of it.
     *
     * @param callable(self, Config): self $map
     */
    public function map(callable $map): self
    {
        return new self($this->implementation->map(
            static fn($implementation, $config) => $map(
                new self($implementation),
                $config,
            )->implementation,
        ));
    }

    public function clock(): Clock
    {
        return $this->implementation->clock();
    }

    public function filesystem(): Filesystem
    {
        return $this->implementation->filesystem();
    }

    public function status(): ServerStatus
    {
        return $this->implementation->status();
    }

    public function control(): ServerControl
    {
        return $this->implementation->control();
    }

    public function ports(): Ports
    {
        return $this->implementation->ports();
    }

    public function sockets(): Sockets
    {
        return $this->implementation->sockets();
    }

    public function remote(): Remote
    {
        return $this->implementation->remote();
    }

    public function process(): CurrentProcess
    {
        return $this->implementation->process();
    }
}
