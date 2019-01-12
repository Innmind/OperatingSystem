<?php
declare(strict_types = 1);

namespace Tests\Innmind\OperatingSystem\CurrentProcess;

use Innmind\OperatingSystem\CurrentProcess\{
    Child,
    Generic,
};
use Innmind\Server\Status\Server\Process\Pid;
use Innmind\Server\Control\Server\Process\ExitCode;
use PHPUnit\Framework\TestCase;

class ChildTest extends TestCase
{
    public function testPid()
    {
        $child = new Child($pid = new Pid(42));

        $this->assertSame($pid, $child->id());
    }

    public function testRunning()
    {
        $process = new Generic;

        $side = $process->fork();

        if ($side->parent() === false) {
            usleep(5000);
            exit(0);
        }

        $child = new Child($side->child());

        $this->assertTrue($child->running());
        sleep(2);
        $this->assertFalse($child->running());
    }

    public function testWait()
    {
        $process = new Generic;

        $side = $process->fork();

        if ($side->parent() === false) {
            usleep(5000);
            exit(42);
        }

        $child = new Child($side->child());

        $this->assertTrue($child->running());
        $exitCode = $child->wait();
        $this->assertInstanceOf(ExitCode::class, $exitCode);
        $this->assertSame(42, $exitCode->toInt());
        $this->assertFalse($child->running());
    }

    public function testKill()
    {
        $process = new Generic;

        $side = $process->fork();

        if ($side->parent() === false) {
            usleep(5000);
            exit(42);
        }

        $child = new Child($side->child());

        $this->assertTrue($child->running());
        $this->assertNull($child->kill());
        usleep(1000);
        $this->assertFalse($child->running());
        $exitCode = $child->wait();
        $this->assertSame(0, $exitCode->toInt());
    }

    public function testTerminate()
    {
        $process = new Generic;

        $side = $process->fork();

        if ($side->parent() === false) {
            usleep(5000);
            exit(42);
        }

        $child = new Child($side->child());

        $this->assertTrue($child->running());
        $this->assertNull($child->terminate());
        $this->assertTrue($child->running());
        $exitCode = $child->wait();
        $this->assertSame(0, $exitCode->toInt());
        $this->assertFalse($child->running());
    }
}
