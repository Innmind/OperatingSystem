<?php
declare(strict_types = 1);

namespace Tests\Innmind\OperatingSystem\CurrentProcess;

use Innmind\OperatingSystem\CurrentProcess\{
    Children,
    Child,
    Generic,
};
use Innmind\Server\Control\Server\Process\Pid;
use Innmind\TimeWarp\Halt;
use PHPUnit\Framework\TestCase;

class ChildrenTest extends TestCase
{
    public function testInterface()
    {
        $children = Children::of(
            $child1 = Child::of(new Pid(10)),
            $child2 = Child::of(new Pid(20)),
        );

        $this->assertTrue($children->contains(new Pid(10)));
        $this->assertTrue($children->contains(new Pid(20)));
        $this->assertFalse($children->contains(new Pid(30)));
        $this->assertSame($child1, $children->get(new Pid(10))->match(
            static fn($child) => $child,
            static fn() => null,
        ));
        $this->assertSame($child2, $children->get(new Pid(20))->match(
            static fn($child) => $child,
            static fn() => null,
        ));
    }

    public function testWait()
    {
        $process = Generic::of($this->createMock(Halt::class));

        $start = \microtime(true);
        $children = [];
        $count = 0;

        foreach (\range(2, 3) as $i) {
            $child = $process->fork()->match(
                static fn() => null,
                static fn($left) => $left,
            );

            if (\is_null($child)) {
                \sleep($i);

                exit(0);
            }

            $this->assertInstanceOf(Child::class, $child);
            $children[] = $child;
            ++$count;
        }

        $children = Children::of(...$children);

        $codes = $children->wait();
        $this->assertCount($count, $codes);
        $codes->foreach(function($pid, $code) use ($children) {
            $this->assertTrue($children->contains($pid));
            $this->assertSame(0, $code->toInt());
        });
        $delta = \microtime(true) - $start;
        $this->assertEqualsWithDelta(3, $delta, 0.1);
    }
}
