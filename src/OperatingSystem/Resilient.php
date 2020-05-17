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

    public function __construct(OperatingSystem $os)
    {
        $this->os = $os;
    }

    public function clock(): Clock
    {
        return $this->os->clock();
    }

    public function filesystem(): Filesystem
    {
        return $this->os->filesystem();
    }

    public function status(): ServerStatus
    {
        return $this->os->status();
    }

    public function control(): ServerControl
    {
        return $this->os->control();
    }

    public function ports(): Ports
    {
        return $this->os->ports();
    }

    public function sockets(): Sockets
    {
        return $this->os->sockets();
    }

    public function remote(): Remote
    {
        return new Remote\Resilient(
            $this->os->remote(),
            $this->os->clock(),
        );
    }

    public function process(): CurrentProcess
    {
        return $this->os->process();
    }
}
