<?php
declare(strict_types = 1);

namespace Innmind\OperatingSystem;

use Innmind\OperatingSystem\CurrentProcess\Signals;
use Innmind\Server\Control\Server\Process\Pid;
use Innmind\Server\Status\Server\Memory\Bytes;
use Innmind\TimeContinuum\Period;
use Innmind\TimeWarp\Halt;
use Innmind\Immutable\{
    Attempt,
    SideEffect,
};

final class CurrentProcess
{
    private Halt $halt;
    private ?Signals $signals = null;

    private function __construct(Halt $halt)
    {
        $this->halt = $halt;
    }

    /**
     * @internal
     */
    public static function of(Halt $halt): self
    {
        return new self($halt);
    }

    /**
     * @return Attempt<Pid>
     */
    public function id(): Attempt
    {
        $pid = \getmypid();

        /** @psalm-suppress ArgumentTypeCoercion */
        return match ($pid) {
            false => Attempt::error(new \RuntimeException('Failed to retrieve process id')),
            default => Attempt::result(new Pid($pid)),
        };
    }

    public function signals(): Signals
    {
        return $this->signals ??= Signals::of();
    }

    /**
     * @return Attempt<SideEffect>
     */
    public function halt(Period $period): Attempt
    {
        return ($this->halt)($period);
    }

    public function memory(): Bytes
    {
        /** @psalm-suppress ArgumentTypeCoercion */
        return Bytes::of(\memory_get_usage());
    }
}
