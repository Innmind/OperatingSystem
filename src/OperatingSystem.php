<?php
declare(strict_types = 1);

namespace Innmind\OperatingSystem;

use Innmind\Server\Status\{
    Server as ServerStatus,
    ServerFactory,
};
use Innmind\Server\Control\{
    Server as ServerControl,
    Servers,
};
use Innmind\TimeContinuum\Clock;

final class OperatingSystem
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

    public static function new(?Config $config = null): self
    {
        return new self($config ?? Config::new());
    }

    /**
     * This method allows to change the underlying OS implementation while being
     * able to keep any decorators on top of it.
     *
     * @param callable(Config): Config $map
     */
    public function map(callable $map): self
    {
        return new self($map($this->config));
    }

    public function clock(): Clock
    {
        return $this->config->clock();
    }

    public function filesystem(): Filesystem
    {
        return $this->filesystem ??= Filesystem::of(
            $this->control()->processes(),
            $this->config,
        );
    }

    public function status(): ServerStatus
    {
        return $this->status ??= $this->config->serverStatus(
            ServerFactory::build(
                $this->clock(),
                $this->control(),
                $this->config->environmentPath(),
            ),
        );
    }

    public function control(): ServerControl
    {
        return $this->control ??= $this->config->serverControl(
            Servers\Unix::of(
                $this->clock(),
                $this->config->io(),
                $this->config->halt(),
            ),
        );
    }

    public function ports(): Ports
    {
        return $this->ports ??= Ports::of($this->config);
    }

    public function sockets(): Sockets
    {
        return $this->sockets ??= Sockets::of($this->config);
    }

    public function remote(): Remote
    {
        return $this->remote ??= Remote::of(
            $this->control(),
            $this->config,
        );
    }

    public function process(): CurrentProcess
    {
        return $this->process ??= CurrentProcess::of($this->config->halt());
    }
}
