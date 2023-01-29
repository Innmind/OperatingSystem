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
    Config,
};
use Innmind\Server\Status\{
    Server as ServerStatus,
    ServerFactory,
};
use Innmind\Server\Control\{
    Server as ServerControl,
    Servers,
};
use Innmind\TimeContinuum\Clock;
use Innmind\TimeWarp\Halt\Usleep;

final class Unix implements OperatingSystem
{
    private Clock $clock;
    private Config $config;
    private ?Filesystem $filesystem = null;
    private ?ServerStatus $status = null;
    private ?ServerControl $control = null;
    private ?Ports $ports = null;
    private ?Sockets $sockets = null;
    private ?Remote $remote = null;
    private ?CurrentProcess $process = null;

    private function __construct(Clock $clock, Config $config)
    {
        $this->clock = $clock;
        $this->config = $config;
    }

    public static function of(Clock $clock, Config $config = null): self
    {
        return new self($clock, $config ?? Config::of());
    }

    public function clock(): Clock
    {
        return $this->clock;
    }

    public function filesystem(): Filesystem
    {
        return $this->filesystem ??= Filesystem\Generic::of(
            $this->control()->processes(),
            new Usleep,
            $this->clock,
            $this->config,
        );
    }

    public function status(): ServerStatus
    {
        return $this->status ??= ServerFactory::build(
            $this->clock(),
            $this->control(),
            $this->config->environmentPath(),
        );
    }

    public function control(): ServerControl
    {
        return $this->control ??= Servers\Unix::of(
            $this->clock(),
            $this->config->streamCapabilities(),
            new Usleep,
        );
    }

    public function ports(): Ports
    {
        return $this->ports ??= Ports\Unix::of();
    }

    public function sockets(): Sockets
    {
        return $this->sockets ??= Sockets\Unix::of();
    }

    public function remote(): Remote
    {
        return $this->remote ??= Remote\Generic::of(
            $this->control(),
            $this->clock(),
            $this->config,
        );
    }

    public function process(): CurrentProcess
    {
        return $this->process ??= CurrentProcess\Generic::of(new Usleep);
    }
}
