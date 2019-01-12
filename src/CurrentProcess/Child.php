<?php
declare(strict_types = 1);

namespace Innmind\OperatingSystem\CurrentProcess;

use Innmind\Server\Status\Server\Process\Pid;
use Innmind\Server\Control\Server\Process\ExitCode;

final class Child
{
    private $pid;

    public function __construct(Pid $pid)
    {
        $this->pid = $pid;
    }

    public function id(): Pid
    {
        return $this->pid;
    }

    public function running(): bool
    {
        return \is_int(\posix_getpgid($this->pid->toInt()));
    }

    public function wait(): ExitCode
    {
        \pcntl_waitpid($this->pid->toInt(), $status);
        $exitCode = \pcntl_wexitstatus($status);

        return new ExitCode($exitCode);
    }

    public function kill(): void
    {
        \posix_kill($this->pid->toInt(), \SIGKILL);
    }

    public function terminate(): void
    {
        \posix_kill($this->pid->toInt(), \SIGTERM);
    }
}
