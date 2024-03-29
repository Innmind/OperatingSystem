<?php
declare(strict_types = 1);

namespace Innmind\OperatingSystem\CurrentProcess;

use Innmind\OperatingSystem\CurrentProcess;
use Innmind\Server\Control\Server\Process\Pid;
use Innmind\Server\Status\Server\Memory\Bytes;
use Innmind\TimeContinuum\Period;
use Psr\Log\LoggerInterface;

final class Logger implements CurrentProcess
{
    private CurrentProcess $process;
    private LoggerInterface $logger;
    private ?Signals $signals = null;

    private function __construct(CurrentProcess $process, LoggerInterface $logger)
    {
        $this->process = $process;
        $this->logger = $logger;
    }

    public static function psr(CurrentProcess $process, LoggerInterface $logger): self
    {
        return new self($process, $logger);
    }

    public function id(): Pid
    {
        $pid = $this->process->id();

        $this->logger->debug(
            'Current process id is {pid}',
            ['pid' => $pid->toInt()],
        );

        return $pid;
    }

    public function signals(): Signals
    {
        return $this->signals ??= Signals\Logger::psr(
            $this->process->signals(),
            $this->logger,
        );
    }

    public function halt(Period $period): void
    {
        $this->logger->debug('Halting current process...', ['period' => [
            'years' => $period->years(),
            'months' => $period->months(),
            'days' => $period->days(),
            'hours' => $period->hours(),
            'minutes' => $period->minutes(),
            'seconds' => $period->seconds(),
            'milliseconds' => $period->milliseconds(),
        ]]);

        $this->process->halt($period);
    }

    public function memory(): Bytes
    {
        $memory = $this->process->memory();

        $this->logger->debug(
            'Current process memory at {memory}',
            ['memory' => $memory->toString()],
        );

        return $memory;
    }
}
