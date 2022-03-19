<?php
declare(strict_types = 1);

namespace Innmind\OperatingSystem\CurrentProcess;

use Innmind\Server\Control\Server\Process\{
    Pid,
    ExitCode,
};

final class Child
{
    private Pid $pid;

    private function __construct(Pid $pid)
    {
        $this->pid = $pid;
    }

    public static function of(Pid $pid): self
    {
        return new self($pid);
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
        /** @var int<0, 255> */
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
