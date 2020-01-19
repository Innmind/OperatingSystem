<?php
declare(strict_types = 1);

namespace Innmind\OperatingSystem;

use Innmind\OperatingSystem\{
    CurrentProcess\ForkSide,
    CurrentProcess\Children,
    CurrentProcess\Signals,
    Exception\ForkFailed,
};
use Innmind\Server\Status\Server\Process\Pid;
use Innmind\TimeContinuum\Period;

interface CurrentProcess
{
    public function id(): Pid;

    /**
     * @throws ForkFailed
     */
    public function fork(): ForkSide;
    public function children(): Children;
    public function signals(): Signals;
    public function halt(Period $period): void;
}
