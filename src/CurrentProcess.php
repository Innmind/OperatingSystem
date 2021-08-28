<?php
declare(strict_types = 1);

namespace Innmind\OperatingSystem;

use Innmind\OperatingSystem\CurrentProcess\{
    Children,
    Signals,
    ForkFailed,
};
use Innmind\Server\Control\Server\Process\Pid;
use Innmind\Server\Status\Server\Memory\Bytes;
use Innmind\TimeContinuum\Period;
use Innmind\Immutable\{
    Either,
    SideEffect,
};

interface CurrentProcess
{
    public function id(): Pid;

    /**
     * @return Either<ForkFailed|Pid, SideEffect> SideEffect represent the child side and the Pid the parent side (with the pid being the child one)
     */
    public function fork(): Either;
    public function children(): Children;
    public function signals(): Signals;
    public function halt(Period $period): void;
    public function memory(): Bytes;
}
