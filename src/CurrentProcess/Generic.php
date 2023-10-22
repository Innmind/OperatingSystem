<?php
declare(strict_types = 1);

namespace Innmind\OperatingSystem\CurrentProcess;

use Innmind\OperatingSystem\CurrentProcess;
use Innmind\Server\Control\Server\Process\Pid;
use Innmind\Server\Status\Server\Memory\Bytes;
use Innmind\TimeContinuum\Period;
use Innmind\TimeWarp\Halt;
use Innmind\Signals\Handler;
use Innmind\Immutable\{
    Set,
    Either,
    SideEffect,
};

final class Generic implements CurrentProcess
{
    private Halt $halt;
    private ?Signals\Wrapper $signals = null;

    private function __construct(Halt $halt)
    {
        $this->halt = $halt;
    }

    public static function of(Halt $halt): self
    {
        return new self($halt);
    }

    public function id(): Pid
    {
        /** @psalm-suppress ArgumentTypeCoercion */
        return new Pid(\getmypid());
    }

    public function signals(): Signals
    {
        return $this->signals ??= Signals\Wrapper::of(new Handler);
    }

    public function halt(Period $period): void
    {
        ($this->halt)($period);
    }

    public function memory(): Bytes
    {
        return new Bytes(\memory_get_usage());
    }
}
