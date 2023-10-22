<?php
declare(strict_types = 1);

namespace Tests\Innmind\OperatingSystem\OperatingSystem;

use Innmind\OperatingSystem\{
    OperatingSystem\Resilient,
    OperatingSystem\Unix,
    OperatingSystem,
    Filesystem,
    Ports,
    Sockets,
    Remote,
    CurrentProcess,
};
use Innmind\Server\Status\Server as ServerStatus;
use Innmind\Server\Control\Server as ServerControl;
use Innmind\TimeContinuum\Clock;
use PHPUnit\Framework\TestCase;

class ResilientTest extends TestCase
{
    public function testInterface()
    {
        $os = Resilient::of($this->createMock(OperatingSystem::class));

        $this->assertInstanceOf(OperatingSystem::class, $os);
        $this->assertInstanceOf(Clock::class, $os->clock());
        $this->assertInstanceOf(Filesystem::class, $os->filesystem());
        $this->assertInstanceOf(ServerStatus::class, $os->status());
        $this->assertInstanceOf(ServerControl::class, $os->control());
        $this->assertInstanceOf(Ports::class, $os->ports());
        $this->assertInstanceOf(Sockets::class, $os->sockets());
        $this->assertInstanceOf(Remote\Resilient::class, $os->remote());
        $this->assertInstanceOf(CurrentProcess::class, $os->process());
    }

    public function testMap()
    {
        $underlying = Unix::of();
        $os = Resilient::of($underlying);

        $result = $os->map(function($os) use ($underlying) {
            $this->assertSame($underlying, $os);

            return Unix::of();
        });

        $this->assertInstanceOf(Resilient::class, $result);
        $this->assertNotSame($os, $result);
    }
}
