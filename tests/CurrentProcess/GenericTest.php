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
use Innmind\Server\Status\Server\Memory\Bytes;
use Innmind\TimeContinuum\Period;
use Innmind\TimeWarp\Halt;
use Innmind\Signals\Signal;
use PHPUnit\Framework\TestCase;

class GenericTest extends TestCase
{
    public function testInterface()
    {
        $this->assertInstanceOf(
            CurrentProcess::class,
            new Generic($this->createMock(Halt::class))
        );
    }

    public function testId()
    {
        $process = new Generic($this->createMock(Halt::class));

        $this->assertInstanceOf(Pid::class, $process->id());
        $this->assertSame($process->id()->toInt(), $process->id()->toInt());
    }

    public function testFork()
    {
        $process = new Generic($this->createMock(Halt::class));

        $parentId = $process->id()->toInt();

        $result = $process->fork()->match(
            static fn() => null,
            static fn($left) => $left,
        );

        if (\is_null($result)) {
            // child cannot be tested as it can't reference the current output
            // (otherwise it will result in a weird output)
            exit(0);
        }

        $this->assertInstanceOf(Pid::class, $result);
        $this->assertSame($parentId, $process->id()->toInt());
        $this->assertNotSame($parentId, $result->toInt());
    }

    public function testChildren()
    {
        $process = new Generic($this->createMock(Halt::class));

        $child = $process->fork()->match(
            static fn() => null,
            static fn($left) => $left,
        );

        if (\is_null($child)) {
            $code = $process->children()->contains($process->id()) ? 1 : 0;

            exit($code);
        }

        $this->assertInstanceOf(Children::class, $process->children());
        $this->assertInstanceOf(Pid::class, $child);
        $this->assertTrue($process->children()->contains($child));
        $child = $process->children()->get($child)->match(
            static fn($child) => $child,
            static fn() => null,
        );
        $this->assertSame(0, $child->wait()->toInt());
    }

    public function testHalt()
    {
        $process = new Generic(
            $halt = $this->createMock(Halt::class),
        );
        $period = $this->createMock(Period::class);
        $halt
            ->expects($this->once())
            ->method('__invoke')
            ->with($period);

        $this->assertNull($process->halt($period));
    }

    public function testSignals()
    {
        $process = new Generic($this->createMock(Halt::class));

        $this->assertInstanceOf(Signals\Wrapper::class, $process->signals());
        $this->assertSame($process->signals(), $process->signals());
    }

    public function testSignalsAreResettedInForkChild()
    {
        $process = new Generic($this->createMock(Halt::class));
        $signals = $process->signals();
        $signals->listen(Signal::terminate, static function(...$args) {
            exit(1);
        });

        $child = $process->fork()->match(
            static fn() => null,
            static fn($left) => $left,
        );

        if (\is_null($child)) {
            if ($process->signals() === $signals) {
                exit(2);
            }

            \sleep(2);

            exit(0);
        }

        // for some reason if we don't wait the signal handlers are still active
        // in the child process, the current guess is that without this sleep the
        // terminate all below is done before the child is correctly initialized
        // and had the chance to reset the signal handler
        // the intriguing thing is that instead of a sleep we do a (symfony)
        // dump(true) it have the time to remove the child signal handler
        \sleep(1);

        $this->assertInstanceOf(Pid::class, $child);
        $child = $process->children()->get($child)->match(
            static fn($child) => $child,
            static fn() => null,
        );
        $child->terminate(); // should not trigger the listener in the child
        $this->assertSame(0, $child->wait()->toInt());
    }

    public function testMemory()
    {
        $process = new Generic($this->createMock(Halt::class));

        $this->assertInstanceOf(Bytes::class, $process->memory());
        $this->assertTrue($process->memory()->toInt() > 6000000); // ~5MB
    }
}
