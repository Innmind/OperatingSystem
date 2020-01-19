<?php
declare(strict_types = 1);

namespace Tests\Innmind\OperatingSystem;

use Innmind\OperatingSystem\{
    Factory,
    OperatingSystem\Unix,
};
use Innmind\TimeContinuum\{
    Clock,
    Earth,
};
use PHPUnit\Framework\TestCase;

class FactoryTest extends TestCase
{
    public function testBuild()
    {
        $clock = $this->createMock(Clock::class);

        $os = Factory::build($clock);

        $this->assertInstanceOf(Unix::class, $os);
        $this->assertSame($clock, $os->clock());
    }

    public function testClockIsOptional()
    {
        $os = Factory::build();

        $this->assertInstanceOf(Earth\Clock::class, $os->clock());
    }
}
