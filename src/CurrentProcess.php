<?php
declare(strict_types = 1);

namespace Innmind\OperatingSystem;

use Innmind\OperatingSystem\CurrentProcess\Signals;
use Innmind\Server\Control\Server\Process\Pid;
use Innmind\Server\Status\Server\Memory\Bytes;
use Innmind\TimeContinuum\Period;
use Innmind\Immutable\{
    Attempt,
    SideEffect,
};

interface CurrentProcess
{
    public function id(): Pid;
    public function signals(): Signals;

    /**
     * @return Attempt<SideEffect>
     */
    public function halt(Period $period): Attempt;
    public function memory(): Bytes;
}
