<?php
declare(strict_types = 1);

namespace Tests\Innmind\OperatingSystem\Ports;

use Innmind\OperatingSystem\{
    Ports\Unix,
    Ports,
};
use Innmind\Socket\{
    Internet\Transport,
    Server\Internet,
};
use Innmind\IP\IPv4;
use Innmind\Url\Authority\Port;
use PHPUnit\Framework\TestCase;

class UnixTest extends TestCase
{
    public function testInterface()
    {
        $this->assertInstanceOf(Ports::class, new Unix);
    }

    public function testOpen()
    {
        $ports = new Unix;

        $socket = $ports->open(
            Transport::tlsv12(),
            IPv4::localhost(),
            Port::of(1234)
        )->match(
            static fn($server) => $server,
            static fn() => null,
        );

        $this->assertInstanceOf(Internet::class, $socket);
        $socket->close();
    }
}
