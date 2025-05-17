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
use PHPUnit\Framework\TestCase;

class GenericTest extends TestCase
{
    public function testInterface()
    {
        $this->assertInstanceOf(
            CurrentProcess::class,
            Generic::of($this->createMock(Halt::class)),
        );
    }

    public function testId()
    {
        $process = Generic::of($this->createMock(Halt::class));

        $this->assertInstanceOf(Pid::class, $process->id());
        $this->assertSame($process->id()->toInt(), $process->id()->toInt());
    }

    public function testHalt()
    {
        $process = Generic::of(
            Halt\Usleep::new(),
        );

        $this->assertNull($process->halt(Period::millisecond(1)));
    }

    public function testSignals()
    {
        $process = Generic::of($this->createMock(Halt::class));

        $this->assertInstanceOf(Signals\Wrapper::class, $process->signals());
        $this->assertSame($process->signals(), $process->signals());
    }

    public function testMemory()
    {
        $process = Generic::of($this->createMock(Halt::class));

        $this->assertInstanceOf(Bytes::class, $process->memory());
        $this->assertTrue($process->memory()->toInt() > 6000000); // ~5MB
    }
}
