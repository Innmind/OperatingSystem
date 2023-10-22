<?php
declare(strict_types = 1);

namespace Innmind\OperatingSystem;

use Innmind\OperatingSystem\CurrentProcess\Signals;
use Innmind\Server\Control\Server\Process\Pid;
use Innmind\Server\Status\Server\Memory\Bytes;
use Innmind\TimeContinuum\Period;

interface CurrentProcess
{
    public function id(): Pid;
    public function signals(): Signals;
    public function halt(Period $period): void;
    public function memory(): Bytes;
}
