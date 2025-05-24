<?php
declare(strict_types = 1);

namespace Innmind\OperatingSystem\CurrentProcess;

use Innmind\Signals\{
    Handler,
    Signal,
    Info,
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
    public static function of(): self
    {
        return new self(new Handler);
    }

    /**
     * @param callable(Signal, Info): void $listener
     */
    public function listen(Signal $signal, callable $listener): void
    {
        $this->handler->listen($signal, $listener);
    }

    /**
     * @param callable(Signal, Info): void $listener
     */
    public function remove(callable $listener): void
    {
        $this->handler->remove($listener);
    }
}
