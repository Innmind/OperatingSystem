<?php
declare(strict_types = 1);

namespace Tests\Innmind\OperatingSystem;

use Innmind\OperatingSystem\{
    Remote,
    OperatingSystem,
    Config,
    Factory,
};
use Innmind\Server\Control\{
    Server,
    Server\Command,
};
use Innmind\Url\{
    Url,
    Authority\Port,
};
use Innmind\IO\Sockets\{
    Internet\Transport,
    Clients\Client,
};
use Innmind\IP\IPv4;
use Innmind\HttpTransport\Transport as HttpTransport;
use Innmind\Immutable\Attempt;
use Innmind\BlackBox\{
    PHPUnit\BlackBox,
    PHPUnit\Framework\TestCase,
    Set,
};
use Fixtures\Innmind\Url\Url as FUrl;
use Psr\Log\NullLogger;

class RemoteTest extends TestCase
{
    use BlackBox;

    public function testSsh()
    {
        $remote = Remote::of(
            $this->server("ssh '-p' '42' 'user@my-vps' 'ls'"),
            Config::new(),
        );

        $remoteServer = $remote->ssh(Url::of('ssh://user@my-vps:42/'));

        $this->assertInstanceOf(Server::class, $remoteServer);
        $_ = $remoteServer
            ->processes()
            ->execute(Command::foreground('ls'))
            ->unwrap();
    }

    public function testSshLogger()
    {
        $remote = Remote::of(
            $this->server("ssh '-p' '42' 'user@my-vps' 'ls'"),
            Config::new()->map(Config\Logger::psr(new NullLogger)),
        );

        $remoteServer = $remote->ssh(Url::of('ssh://user@my-vps:42/'));

        $this->assertInstanceOf(Server::class, $remoteServer);
        $_ = $remoteServer
            ->processes()
            ->execute(Command::foreground('ls'))
            ->unwrap();
    }

    public function testSshWithoutPort()
    {
        $remote = Remote::of(
            $this->server("ssh 'user@my-vps' 'ls'"),
            Config::new(),
        );

        $remoteServer = $remote->ssh(Url::of('ssh://user@my-vps/'));

        $this->assertInstanceOf(Server::class, $remoteServer);
        $_ = $remoteServer
            ->processes()
            ->execute(Command::foreground('ls'))
            ->unwrap();
    }

    public function testSocket()
    {
        $remote = Remote::of(
            $this->server(),
            Config::new(),
        );
        $server = Factory::build()
            ->ports()
            ->open(Transport::tcp(), IPv4::localhost(), Port::of(1234))
            ->match(
                static fn($server) => $server,
                static fn() => null,
            );

        $socket = $remote->socket(Transport::tcp(), Url::of('tcp://127.0.0.1:1234')->authority())->match(
            static fn($client) => $client,
            static fn() => null,
        );

        $this->assertInstanceOf(Client::class, $socket);
        $server->close();
        $socket->close();
    }

    public function testHttp(): BlackBox\Proof
    {
        return $this
            ->forAll(Set::of(
                OperatingSystem::new(),
                OperatingSystem::new(Config::new()->map(Config\Logger::psr(new NullLogger))),
            ))
            ->prove(function($os) {
                $remote = $os->remote();
                $http = $remote->http();

                $this->assertInstanceOf(HttpTransport::class, $http);
                $this->assertSame($http, $remote->http());
            });
    }

    public function testSql(): BlackBox\Proof
    {
        return $this
            ->forAll(
                FUrl::any(),
                Set::of(
                    OperatingSystem::new(),
                    OperatingSystem::new(Config::new()->map(Config\Logger::psr(new NullLogger))),
                ),
            )
            ->prove(function($server, $os) {
                $sql = $os->remote()->sql($server);

                $this->assertInstanceOf(Attempt::class, $sql);
            });
    }

    private function server(string ...$commands): Server
    {
        $processes = Factory::build()->control()->processes();

        return Server::via(function($command) use (&$commands, $processes) {
            $expected = \array_shift($commands);
            $this->assertNotNull($expected);
            $this->assertSame(
                $expected,
                $command->toString(),
            );

            return $processes->execute(Command::foreground('echo'));
        });
    }
}
