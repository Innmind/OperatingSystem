<?php
declare(strict_types = 1);

namespace Innmind\OperatingSystem\CurrentProcess;

use Innmind\OperatingSystem\{
    CurrentProcess,
    Exception\ForkFailed,
};
use Innmind\Server\Control\Server\Process\Pid;
use Innmind\Server\Status\Server\Memory\Bytes;
use Innmind\TimeContinuum\Period;
use Innmind\TimeWarp\Halt;
use Innmind\Signals\Handler;
use Innmind\Immutable\Set;

final class Generic implements CurrentProcess
{
    private Halt $halt;
    /** @var Set<Child> */
    private Set $children;
    private ?Handler $signalsHandler = null;
    private ?Signals\Wrapper $signals = null;

    public function __construct(Halt $halt)
    {
        $this->halt = $halt;
        /** @var Set<Child> */
        $this->children = Set::of();
    }

    public function id(): Pid
    {
        return new Pid(\getmypid());
    }

    public function fork(): ForkSide
    {
        $side = ForkSide::of(\pcntl_fork());

        if ($side->parent()) {
            $this->children = $this->children->add(
                new Child($side->child()),
            );
        } else {
            $this->children = $this->children->clear();
            $this->signals = null;
            $this->signalsHandler = $this->signalsHandler ? $this->signalsHandler->reset() : null;
        }

        return $side;
    }

    public function children(): Children
    {
        return new Children(...$this->children->toList());
    }

    public function signals(): Signals
    {
        return $this->signals ??= new Signals\Wrapper(
            $this->signalsHandler = new Handler,
        );
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
