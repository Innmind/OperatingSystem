<?php
declare(strict_types = 1);

namespace Tests\Innmind\OperatingSystem\Ports;

use Innmind\OperatingSystem\{
    Ports\Unix,
    Ports,
    Config,
};
use Innmind\IO\Sockets\{
    Internet\Transport,
    Servers\Server,
};
use Innmind\IP\IPv4;
use Innmind\Url\Authority\Port;
use PHPUnit\Framework\TestCase;

class UnixTest extends TestCase
{
    public function testInterface()
    {
        $this->assertInstanceOf(Ports::class, Unix::of(Config::of()));
    }

    public function testOpen()
    {
        $ports = Unix::of(Config::of());

        $socket = $ports->open(
            Transport::tlsv12(),
            IPv4::localhost(),
            Port::of(1234),
        )->match(
            static fn($server) => $server,
            static fn() => null,
        );

        $this->assertInstanceOf(Server::class, $socket);
        $socket->close();
    }
}
