<?php
declare(strict_types = 1);

namespace Tests\Innmind\OperatingSystem;

use Innmind\OperatingSystem\{
    OperatingSystem,
    Filesystem,
    Ports,
    Sockets,
    Remote,
    CurrentProcess,
    Config,
};
use Innmind\Server\Status\Server as ServerStatus;
use Innmind\Server\Control\Server as ServerControl;
use Innmind\TimeContinuum\Clock;
use Innmind\BlackBox\PHPUnit\Framework\TestCase;

class OperatingSystemTest extends TestCase
{
    public function testInterface()
    {
        $clock = Clock::live();

        $os = OperatingSystem::new(Config::of()->withClock($clock));

        $this->assertSame($clock, $os->clock());
        $this->assertInstanceOf(Filesystem::class, $os->filesystem());
        $this->assertInstanceOf(ServerStatus::class, $os->status());
        $this->assertInstanceOf(ServerControl::class, $os->control());
        $this->assertInstanceOf(Ports::class, $os->ports());
        $this->assertInstanceOf(Sockets::class, $os->sockets());
        $this->assertInstanceOf(Remote::class, $os->remote());
        $this->assertInstanceOf(CurrentProcess::class, $os->process());
        $this->assertSame($os->filesystem(), $os->filesystem());
        $this->assertSame($os->status(), $os->status());
        $this->assertSame($os->control(), $os->control());
        $this->assertSame($os->ports(), $os->ports());
        $this->assertSame($os->sockets(), $os->sockets());
        $this->assertSame($os->remote(), $os->remote());
        $this->assertSame($os->process(), $os->process());
    }

    public function testMap()
    {
        $os = OperatingSystem::new($config = Config::of());
        $expected = OperatingSystem::new();

        $result = $os->map(function($os_, $config_) use ($os, $config, $expected) {
            $this->assertSame($os, $os_);
            $this->assertSame($config, $config_);

            return $expected;
        });

        $this->assertSame($expected, $result);
    }
}
