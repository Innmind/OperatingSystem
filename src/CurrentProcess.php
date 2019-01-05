<?php
declare(strict_types = 1);

namespace Innmind\OperatingSystem;

use Innmind\OperatingSystem\{
    CurrentProcess\ForkSide,
    Exception\ForkFailed,
};
use Innmind\Server\Status\Server\Process\Pid;

interface CurrentProcess
{
    public function id(): Pid;

    /**
     * @throws ForkFailed
     */
    public function fork(): ForkSide;
}
