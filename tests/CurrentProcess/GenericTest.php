<?php
declare(strict_types = 1);

namespace Tests\Innmind\OperatingSystem\CurrentProcess;

use Innmind\OperatingSystem\{
    CurrentProcess\Generic,
    CurrentProcess\Signals,
    CurrentProcess,
};
use Innmind\Server\Control\Server\Process\Pid;
use Innmind\Server\Status\Server\Memory\Bytes;
use Innmind\TimeContinuum\Period;
use Innmind\TimeWarp\Halt;
use Innmind\Immutable\SideEffect;
use Innmind\BlackBox\PHPUnit\Framework\TestCase;

class GenericTest extends TestCase
{
    public function testInterface()
    {
        $this->assertInstanceOf(
            CurrentProcess::class,
            Generic::of(Halt\Usleep::new()),
        );
    }

    public function testId()
    {
        $process = Generic::of(Halt\Usleep::new());

        $this->assertInstanceOf(Pid::class, $process->id()->unwrap());
        $this->assertSame(
            $process->id()->unwrap()->toInt(),
            $process->id()->unwrap()->toInt(),
        );
    }

    public function testHalt()
    {
        $process = Generic::of(
            Halt\Usleep::new(),
        );

        $this->assertInstanceOf(
            SideEffect::class,
            $process
                ->halt(Period::millisecond(1))
                ->unwrap(),
        );
    }

    public function testSignals()
    {
        $process = Generic::of(Halt\Usleep::new());

        $this->assertInstanceOf(Signals\Wrapper::class, $process->signals());
        $this->assertSame($process->signals(), $process->signals());
    }

    public function testMemory()
    {
        $process = Generic::of(Halt\Usleep::new());

        $this->assertInstanceOf(Bytes::class, $process->memory());
        $this->assertGreaterThan(3_000_000, $process->memory()->toInt()); // ~3MB
    }
}
