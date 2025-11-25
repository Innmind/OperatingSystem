<?php
declare(strict_types = 1);

namespace Innmind\OperatingSystem;

use Innmind\Server\Status;
use Innmind\Server\Control;
use Innmind\TimeContinuum\Clock;

final class OperatingSystem
{
    private Config $config;
    private ?Filesystem $filesystem = null;
    private ?Status\Server $status = null;
    private ?Control\Server $control = null;
    private ?Ports $ports = null;
    private ?Sockets $sockets = null;
    private ?Remote $remote = null;
    private ?CurrentProcess $process = null;

    private function __construct(Config $config)
    {
        $this->config = $config;
    }

    #[\NoDiscard]
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
    #[\NoDiscard]
    public function map(callable $map): self
    {
        return new self($map($this->config));
    }

    #[\NoDiscard]
    public function clock(): Clock
    {
        return $this->config->clock();
    }

    #[\NoDiscard]
    public function filesystem(): Filesystem
    {
        return $this->filesystem ??= Filesystem::of(
            $this->control()->processes(),
            $this->config,
        );
    }

    #[\NoDiscard]
    public function status(): Status\Server
    {
        return $this->status ??= $this->config->serverStatus(
            $this->control(),
        );
    }

    #[\NoDiscard]
    public function control(): Control\Server
    {
        return $this->control ??= $this->config->serverControl();
    }

    #[\NoDiscard]
    public function ports(): Ports
    {
        return $this->ports ??= Ports::of($this->config);
    }

    #[\NoDiscard]
    public function sockets(): Sockets
    {
        return $this->sockets ??= Sockets::of($this->config);
    }

    #[\NoDiscard]
    public function remote(): Remote
    {
        return $this->remote ??= Remote::of(
            $this->control(),
            $this->config,
        );
    }

    #[\NoDiscard]
    public function process(): CurrentProcess
    {
        return $this->process ??= CurrentProcess::of(
            $this->config->halt(),
            $this->config->signalsHandler(),
        );
    }
}
