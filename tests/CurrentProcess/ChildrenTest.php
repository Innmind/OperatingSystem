<?php
declare(strict_types = 1);

namespace Tests\Innmind\OperatingSystem\CurrentProcess;

use Innmind\OperatingSystem\CurrentProcess\{
    Children,
    Child,
    Generic,
};
use Innmind\Server\Control\Server\Process\Pid;
use Innmind\TimeContinuum\Clock;
use Innmind\TimeWarp\Halt;
use PHPUnit\Framework\TestCase;

class ChildrenTest extends TestCase
{
    public function testInterface()
    {
        $children = new Children(
            $child1 = new Child(new Pid(10)),
            $child2 = new Child(new Pid(20))
        );

        $this->assertTrue($children->has(new Pid(10)));
        $this->assertTrue($children->has(new Pid(20)));
        $this->assertFalse($children->has(new Pid(30)));
        $this->assertSame($child1, $children->get(new Pid(10)));
        $this->assertSame($child2, $children->get(new Pid(20)));
    }

    public function testWait()
    {
        $process = new Generic(
            $this->createMock(Clock::class),
            $this->createMock(Halt::class)
        );

        $start = microtime(true);
        $children = [];

        foreach (range(2, 3) as $i) {
            $side = $process->fork();

            if (!$side->parent()) {
                sleep($i);
                exit(0);
            } else {
                $children[] = new Child($side->child());
            }
        }

        $children = new Children(...$children);

        $this->assertNull($children->wait());
        $delta = microtime(true) - $start;
        $this->assertEqualsWithDelta(3, $delta, 0.05);
    }
}
