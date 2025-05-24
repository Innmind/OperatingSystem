<?php
declare(strict_types = 1);

namespace Tests\Innmind\OperatingSystem;

use Innmind\OperatingSystem\{
    Sockets,
    Config,
};
use Innmind\IO\Sockets\{
    Unix\Address,
    Servers\Server,
    Clients\Client,
};
use Innmind\Url\Path;
use Innmind\BlackBox\PHPUnit\Framework\TestCase;

class SocketsTest extends TestCase
{
    public function testOpen()
    {
        $sockets = Sockets::of(Config::of());

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
        $sockets = Sockets::of(Config::of());

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
        $sockets = Sockets::of(Config::of());

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
