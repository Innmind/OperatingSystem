<?php
declare(strict_types = 1);

namespace Tests\Innmind\OperatingSystem\CurrentProcess;

use Innmind\OperatingSystem\{
    CurrentProcess\ForkSide,
    Exception\ForkFailed,
};
use Innmind\Server\Status\Server\Process\Pid;
use PHPUnit\Framework\TestCase;
use Eris\{
    Generator,
    TestTrait,
};

class ForkSideTest extends TestCase
{
    use TestTrait;

    public function testFailure()
    {
        $this->expectException(ForkFailed::class);

        ForkSide::of(-1);
    }

    public function testChild()
    {
        $side = ForkSide::of(0);

        $this->assertInstanceOf(ForkSide::class, $side);
        $this->assertFalse($side->parent());
    }

    public function testParent()
    {
        $this
            ->forAll(Generator\pos())
            ->then(function($status): void {
                $side = ForkSide::of($status);

                $this->assertInstanceOf(ForkSide::class, $side);
                $this->assertTrue($side->parent());
                $this->assertInstanceOf(Pid::class, $side->child());
                $this->assertSame($status, $side->child()->toInt());
            });
    }
}
