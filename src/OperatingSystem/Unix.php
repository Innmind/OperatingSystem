<?php
declare(strict_types = 1);

namespace Innmind\OperatingSystem\OperatingSystem;

use Innmind\OperatingSystem\{
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

final class Unix implements Implementation
{
    private Config $config;
    private ?Filesystem $filesystem = null;
    private ?ServerStatus $status = null;
    private ?ServerControl $control = null;
    private ?Ports $ports = null;
    private ?Sockets $sockets = null;
    private ?Remote $remote = null;
    private ?CurrentProcess $process = null;

    private function __construct(Config $config)
    {
        $this->config = $config;
    }

    public static function of(?Config $config = null): self
    {
        return new self($config ?? Config::of());
    }

    #[\Override]
    public function map(callable $map): Implementation
    {
        return $map($this, $this->config);
    }

    #[\Override]
    public function clock(): Clock
    {
        return $this->config->clock();
    }

    #[\Override]
    public function filesystem(): Filesystem
    {
        return $this->filesystem ??= Filesystem\Generic::of(
            $this->control()->processes(),
            $this->config,
        );
    }

    #[\Override]
    public function status(): ServerStatus
    {
        return $this->status ??= ServerFactory::build(
            $this->clock(),
            $this->control(),
            $this->config->environmentPath(),
        );
    }

    #[\Override]
    public function control(): ServerControl
    {
        return $this->control ??= Servers\Unix::of(
            $this->clock(),
            $this->config->io(),
            $this->config->halt(),
        );
    }

    #[\Override]
    public function ports(): Ports
    {
        return $this->ports ??= Ports\Unix::of($this->config);
    }

    #[\Override]
    public function sockets(): Sockets
    {
        return $this->sockets ??= Sockets\Unix::of($this->config);
    }

    #[\Override]
    public function remote(): Remote
    {
        return $this->remote ??= Remote\Generic::of(
            $this->control(),
            $this->config,
        );
    }

    #[\Override]
    public function process(): CurrentProcess
    {
        return $this->process ??= CurrentProcess\Generic::of($this->config->halt());
    }
}
