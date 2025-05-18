<?php
declare(strict_types = 1);

namespace Tests\Innmind\OperatingSystem\CurrentProcess;

use Innmind\OperatingSystem\{
    CurrentProcess\Generic,
    CurrentProcess\Signals,
    CurrentProcess,
    OperatingSystem\Logger,
    Factory,
};
use Innmind\Server\Control\Server\Process\Pid;
use Innmind\Server\Status\Server\Memory\Bytes;
use Innmind\TimeContinuum\Period;
use Innmind\TimeWarp\Halt;
use Innmind\Immutable\SideEffect;
use Psr\Log\NullLogger;
use Innmind\BlackBox\{
    PHPUnit\BlackBox,
    PHPUnit\Framework\TestCase,
    Set,
};

class GenericTest extends TestCase
{
    use BlackBox;

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

    public function testHalt(): BlackBox\Proof
    {
        $os = Factory::build();

        return $this
            ->forAll(Set::of(
                $os,
                Logger::psr($os, new NullLogger),
            ))
            ->prove(function($os) {
                $process = $os->process();

                $this->assertInstanceOf(
                    SideEffect::class,
                    $process
                        ->halt(Period::millisecond(1))
                        ->unwrap(),
                );
            });
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
