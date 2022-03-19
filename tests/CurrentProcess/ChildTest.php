<?php
declare(strict_types = 1);

namespace Tests\Innmind\OperatingSystem\CurrentProcess;

use Innmind\OperatingSystem\CurrentProcess\{
    Child,
    Generic,
};
use Innmind\Server\Control\Server\Process\{
    Pid,
    ExitCode,
};
use Innmind\TimeWarp\Halt;
use PHPUnit\Framework\TestCase;

class ChildTest extends TestCase
{
    public function testPid()
    {
        $child = Child::of($pid = new Pid(42));

        $this->assertSame($pid, $child->id());
    }

    public function testRunning()
    {
        $process = Generic::of($this->createMock(Halt::class));

        $child = $process->fork()->match(
            static fn() => null,
            static fn($left) => $left,
        );

        if (\is_null($child)) {
            \usleep(5000);

            exit(0);
        }

        $this->assertInstanceOf(Child::class, $child);
        $this->assertTrue($child->running());
        \sleep(1);
        $child->wait();
        $this->assertFalse($child->running());
    }

    public function testWait()
    {
        $process = Generic::of($this->createMock(Halt::class));

        $child = $process->fork()->match(
            static fn() => null,
            static fn($left) => $left,
        );

        if (\is_null($child)) {
            \usleep(5000);

            exit(42);
        }

        $this->assertInstanceOf(Child::class, $child);
        $this->assertTrue($child->running());
        $exitCode = $child->wait();
        $this->assertInstanceOf(ExitCode::class, $exitCode);
        $this->assertSame(42, $exitCode->toInt());
        $this->assertFalse($child->running());
    }

    public function testKill()
    {
        $process = Generic::of($this->createMock(Halt::class));

        $child = $process->fork()->match(
            static fn() => null,
            static fn($left) => $left,
        );

        if (\is_null($child)) {
            \usleep(5000);

            exit(42);
        }

        $this->assertInstanceOf(Child::class, $child);
        $this->assertTrue($child->running());
        $this->assertNull($child->kill());
        $exitCode = $child->wait();
        $this->assertFalse($child->running());
        $this->assertSame(0, $exitCode->toInt());
    }

    public function testTerminate()
    {
        $process = Generic::of($this->createMock(Halt::class));

        $child = $process->fork()->match(
            static fn() => null,
            static fn($left) => $left,
        );

        if (\is_null($child)) {
            \usleep(5000);

            exit(42);
        }

        $this->assertInstanceOf(Child::class, $child);
        $this->assertTrue($child->running());
        $this->assertNull($child->terminate());
        $exitCode = $child->wait();
        $this->assertSame(0, $exitCode->toInt());
        $this->assertFalse($child->running());
    }
}
