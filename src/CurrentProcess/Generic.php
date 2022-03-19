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
        /** @psalm-suppress ArgumentTypeCoercion */
        return new Pid(\getmypid());
    }

    public function fork(): Either
    {
        /**
         * @psalm-suppress ArgumentTypeCoercion
         * @var Either<ForkFailed|Child, SideEffect>
         */
        $result = match ($pid = \pcntl_fork()) {
            -1 => Either::left(new ForkFailed),
            0 => Either::right(new SideEffect),
            default => Either::left(new Child(new Pid($pid))),
        };

        return $result
            ->map(function($sideEffect) {
                $this->children = $this->children->clear();
                $this->signals = null;
                $this->signalsHandler = $this->signalsHandler ? $this->signalsHandler->reset() : null;

                return $sideEffect;
            })
            ->leftMap(fn($left) => match (true) {
                $left instanceof Child => $this->register($left),
                default => $left,
            });
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

    private function register(Child $child): Child
    {
        $this->children = ($this->children)($child);

        return $child;
    }
}
