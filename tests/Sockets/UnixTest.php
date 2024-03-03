<?php
declare(strict_types = 1);

namespace Tests\Innmind\OperatingSystem\Sockets;

use Innmind\OperatingSystem\{
    Sockets\Unix,
    Sockets,
    Config,
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
        $this->assertInstanceOf(Sockets::class, Unix::of(Config::of()));
    }

    public function testOpen()
    {
        $sockets = Unix::of(Config::of());

        $socket = $sockets->open(Address::of('/tmp/foo'))->match(
            static fn($server) => $server,
            static fn() => null,
        );

        $this->assertInstanceOf(Server\Unix::class, $socket);

        // return nothing as the socket already exist
        $this->assertNull($sockets->open(Address::of('/tmp/foo'))->match(
            static fn($server) => $server,
            static fn() => null,
        ));
        $socket->close();
    }

    public function testTakeOver()
    {
        $sockets = Unix::of(Config::of());

        $socket = $sockets->open(Address::of('/tmp/foo'))->match(
            static fn($server) => $server,
            static fn() => null,
        );
        $socket2 = $sockets->takeOver(Address::of('/tmp/foo'))->match(
            static fn($server) => $server,
            static fn() => null,
        );

        $this->assertInstanceOf(Server\Unix::class, $socket2);
        $this->assertNotSame($socket, $socket2);
        $socket2->close();
    }

    public function testConnectTo()
    {
        $sockets = Unix::of(Config::of());

        $server = $sockets->open(Address::of('/tmp/foo'))->match(
            static fn($server) => $server,
            static fn() => null,
        );
        $client = $sockets->connectTo(Address::of('/tmp/foo'))->match(
            static fn($client) => $client->unwrap(),
            static fn() => null,
        );

        $this->assertInstanceOf(Client\Unix::class, $client);
        $client->close();
        $server->close();
    }

    public function testWatch()
    {
        $sockets = Unix::of(Config::of());

        $this->assertInstanceOf(
            Watch::class,
            $sockets->watch($this->createMock(ElapsedPeriod::class)),
        );
    }
}
