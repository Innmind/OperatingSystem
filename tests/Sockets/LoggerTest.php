<?php
declare(strict_types = 1);

namespace Tests\Innmind\OperatingSystem\Sockets;

use Innmind\OperatingSystem\{
    Sockets\Logger,
    Sockets,
};
use Innmind\Socket\{
    Address\Unix as Address,
    Server,
    Client,
};
use Innmind\TimeContinuum\Earth\ElapsedPeriod;
use Innmind\Stream\Watch;
use Innmind\Immutable\Maybe;
use Psr\Log\LoggerInterface;
use PHPUnit\Framework\TestCase;
use Innmind\BlackBox\{
    PHPUnit\BlackBox,
    Set,
};

class LoggerTest extends TestCase
{
    use BlackBox;

    public function testInterface()
    {
        $this->assertInstanceOf(
            Sockets::class,
            new Logger(
                $this->createMock(Sockets::class),
                $this->createMock(LoggerInterface::class),
            ),
        );
    }

    public function testOpen()
    {
        $address = Address::of('/tmp/foo');
        $inner = $this->createMock(Sockets::class);
        $inner
            ->expects($this->once())
            ->method('open')
            ->with($address)
            ->willReturn($expected = Maybe::just($this->createMock(Server::class)));
        $logger = $this->createMock(LoggerInterface::class);
        $logger
            ->expects($this->once())
            ->method('info')
            ->with(
                'Opening socket at {address}',
                ['address' => '/tmp/foo.sock'],
            );
        $sockets = new Logger($inner, $logger);

        $this->assertSame($expected, $sockets->open($address));
    }

    public function testTakeOver()
    {
        $address = Address::of('/tmp/foo');
        $inner = $this->createMock(Sockets::class);
        $inner
            ->expects($this->once())
            ->method('takeOver')
            ->with($address)
            ->willReturn($expected = Maybe::just($this->createMock(Server::class)));
        $logger = $this->createMock(LoggerInterface::class);
        $logger
            ->expects($this->once())
            ->method('info')
            ->with(
                'Taking over the socket at {address}',
                ['address' => '/tmp/foo.sock'],
            );
        $sockets = new Logger($inner, $logger);

        $this->assertSame($expected, $sockets->takeOver($address));
    }

    public function testConnectTo()
    {
        $address = Address::of('/tmp/foo');
        $inner = $this->createMock(Sockets::class);
        $inner
            ->expects($this->once())
            ->method('connectTo')
            ->with($address)
            ->willReturn($expected = Maybe::just($this->createMock(Client::class)));
        $logger = $this->createMock(LoggerInterface::class);
        $logger
            ->expects($this->once())
            ->method('info')
            ->with(
                'Connecting to socket at {address}',
                ['address' => '/tmp/foo.sock'],
            );
        $sockets = new Logger($inner, $logger);

        $this->assertSame($expected, $sockets->connectTo($address));
    }

    public function testWatch()
    {
        $this
            ->forAll(Set\Integers::above(0))
            ->then(function($milliseconds) {
                $inner = $this->createMock(Sockets::class);
                $inner
                    ->expects($this->once())
                    ->method('watch')
                    ->with(new ElapsedPeriod($milliseconds));
                $logger = $this->createMock(LoggerInterface::class);
                $sockets = new Logger($inner, $logger);

                $this->assertInstanceOf(
                    Watch\Logger::class,
                    $sockets->watch(new ElapsedPeriod($milliseconds)),
                );
            });
    }
}
