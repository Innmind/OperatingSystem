<?php
declare(strict_types = 1);

namespace Tests\Innmind\OperatingSystem\OperatingSystem;

use Innmind\OperatingSystem\{
    OperatingSystem\Resilient,
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
        $os = new Resilient($this->createMock(OperatingSystem::class));

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
}
