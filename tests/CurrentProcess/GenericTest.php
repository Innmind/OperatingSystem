<?php
declare(strict_types = 1);

namespace Tests\Innmind\OperatingSystem\CurrentProcess;

use Innmind\OperatingSystem\{
    CurrentProcess\Generic,
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
}
