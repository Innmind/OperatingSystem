<?php
declare(strict_types = 1);

namespace Innmind\OperatingSystem\CurrentProcess;

use Innmind\OperatingSystem\CurrentProcess;
use Innmind\Server\Control\Server\Process\Pid;
use Innmind\Server\Status\Server\Memory\Bytes;
use Innmind\TimeContinuum\Period;
use Innmind\TimeWarp\Halt;
use Innmind\Signals\Handler;

final class Generic implements CurrentProcess
{
    private Halt $halt;
    private ?Signals\Wrapper $signals = null;

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

    #[\Override]
    public function id(): Pid
    {
        /**
         * @psalm-suppress ArgumentTypeCoercion
         * @psalm-suppress PossiblyFalseArgument
         */
        return new Pid(\getmypid());
    }

    #[\Override]
    public function signals(): Signals
    {
        return $this->signals ??= Signals\Wrapper::of(new Handler);
    }

    #[\Override]
    public function halt(Period $period): void
    {
        ($this->halt)($period);
    }

    #[\Override]
    public function memory(): Bytes
    {
        /** @psalm-suppress ArgumentTypeCoercion */
        return Bytes::of(\memory_get_usage());
    }
}
