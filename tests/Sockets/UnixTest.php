<?php
declare(strict_types = 1);

namespace Tests\Innmind\OperatingSystem\Sockets;

use Innmind\OperatingSystem\{
    Sockets\Unix,
    Sockets,
};
use Innmind\Socket\{
    Address\Unix as Address,
    Server,
    Client,
};
use Innmind\TimeContinuum\ElapsedPeriod;
use Innmind\Stream\Watch;
use PHPUnit\Framework\TestCase;

class UnixTest extends TestCase
{
    public function testInterface()
    {
        $this->assertInstanceOf(Sockets::class, new Unix);
    }

    public function testOpen()
    {
        $sockets = new Unix;

        $socket = $sockets->open(Address::of('/tmp/foo'));

        $this->assertInstanceOf(Server\Unix::class, $socket);

        // throw as the socket already exist
        $this->expectException(\Exception::class);

        try {
            $sockets->open(Address::of('/tmp/foo'));
        } finally {
            $socket->close();
        }
    }

    public function testTakeOver()
    {
        $sockets = new Unix;

        $socket = $sockets->open(Address::of('/tmp/foo'));
        $socket2 = $sockets->takeOver(Address::of('/tmp/foo'));

        $this->assertInstanceOf(Server\Unix::class, $socket2);
        $this->assertNotSame($socket, $socket2);
        $socket2->close();
    }

    public function testConnectTo()
    {
        $sockets = new Unix;

        $server = $sockets->open(Address::of('/tmp/foo'));
        $client = $sockets->connectTo(Address::of('/tmp/foo'));

        $this->assertInstanceOf(Client\Unix::class, $client);
        $client->close();
        $server->close();
    }

    public function testWatch()
    {
        $sockets = new Unix;

        $this->assertInstanceOf(
            Watch::class,
            $sockets->watch($this->createMock(ElapsedPeriod::class)),
        );
    }
}
