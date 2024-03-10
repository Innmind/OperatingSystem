<?php
declare(strict_types = 1);

namespace Tests\Innmind\OperatingSystem\Remote;

use Innmind\OperatingSystem\{
    Remote\Generic,
    Remote,
    Config,
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
use Innmind\Socket\{
    Internet\Transport,
    Client\Internet,
    Server\Internet as InternetServer,
};
use Innmind\IP\IPv4;
use Innmind\HttpTransport\Transport as HttpTransport;
use Formal\AccessLayer\Connection;
use PHPUnit\Framework\TestCase;
use Innmind\BlackBox\PHPUnit\BlackBox;
use Fixtures\Innmind\Url\Url as FUrl;

class GenericTest extends TestCase
{
    use BlackBox;

    public function testInterface()
    {
        $this->assertInstanceOf(
            Remote::class,
            Generic::of(
                $this->createMock(Server::class),
                Config::of(),
            ),
        );
    }

    public function testSsh()
    {
        $remote = Generic::of(
            $server = $this->createMock(Server::class),
            Config::of(),
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
        $remote = Generic::of(
            $server = $this->createMock(Server::class),
            Config::of(),
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
        $remote = Generic::of(
            $this->createMock(Server::class),
            Config::of(),
        );
        $server = InternetServer::of(Transport::tcp(), IPv4::localhost(), Port::of(1234))->match(
            static fn($server) => $server,
            static fn() => null,
        );

        $socket = $remote->socket(Transport::tcp(), Url::of('tcp://127.0.0.1:1234')->authority())->match(
            static fn($client) => $client->unwrap(),
            static fn() => null,
        );

        $this->assertInstanceOf(Internet::class, $socket);
        $server->close();
        $socket->close();
    }

    public function testHttp()
    {
        $remote = Generic::of(
            $this->createMock(Server::class),
            Config::of(),
        );

        $http = $remote->http();

        $this->assertInstanceOf(HttpTransport::class, $http);
        $this->assertSame($http, $remote->http());
    }

    public function testSql()
    {
        $this
            ->forAll(FUrl::any())
            ->then(function($server) {
                $remote = Generic::of(
                    $this->createMock(Server::class),
                    Config::of(),
                );

                $sql = $remote->sql($server);

                $this->assertInstanceOf(Connection::class, $sql);
            });
    }
}
