<?php
declare(strict_types = 1);

namespace Tests\Innmind\OperatingSystem\Sockets;

use Innmind\OperatingSystem\{
    Sockets\Unix,
    Sockets,
    Config,
};
use Innmind\IO\Sockets\{
    Unix\Address,
    Servers\Server,
    Clients\Client,
};
use Innmind\Url\Path;
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

        $socket = $sockets->open(Address::of(Path::of('/tmp/foo')))->match(
            static fn($server) => $server,
            static fn() => null,
        );

        $this->assertInstanceOf(Server::class, $socket);

        // return nothing as the socket already exist
        $this->assertNull($sockets->open(Address::of(Path::of('/tmp/foo')))->match(
            static fn($server) => $server,
            static fn() => null,
        ));
        $socket->close();
    }

    public function testTakeOver()
    {
        $sockets = Unix::of(Config::of());

        $socket = $sockets->open(Address::of(Path::of('/tmp/foo')))->match(
            static fn($server) => $server,
            static fn() => null,
        );
        $socket2 = $sockets->takeOver(Address::of(Path::of('/tmp/foo')))->match(
            static fn($server) => $server,
            static fn() => null,
        );

        $this->assertInstanceOf(Server::class, $socket2);
        $this->assertNotSame($socket, $socket2);
        $socket2->close();
    }

    public function testConnectTo()
    {
        $sockets = Unix::of(Config::of());

        $server = $sockets->open(Address::of(Path::of('/tmp/foo')))->match(
            static fn($server) => $server,
            static fn() => null,
        );
        $client = $sockets->connectTo(Address::of(Path::of('/tmp/foo')))->match(
            static fn($client) => $client,
            static fn() => null,
        );

        $this->assertInstanceOf(Client::class, $client);
        $client->close();
        $server->close();
    }
}
