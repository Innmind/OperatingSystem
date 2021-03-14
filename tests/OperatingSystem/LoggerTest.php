<?php
declare(strict_types = 1);

namespace Tests\Innmind\OperatingSystem\OperatingSystem;

use Innmind\OperatingSystem\{
    OperatingSystem\Logger,
    OperatingSystem,
    Filesystem,
    Sockets,
    Ports,
    Remote,
    CurrentProcess,
};
use Innmind\TimeContinuum;
use Innmind\Server\Status;
use Innmind\Server\Control;
use Psr\Log\LoggerInterface;
use PHPUnit\Framework\TestCase;
use Innmind\BlackBox\{
    PHPUnit\BlackBox,
    Set,
};

class LoggerTest extends TestCase
{
    use BlackBox;

    private OperatingSystem $os;

    public function setUp(): void
    {
        $this->os = new Logger(
            $this->createMock(OperatingSystem::class),
            $this->createMock(LoggerInterface::class),
        );
    }

    public function testInterface()
    {
        $this->assertInstanceOf(
            OperatingSystem::class,
            $this->os,
        );
    }

    public function testClock()
    {
        $this->assertInstanceOf(
            TimeContinuum\Logger\Clock::class,
            $this->os->clock(),
        );
    }

    public function testFilesystem()
    {
        $this->assertInstanceOf(
            Filesystem\Logger::class,
            $this->os->filesystem(),
        );
    }

    public function testStatus()
    {
        $this->assertInstanceOf(
            Status\Servers\Logger::class,
            $this->os->status(),
        );
    }

    public function testControl()
    {
        $this->assertInstanceOf(
            Control\Servers\Logger::class,
            $this->os->control(),
        );
    }

    public function testPorts()
    {
        $this->assertInstanceOf(
            Ports\Logger::class,
            $this->os->ports(),
        );
    }

    public function testSockets()
    {
        $this->assertInstanceOf(
            Sockets\Logger::class,
            $this->os->sockets(),
        );
    }

    public function testRemote()
    {
        $this->assertInstanceOf(
            Remote\Logger::class,
            $this->os->remote(),
        );
    }

    public function testProcess()
    {
        $this->assertInstanceOf(
            CurrentProcess\Logger::class,
            $this->os->process(),
        );
    }
}
