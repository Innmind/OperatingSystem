<?php
declare(strict_types = 1);

namespace Tests\Innmind\OperatingSystem;

use Innmind\OperatingSystem\{
    CurrentProcess,
    CurrentProcess\Signals,
    OperatingSystem,
    Config,
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

class CurrentProcessTest extends TestCase
{
    use BlackBox;

    public function testId()
    {
        $process = CurrentProcess::of(Halt\Usleep::new());

        $this->assertInstanceOf(Pid::class, $process->id()->unwrap());
        $this->assertSame(
            $process->id()->unwrap()->toInt(),
            $process->id()->unwrap()->toInt(),
        );
    }

    public function testHalt(): BlackBox\Proof
    {
        return $this
            ->forAll(Set::of(
                OperatingSystem::new(),
                OperatingSystem::new(Config::new()->map(Config\Logger::psr(new NullLogger))),
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
        $process = CurrentProcess::of(Halt\Usleep::new());

        $this->assertInstanceOf(Signals::class, $process->signals());
        $this->assertSame($process->signals(), $process->signals());
    }

    public function testMemory()
    {
        $process = CurrentProcess::of(Halt\Usleep::new());

        $this->assertInstanceOf(Bytes::class, $process->memory());
        $this->assertGreaterThan(3_000_000, $process->memory()->toInt()); // ~3MB
    }
}
