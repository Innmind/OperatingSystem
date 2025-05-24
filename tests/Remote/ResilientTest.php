<?php
declare(strict_types = 1);

namespace Tests\Innmind\OperatingSystem\Remote;

use Innmind\OperatingSystem\{
    Config,
    Factory,
};
use Innmind\HttpTransport\ExponentialBackoff;
use Innmind\BlackBox\PHPUnit\Framework\TestCase;

class ResilientTest extends TestCase
{
    private $os;

    public function setUp(): void
    {
        $this->os = Factory::build();
    }

    public function testHttp()
    {
        $os = $this->os->map(
            Config\Resilient::new(),
        );

        $this->assertInstanceOf(ExponentialBackoff::class, $os->remote()->http());
    }
}
