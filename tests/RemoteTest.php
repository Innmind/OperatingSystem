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
    Servers,
    Server\Processes,
    Server\Volumes,
    Server\Command,
    Server\Process\Pid,
    Server\Signal,
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
use Formal\AccessLayer\Connection;
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

        $this->assertInstanceOf(Servers\Remote::class, $remoteServer);
        $remoteServer
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

        $this->assertInstanceOf(Servers\Logger::class, $remoteServer);
        $remoteServer
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

        $this->assertInstanceOf(Servers\Remote::class, $remoteServer);
        $remoteServer->processes()->execute(Command::foreground('ls'));
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

                $this->assertInstanceOf(Connection::class, $sql);
            });
    }

    private function server(string ...$commands): Server
    {
        return new class($this->processes(), $this, $commands) implements Server {
            private $inner;

            public function __construct(
                private $processes,
                private $test,
                private $commands,
            ) {
            }

            public function processes(): Processes
            {
                return $this->inner ??= new class($this->processes, $this->test, $this->commands) implements Processes {
                    public function __construct(
                        private $processes,
                        private $test,
                        private $commands,
                    ) {
                    }

                    public function execute(Command $command): Attempt
                    {
                        $expected = \array_shift($this->commands);
                        $this->test->assertNotNull($expected);
                        $this->test->assertSame(
                            $expected,
                            $command->toString(),
                        );

                        return $this->processes->execute(Command::foreground('echo'));
                    }

                    public function kill(Pid $pid, Signal $signal): Attempt
                    {
                    }
                };
            }

            public function volumes(): Volumes
            {
            }

            public function reboot(): Attempt
            {
            }

            public function shutdown(): Attempt
            {
            }
        };
    }

    private function processes(): Processes
    {
        return Factory::build()->control()->processes();
    }
}
