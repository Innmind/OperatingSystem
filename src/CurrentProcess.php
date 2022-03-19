<?php
declare(strict_types = 1);

namespace Innmind\OperatingSystem;

use Innmind\OperatingSystem\CurrentProcess\{
    Children,
    Child,
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
     * @return Either<ForkFailed|Child, SideEffect> SideEffect represent the child side
     */
    public function fork(): Either;
    public function children(): Children;
    public function signals(): Signals;
    public function halt(Period $period): void;
    public function memory(): Bytes;
}
