<?php
declare(strict_types = 1);

namespace Innmind\OperatingSystem\CurrentProcess;

use Innmind\OperatingSystem\CurrentProcess;
use Innmind\Server\Control\Server\Process\Pid;
use Innmind\Server\Status\Server\Memory\Bytes;
use Innmind\TimeContinuum\Period;
use Innmind\Immutable\Either;
use Psr\Log\LoggerInterface;

final class Logger implements CurrentProcess
{
    private CurrentProcess $process;
    private LoggerInterface $logger;
    private ?Signals $signals = null;

    public function __construct(CurrentProcess $process, LoggerInterface $logger)
    {
        $this->process = $process;
        $this->logger = $logger;
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

    public function fork(): Either
    {
        $this->logger->debug('Forking process');

        return $this
            ->process
            ->fork()
            ->map(function($sideEffect) {
                // @see Generic::fork()
                // the child process reset the signal handlers to avoid side effects
                // between parent and child so here we can safely reset the signals
                // to free memory as listeners are kept in memory to reference the
                // decorated listeners given to the underlying signals handler
                $this->signals = null;

                return $sideEffect;
            });
    }

    public function children(): Children
    {
        return $this->process->children();
    }

    public function signals(): Signals
    {
        return $this->signals ??= new Signals\Logger(
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
