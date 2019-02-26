<?php
declare(strict_types = 1);

namespace Innmind\OperatingSystem\CurrentProcess;

use Innmind\Signals\Signal;

interface Signals
{
    public function listen(Signal $signal, callable $listener): void;
    public function remove(callable $listener): void;
}
