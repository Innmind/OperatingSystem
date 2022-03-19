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

    private function __construct(OperatingSystem $os, LoggerInterface $logger)
    {
        $this->os = $os;
        $this->logger = $logger;
    }

    public static function psr(OperatingSystem $os, LoggerInterface $logger): self
    {
        return new self($os, $logger);
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
        return Filesystem\Logger::psr(
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
        return Ports\Logger::psr(
            $this->os->ports(),
            $this->logger,
        );
    }

    public function sockets(): Sockets
    {
        return Sockets\Logger::psr(
            $this->os->sockets(),
            $this->logger,
        );
    }

    public function remote(): Remote
    {
        return Remote\Logger::psr(
            $this->os->remote(),
            $this->logger,
        );
    }

    public function process(): CurrentProcess
    {
        return CurrentProcess\Logger::psr(
            $this->os->process(),
            $this->logger,
        );
    }
}
