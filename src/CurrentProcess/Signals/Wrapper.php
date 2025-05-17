<?php
declare(strict_types = 1);

namespace Innmind\OperatingSystem\CurrentProcess\Signals;

use Innmind\OperatingSystem\CurrentProcess\Signals;
use Innmind\Signals\{
    Handler,
    Signal,
};

final class Wrapper implements Signals
{
    private Handler $handler;

    private function __construct(Handler $handler)
    {
        $this->handler = $handler;
    }

    public static function of(Handler $handler): self
    {
        return new self($handler);
    }

    #[\Override]
    public function listen(Signal $signal, callable $listener): void
    {
        $this->handler->listen($signal, $listener);
    }

    #[\Override]
    public function remove(callable $listener): void
    {
        $this->handler->remove($listener);
    }
}
