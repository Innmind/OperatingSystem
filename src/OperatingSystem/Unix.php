<?php
declare(strict_types = 1);

namespace Innmind\OperatingSystem\OperatingSystem;

use Innmind\OperatingSystem\{
    OperatingSystem,
    Filesystem,
    Ports,
    Sockets,
    Remote,
};
use Innmind\Server\Status\{
    Server as ServerStatus,
    ServerFactory,
};
use Innmind\Server\Control\{
    Server as ServerControl,
    Servers\Unix as UnixControl,
};
use Innmind\TimeContinuum\TimeContinuumInterface;

final class Unix implements OperatingSystem
{
    private $clock;
    private $filesystem;
    private $status;
    private $control;
    private $ports;
    private $sockets;
    private $remote;

    public function __construct(TimeContinuumInterface $clock)
    {
        $this->clock = $clock;
    }

    public function clock(): TimeContinuumInterface
    {
        return $this->clock;
    }

    public function filesystem(): Filesystem
    {
        return $this->filesystem ?? $this->filesystem = new Filesystem\Generic;
    }

    public function status(): ServerStatus
    {
        return $this->status ?? $this->status = ServerFactory::build($this->clock());
    }

    public function control(): ServerControl
    {
        return $this->control ?? $this->control = new UnixControl;
    }

    public function ports(): Ports
    {
        return $this->ports ?? $this->ports = new Ports\Unix;
    }

    public function sockets(): Sockets
    {
        return $this->sockets ?? $this->sockets = new Sockets\Unix;
    }

    public function remote(): Remote
    {
        return $this->remote ?? $this->remote = new Remote\Generic($this->control());
    }
}
