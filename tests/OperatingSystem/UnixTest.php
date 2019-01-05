<?php
declare(strict_types = 1);

namespace Tests\Innmind\OperatingSystem\OperatingSystem;

use Innmind\OperatingSystem\{
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
use Innmind\TimeContinuum\TimeContinuumInterface;
use PHPUnit\Framework\TestCase;

class UnixTest extends TestCase
{
    public function testInterface()
    {
        $clock = $this->createMock(TimeContinuumInterface::class);

        $os = new Unix($clock);

        $this->assertInstanceOf(OperatingSystem::class, $os);
        $this->assertSame($clock, $os->clock());
        $this->assertInstanceOf(Filesystem\Generic::class, $os->filesystem());
        $this->assertInstanceOf(ServerStatus::class, $os->status());
        $this->assertInstanceOf(ServerControl::class, $os->control());
        $this->assertInstanceOf(Ports\Unix::class, $os->ports());
        $this->assertInstanceOf(Sockets\Unix::class, $os->sockets());
        $this->assertInstanceOf(Remote\Generic::class, $os->remote());
        $this->assertInstanceOf(CurrentProcess\Generic::class, $os->process());
        $this->assertSame($os->filesystem(), $os->filesystem());
        $this->assertSame($os->status(), $os->status());
        $this->assertSame($os->control(), $os->control());
        $this->assertSame($os->ports(), $os->ports());
        $this->assertSame($os->sockets(), $os->sockets());
        $this->assertSame($os->remote(), $os->remote());
        $this->assertSame($os->process(), $os->process());
    }
}
