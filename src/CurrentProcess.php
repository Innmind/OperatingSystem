<?php
declare(strict_types = 1);

namespace Innmind\OperatingSystem;

use Innmind\OperatingSystem\CurrentProcess\Signals;
use Innmind\Server\Control\Server\Process\Pid;
use Innmind\Server\Status\Server\Memory\Bytes;
use Innmind\TimeContinuum\Period;
use Innmind\TimeWarp\Halt;
use Innmind\Signals\Handler;
use Innmind\Immutable\{
    Attempt,
    SideEffect,
};

final class CurrentProcess
{
    private Halt $halt;
    private Handler $handler;
    private ?Signals $signals = null;

    private function __construct(Halt $halt, Handler $handler)
    {
        $this->halt = $halt;
        $this->handler = $handler;
    }

    /**
     * @internal
     */
    #[\NoDiscard]
    public static function of(Halt $halt, Handler $handler): self
    {
        return new self($halt, $handler);
    }

    /**
     * @return Attempt<Pid>
     */
    #[\NoDiscard]
    public function id(): Attempt
    {
        $pid = \getmypid();

        /** @psalm-suppress ArgumentTypeCoercion */
        return match ($pid) {
            false => Attempt::error(new \RuntimeException('Failed to retrieve process id')),
            default => Attempt::result(new Pid($pid)),
        };
    }

    #[\NoDiscard]
    public function signals(): Signals
    {
        return $this->signals ??= Signals::of($this->handler);
    }

    /**
     * @return Attempt<SideEffect>
     */
    #[\NoDiscard]
    public function halt(Period $period): Attempt
    {
        return ($this->halt)($period);
    }

    #[\NoDiscard]
    public function memory(): Bytes
    {
        /** @psalm-suppress ArgumentTypeCoercion */
        return Bytes::of(\memory_get_usage());
    }
}
