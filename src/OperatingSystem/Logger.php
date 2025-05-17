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

    #[\Override]
    public function map(callable $map): OperatingSystem
    {
        return new self(
            $this->os->map($map),
            $this->logger,
        );
    }

    #[\Override]
    public function clock(): TimeContinuum\Clock
    {
        return TimeContinuum\Clock::logger(
            $this->os->clock(),
            $this->logger,
        );
    }

    #[\Override]
    public function filesystem(): Filesystem
    {
        return Filesystem\Logger::psr(
            $this->os->filesystem(),
            $this->logger,
        );
    }

    #[\Override]
    public function status(): Status\Server
    {
        return Status\Servers\Logger::of(
            $this->os->status(),
            $this->logger,
        );
    }

    #[\Override]
    public function control(): Control\Server
    {
        return Control\Servers\Logger::psr(
            $this->os->control(),
            $this->logger,
        );
    }

    #[\Override]
    public function ports(): Ports
    {
        return Ports\Logger::psr(
            $this->os->ports(),
            $this->logger,
        );
    }

    #[\Override]
    public function sockets(): Sockets
    {
        return Sockets\Logger::psr(
            $this->os->sockets(),
            $this->logger,
        );
    }

    #[\Override]
    public function remote(): Remote
    {
        return Remote\Logger::psr(
            $this->os->remote(),
            $this->logger,
        );
    }

    #[\Override]
    public function process(): CurrentProcess
    {
        return CurrentProcess\Logger::psr(
            $this->os->process(),
            $this->logger,
        );
    }
}
