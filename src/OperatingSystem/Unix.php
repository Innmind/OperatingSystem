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
use Innmind\Server\Status\{
    Server as ServerStatus,
    ServerFactory,
};
use Innmind\Server\Control\{
    Server as ServerControl,
    Servers\Unix as UnixControl,
};
use Innmind\TimeContinuum\Clock;
use Innmind\TimeWarp\Halt\Usleep;

final class Unix implements OperatingSystem
{
    private Clock $clock;
    private ?Filesystem $filesystem = null;
    private ?ServerStatus $status = null;
    private ?ServerControl $control = null;
    private ?Ports $ports = null;
    private ?Sockets $sockets = null;
    private ?Remote $remote = null;
    private ?CurrentProcess $process = null;

    public function __construct(Clock $clock)
    {
        $this->clock = $clock;
    }

    public function clock(): Clock
    {
        return $this->clock;
    }

    public function filesystem(): Filesystem
    {
        return $this->filesystem ??= new Filesystem\Generic(
            $this->control()->processes(),
            new Usleep,
            $this->clock,
        );
    }

    public function status(): ServerStatus
    {
        return $this->status ??= ServerFactory::build($this->clock());
    }

    public function control(): ServerControl
    {
        return $this->control ??= new UnixControl;
    }

    public function ports(): Ports
    {
        return $this->ports ??= new Ports\Unix;
    }

    public function sockets(): Sockets
    {
        return $this->sockets ??= new Sockets\Unix;
    }

    public function remote(): Remote
    {
        return $this->remote ??= new Remote\Generic($this->control());
    }

    public function process(): CurrentProcess
    {
        return $this->process ??= new CurrentProcess\Generic(
            $this->clock(),
            new Usleep,
        );
    }
}
