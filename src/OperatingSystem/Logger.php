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
use Innmind\Server\Status;
use Innmind\Server\Control;
use Innmind\TimeContinuum;
use Psr\Log\LoggerInterface;

final class Logger implements OperatingSystem
{
    private OperatingSystem $os;
    private LoggerInterface $logger;

    public function __construct(OperatingSystem $os, LoggerInterface $logger)
    {
        $this->os = $os;
        $this->logger = $logger;
    }

    public function clock(): TimeContinuum\Clock
    {
        return new TimeContinuum\Logger\Clock(
            $this->os->clock(),
            $this->logger,
        );
    }

    public function filesystem(): Filesystem
    {
        return new Filesystem\Logger(
            $this->os->filesystem(),
            $this->logger,
        );
    }

    public function status(): Status\Server
    {
        return new Status\Servers\Logger(
            $this->os->status(),
            $this->logger,
        );
    }

    public function control(): Control\Server
    {
        return Control\Servers\Logger::psr(
            $this->os->control(),
            $this->logger,
        );
    }

    public function ports(): Ports
    {
        return new Ports\Logger(
            $this->os->ports(),
            $this->logger,
        );
    }

    public function sockets(): Sockets
    {
        return new Sockets\Logger(
            $this->os->sockets(),
            $this->logger,
        );
    }

    public function remote(): Remote
    {
        return new Remote\Logger(
            $this->os->remote(),
            $this->logger,
        );
    }

    public function process(): CurrentProcess
    {
        return new CurrentProcess\Logger(
            $this->os->process(),
            $this->logger,
        );
    }
}
