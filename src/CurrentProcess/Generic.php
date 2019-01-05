<?php
declare(strict_types = 1);

namespace Innmind\OperatingSystem\CurrentProcess;

use Innmind\OperatingSystem\{
    CurrentProcess,
    Exception\ForkFailed,
};
use Innmind\Server\Status\Server\Process\Pid;

final class Generic implements CurrentProcess
{
    public function id(): Pid
    {
        return new Pid(\getmypid());
    }

    /**
     * {@inheritdoc}
     */
    public function fork(): ForkSide
    {
        return ForkSide::of(\pcntl_fork());
    }
}
