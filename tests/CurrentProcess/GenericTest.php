<?php
declare(strict_types = 1);

namespace Tests\Innmind\OperatingSystem\CurrentProcess;

use Innmind\OperatingSystem\{
    CurrentProcess\Generic,
    CurrentProcess\Children,
    CurrentProcess\Signals,
    CurrentProcess,
};
use Innmind\Server\Control\Server\Process\Pid;
use Innmind\TimeContinuum\{
    Clock,
    Period,
};
use Innmind\TimeWarp\Halt;
use Innmind\Signals\Signal;
use PHPUnit\Framework\TestCase;

class GenericTest extends TestCase
{
    public function testInterface()
    {
        $this->assertInstanceOf(
            CurrentProcess::class,
            new Generic(
                $this->createMock(Clock::class),
                $this->createMock(Halt::class)
            )
        );
    }

    public function testId()
    {
        $process = new Generic(
            $this->createMock(Clock::class),
            $this->createMock(Halt::class)
        );

        $this->assertInstanceOf(Pid::class, $process->id());
        $this->assertSame($process->id()->toInt(), $process->id()->toInt());
    }

    public function testFork()
    {
        $process = new Generic(
            $this->createMock(Clock::class),
            $this->createMock(Halt::class)
        );

        $parentId = $process->id()->toInt();

        $side = $process->fork();

        if ($side->parent()) {
            $this->assertSame($parentId, $process->id()->toInt());
            $this->assertNotSame($parentId, $side->child()->toInt());
        } else {
            // child cannot be tested as it can't reference the current output
            // (otherwise it will result in a weird output)
            exit(0);
        }
    }

    public function testChildren()
    {
        $process = new Generic(
            $this->createMock(Clock::class),
            $this->createMock(Halt::class)
        );

        $side = $process->fork();

        if (!$side->parent()) {
            $code = $process->children()->has($process->id()) ? 1 : 0;
            exit($code);
        }

        $this->assertInstanceOf(Children::class, $process->children());
        $this->assertTrue($process->children()->has($side->child()));
        $child = $process->children()->get($side->child());
        $this->assertSame(0, $child->wait()->toInt());
    }

    public function testHalt()
    {
        $process = new Generic(
            $clock = $this->createMock(Clock::class),
            $halt = $this->createMock(Halt::class)
        );
        $period = $this->createMock(Period::class);
        $halt
            ->expects($this->once())
            ->method('__invoke')
            ->with($clock, $period);

        $this->assertNull($process->halt($period));
    }

    public function testSignals()
    {
        $process = new Generic(
            $this->createMock(Clock::class),
            $this->createMock(Halt::class)
        );

        $this->assertInstanceOf(Signals\Wrapper::class, $process->signals());
        $this->assertSame($process->signals(), $process->signals());
    }

    public function testSignalsAreResettedInForkChild()
    {
        $process = new Generic(
            $this->createMock(Clock::class),
            $this->createMock(Halt::class)
        );
        $signals = $process->signals();
        $signals->listen(Signal::terminate(), function(...$args) {
            exit(1);
        });

        $side = $process->fork();

        if (!$side->parent()) {
            if ($process->signals() === $signals) {
                exit(2);
            }

            sleep(2);
            exit(0);
        }

        // for some reason if we don't wait the signal handlers are still active
        // in the child process, the current guess is that without this sleep the
        // terminate all below is done before the child is correctly initialized
        // and had the chance to reset the signal handler
        // the intriguing thing is that instead of a sleep we do a (symfony)
        // dump(true) it have the time to remove the child signal handler
        sleep(1);

        $child = $process->children()->get($side->child());
        $child->terminate(); // should not trigger the listener in the child
        $this->assertSame(0, $child->wait()->toInt());
    }
}
