<?php
declare(strict_types = 1);

namespace Innmind\OperatingSystem\CurrentProcess;

use Innmind\Signals\{
    Handler,
    Signal,
    Info,
};
use Innmind\Immutable\{
    Attempt,
    SideEffect,
};

final class Signals
{
    private Handler $handler;

    private function __construct(Handler $handler)
    {
        $this->handler = $handler;
    }

    /**
     * @internal
     */
    public static function of(Handler $handler): self
    {
        return new self($handler);
    }

    /**
     * @param callable(Signal, Info): void $listener
     *
     * @return Attempt<SideEffect>
     */
    public function listen(Signal $signal, callable $listener): Attempt
    {
        return $this->handler->listen($signal, $listener);
    }

    /**
     * @param callable(Signal, Info): void $listener
     *
     * @return Attempt<SideEffect>
     */
    public function remove(callable $listener): Attempt
    {
        return $this->handler->remove($listener);
    }
}
