<?php
declare(strict_types = 1);

namespace Tests\Innmind\OperatingSystem\Sockets;

use Innmind\OperatingSystem\{
    Sockets\Logger,
    Sockets,
};
use Innmind\IO\Sockets\{
    Unix\Address,
    Servers\Server,
    Clients\Client,
};
use Innmind\Url\Path;
use Innmind\Immutable\Maybe;
use Psr\Log\LoggerInterface;
use PHPUnit\Framework\TestCase;

class LoggerTest extends TestCase
{
    public function testInterface()
    {
        $this->assertInstanceOf(
            Sockets::class,
            Logger::psr(
                $this->createMock(Sockets::class),
                $this->createMock(LoggerInterface::class),
            ),
        );
    }

    public function testOpen()
    {
        $address = Address::of(Path::of('/tmp/foo'));
        $inner = $this->createMock(Sockets::class);
        $inner
            ->expects($this->once())
            ->method('open')
            ->with($address)
            ->willReturn($expected = Maybe::just(null /* hack to avoid creating real server */));
        $logger = $this->createMock(LoggerInterface::class);
        $logger
            ->expects($this->once())
            ->method('debug')
            ->with(
                'Opening socket at {address}',
                ['address' => '/tmp/foo.sock'],
            );
        $sockets = Logger::psr($inner, $logger);

        $this->assertSame($expected, $sockets->open($address));
    }

    public function testTakeOver()
    {
        $address = Address::of(Path::of('/tmp/foo'));
        $inner = $this->createMock(Sockets::class);
        $inner
            ->expects($this->once())
            ->method('takeOver')
            ->with($address)
            ->willReturn($expected = Maybe::just(null /* hack to avoid creating real server */));
        $logger = $this->createMock(LoggerInterface::class);
        $logger
            ->expects($this->once())
            ->method('debug')
            ->with(
                'Taking over the socket at {address}',
                ['address' => '/tmp/foo.sock'],
            );
        $sockets = Logger::psr($inner, $logger);

        $this->assertSame($expected, $sockets->takeOver($address));
    }

    public function testConnectTo()
    {
        $address = Address::of(Path::of('/tmp/foo'));
        $inner = $this->createMock(Sockets::class);
        $inner
            ->expects($this->once())
            ->method('connectTo')
            ->with($address)
            ->willReturn($expected = Maybe::just(null /* hack to avoid creating real client */));
        $logger = $this->createMock(LoggerInterface::class);
        $logger
            ->expects($this->once())
            ->method('debug')
            ->with(
                'Connecting to socket at {address}',
                ['address' => '/tmp/foo.sock'],
            );
        $sockets = Logger::psr($inner, $logger);

        $this->assertSame($expected, $sockets->connectTo($address));
    }
}
