<?php
declare(strict_types = 1);

namespace Tests\Innmind\OperatingSystem;

use Innmind\OperatingSystem\{
    Factory,
    OperatingSystem\Unix,
};
use Innmind\TimeContinuum\TimeContinuumInterface;
use PHPUnit\Framework\TestCase;

class FactoryTest extends TestCase
{
    public function testBuild()
    {
        $clock = $this->createMock(TimeContinuumInterface::class);

        $os = Factory::build($clock);

        $this->assertInstanceOf(Unix::class, $os);
        $this->assertSame($clock, $os->clock());
    }
}
