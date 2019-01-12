<?php
declare(strict_types = 1);

namespace Tests\Innmind\OperatingSystem\CurrentProcess;

use Innmind\OperatingSystem\{
    CurrentProcess\Generic,
    CurrentProcess\Children,
    CurrentProcess,
};
use Innmind\Server\Status\Server\Process\Pid;
use PHPUnit\Framework\TestCase;

class GenericTest extends TestCase
{
    public function testInterface()
    {
        $this->assertInstanceOf(CurrentProcess::class, new Generic);
    }

    public function testId()
    {
        $process = new Generic;

        $this->assertInstanceOf(Pid::class, $process->id());
        $this->assertSame($process->id()->toInt(), $process->id()->toInt());
    }

    public function testFork()
    {
        $process = new Generic;

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
        $process = new Generic;

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
}
