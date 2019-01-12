<?php
declare(strict_types = 1);

namespace Innmind\OperatingSystem;

use Innmind\OperatingSystem\{
    CurrentProcess\ForkSide,
    CurrentProcess\Children,
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
    public function children(): Children;
}
