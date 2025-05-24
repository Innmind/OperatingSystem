<?php
declare(strict_types = 1);

namespace Innmind\OperatingSystem\Config;

use Innmind\OperatingSystem\Config;
use Innmind\Server\Control;
use Innmind\Server\Status;
use Innmind\TimeContinuum\Clock;
use Innmind\FileWatch\Watch;
use Innmind\HttpTransport;
use Innmind\TimeWarp\Halt;
use Formal\AccessLayer\Connection;
use Psr\Log\LoggerInterface;

final class Logger
{
    private function __construct(
        private LoggerInterface $logger,
    ) {
    }

    public function __invoke(Config $config): Config
    {
        return $config
            ->mapHalt(fn($halt) => Halt\Logger::psr(
                $halt,
                $this->logger,
            ))
            ->mapHttpTransport(fn($transport) => HttpTransport\Logger::psr(
                $transport,
                $this->logger,
            ))
            ->mapSQLConnection(fn($connection) => Connection\Logger::psr(
                $connection,
                $this->logger,
            ))
            ->mapServerControl(fn($server) => Control\Servers\Logger::psr(
                $server,
                $this->logger,
            ))
            ->mapServerStatus(fn($server) => Status\Servers\Logger::of(
                $server,
                $this->logger,
            ))
            ->mapClock(fn($clock) => Clock::logger($clock, $this->logger))
            ->mapFileWatch(fn($watch) => Watch::logger(
                $watch,
                $this->logger,
            ));
    }

    public static function psr(LoggerInterface $logger): self
    {
        return new self($logger);
    }
}
