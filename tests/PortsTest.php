<?php
declare(strict_types = 1);

namespace Tests\Innmind\OperatingSystem;

use Innmind\OperatingSystem\{
    Ports,
    Config,
};
use Innmind\IO\Sockets\{
    Internet\Transport,
    Servers\Server,
};
use Innmind\IP\IPv4;
use Innmind\Url\Authority\Port;
use Innmind\BlackBox\PHPUnit\Framework\TestCase;

class PortsTest extends TestCase
{
    public function testOpen()
    {
        $ports = Ports::of(Config::of());

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
