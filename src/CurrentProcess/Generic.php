<?php
declare(strict_types = 1);

namespace Innmind\OperatingSystem\CurrentProcess;

use Innmind\OperatingSystem\{
    CurrentProcess,
    Exception\ForkFailed,
};
use Innmind\Server\Status\Server\Process\Pid;
use Innmind\TimeContinuum\{
    TimeContinuumInterface,
    PeriodInterface,
};
use Innmind\TimeWarp\Halt;
use Innmind\Immutable\Set;

final class Generic implements CurrentProcess
{
    private $clock;
    private $halt;
    private $children;

    public function __construct(TimeContinuumInterface $clock, Halt $halt)
    {
        $this->clock = $clock;
        $this->halt = $halt;
        $this->children = Set::of(Child::class);
    }

    public function id(): Pid
    {
        return new Pid(\getmypid());
    }

    /**
     * {@inheritdoc}
     */
    public function fork(): ForkSide
    {
        $side = ForkSide::of(\pcntl_fork());

        if ($side->parent()) {
            $this->children = $this->children->add(
                new Child($side->child())
            );
        } else {
            $this->children = $this->children->clear();
        }

        return $side;
    }

    public function children(): Children
    {
        return new Children(...$this->children);
    }

    public function halt(PeriodInterface $period): void
    {
        ($this->halt)($this->clock, $period);
    }
}
