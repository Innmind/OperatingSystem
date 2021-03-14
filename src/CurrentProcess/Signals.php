<?php
declare(strict_types = 1);

namespace Innmind\OperatingSystem\CurrentProcess;

use Innmind\Signals\{
    Signal,
    Info,
};

interface Signals
{
    /**
     * @param callable(Signal, Info): void $listener
     */
    public function listen(Signal $signal, callable $listener): void;

    /**
     * @param callable(Signal, Info): void $listener
     */
    public function remove(callable $listener): void;
}
