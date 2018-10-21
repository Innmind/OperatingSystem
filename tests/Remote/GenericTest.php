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
use Innmind\Socket\{
    Internet\Transport,
    Client\Internet,
    Server\Internet as InternetServer,
};
use Innmind\IP\IPv4;
use PHPUnit\Framework\TestCase;

class GenericTest extends TestCase
{
    public function testInterface()
    {
        $this->assertInstanceOf(
            Remote::class,
            new Generic(
                $this->createMock(Server::class)
            )
        );
    }

    public function testSsh()
    {
        $remote = new Generic(
            $server = $this->createMock(Server::class)
        );
        $server
            ->expects($this->once())
            ->method('processes')
            ->willReturn($processes = $this->createMock(Server\Processes::class));
        $processes
            ->expects($this->once())
            ->method('execute')
            ->with($this->callback(function($command): bool {
                return (string) $command === "ssh '-p' '42' 'user@my-vps' 'ls'";
            }));

        $remoteServer = $remote->ssh(Url::fromString('ssh://user@my-vps:42/'));

        $this->assertInstanceOf(Servers\Remote::class, $remoteServer);
        $remoteServer->processes()->execute(Command::foreground('ls'));
    }

    public function testSshWithoutPort()
    {
        $remote = new Generic(
            $server = $this->createMock(Server::class)
        );
        $server
            ->expects($this->once())
            ->method('processes')
            ->willReturn($processes = $this->createMock(Server\Processes::class));
        $processes
            ->expects($this->once())
            ->method('execute')
            ->with($this->callback(function($command): bool {
                return (string) $command === "ssh 'user@my-vps' 'ls'";
            }));

        $remoteServer = $remote->ssh(Url::fromString('ssh://user@my-vps/'));

        $this->assertInstanceOf(Servers\Remote::class, $remoteServer);
        $remoteServer->processes()->execute(Command::foreground('ls'));
    }

    public function testSocket()
    {
        $remote = new Generic(
            $this->createMock(Server::class)
        );
        $server = new InternetServer(Transport::tcp(), IPv4::localhost(), new Port(1234));

        $socket = $remote->socket(Transport::tcp(), Url::fromString('tcp://127.0.0.1:1234')->authority());

        $this->assertInstanceOf(Internet::class, $socket);
        $server->close();
        $socket->close();
    }
}
