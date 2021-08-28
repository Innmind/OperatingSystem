<?php
declare(strict_types = 1);

namespace Tests\Innmind\OperatingSystem\Remote;

use Innmind\OperatingSystem\{
    Remote\Generic,
    Remote,
};
use Innmind\Server\Control\{
    Server,
    Servers,
    Server\Command,
};
use Innmind\Url\{
    Url,
    Authority\Port,
};
use Innmind\TimeContinuum\Clock;
use Innmind\Socket\{
    Internet\Transport,
    Client\Internet,
    Server\Internet as InternetServer,
};
use Innmind\IP\IPv4;
use Innmind\HttpTransport\Transport as HttpTransport;
use PHPUnit\Framework\TestCase;

class GenericTest extends TestCase
{
    public function testInterface()
    {
        $this->assertInstanceOf(
            Remote::class,
            new Generic(
                $this->createMock(Server::class),
                $this->createMock(Clock::class),
            ),
        );
    }

    public function testSsh()
    {
        $remote = new Generic(
            $server = $this->createMock(Server::class),
            $this->createMock(Clock::class),
        );
        $server
            ->expects($this->once())
            ->method('processes')
            ->willReturn($processes = $this->createMock(Server\Processes::class));
        $processes
            ->expects($this->once())
            ->method('execute')
            ->with($this->callback(static function($command): bool {
                return $command->toString() === "ssh '-p' '42' 'user@my-vps' 'ls'";
            }));

        $remoteServer = $remote->ssh(Url::of('ssh://user@my-vps:42/'));

        $this->assertInstanceOf(Servers\Remote::class, $remoteServer);
        $remoteServer->processes()->execute(Command::foreground('ls'));
    }

    public function testSshWithoutPort()
    {
        $remote = new Generic(
            $server = $this->createMock(Server::class),
            $this->createMock(Clock::class),
        );
        $server
            ->expects($this->once())
            ->method('processes')
            ->willReturn($processes = $this->createMock(Server\Processes::class));
        $processes
            ->expects($this->once())
            ->method('execute')
            ->with($this->callback(static function($command): bool {
                return $command->toString() === "ssh 'user@my-vps' 'ls'";
            }));

        $remoteServer = $remote->ssh(Url::of('ssh://user@my-vps/'));

        $this->assertInstanceOf(Servers\Remote::class, $remoteServer);
        $remoteServer->processes()->execute(Command::foreground('ls'));
    }

    public function testSocket()
    {
        $remote = new Generic(
            $this->createMock(Server::class),
            $this->createMock(Clock::class),
        );
        $server = InternetServer::of(Transport::tcp(), IPv4::localhost(), Port::of(1234))->match(
            static fn($server) => $server,
            static fn() => null,
        );

        $socket = $remote->socket(Transport::tcp(), Url::of('tcp://127.0.0.1:1234')->authority())->match(
            static fn($client) => $client,
            static fn() => null,
        );

        $this->assertInstanceOf(Internet::class, $socket);
        $server->close();
        $socket->close();
    }

    public function testHttp()
    {
        $remote = new Generic(
            $this->createMock(Server::class),
            $this->createMock(Clock::class),
        );

        $http = $remote->http();

        $this->assertInstanceOf(HttpTransport::class, $http);
        $this->assertSame($http, $remote->http());
    }
}
