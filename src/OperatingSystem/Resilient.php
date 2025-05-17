<?php
declare(strict_types = 1);

namespace Innmind\OperatingSystem\OperatingSystem;

use Innmind\OperatingSystem\{
    OperatingSystem,
    Filesystem,
    Ports,
    Sockets,
    Remote,
    CurrentProcess,
};
use Innmind\Server\Status\Server as ServerStatus;
use Innmind\Server\Control\Server as ServerControl;
use Innmind\TimeContinuum\Clock;

/**
 * This decorator helps retry certain _safe_ operations on remote systems
 */
final class Resilient implements OperatingSystem
{
    private OperatingSystem $os;

    private function __construct(OperatingSystem $os)
    {
        $this->os = $os;
    }

    public static function of(OperatingSystem $os): self
    {
        return new self($os);
    }

    #[\Override]
    public function map(callable $map): OperatingSystem
    {
        return new self($this->os->map($map));
    }

    #[\Override]
    public function clock(): Clock
    {
        return $this->os->clock();
    }

    #[\Override]
    public function filesystem(): Filesystem
    {
        return $this->os->filesystem();
    }

    #[\Override]
    public function status(): ServerStatus
    {
        return $this->os->status();
    }

    #[\Override]
    public function control(): ServerControl
    {
        return $this->os->control();
    }

    #[\Override]
    public function ports(): Ports
    {
        return $this->os->ports();
    }

    #[\Override]
    public function sockets(): Sockets
    {
        return $this->os->sockets();
    }

    #[\Override]
    public function remote(): Remote
    {
        return Remote\Resilient::of(
            $this->os->remote(),
            $this->os->process(),
        );
    }

    #[\Override]
    public function process(): CurrentProcess
    {
        return $this->os->process();
    }
}
