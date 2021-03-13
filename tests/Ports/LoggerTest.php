<?php
declare(strict_types = 1);

namespace Tests\Innmind\OperatingSystem\Ports;

use Innmind\OperatingSystem\{
    Ports\Logger,
    Ports,
};
use Innmind\Url\Authority\Port;
use Innmind\Socket\{
    Internet\Transport,
    Server,
};
use Innmind\IP\{
    IPv4,
    IPv6,
};
use Psr\Log\LoggerInterface;
use PHPUnit\Framework\TestCase;
use Innmind\BlackBox\{
    PHPUnit\BlackBox,
    Set,
};

class LoggerTest extends TestCase
{
    use BlackBox;

    public function testInterface()
    {
        $this->assertInstanceOf(
            Ports::class,
            new Logger(
                $this->createMock(Ports::class),
                $this->createMock(LoggerInterface::class),
            ),
        );
    }

    public function testOpen()
    {
        $this
            ->forAll(
                Set\Elements::of(
                    Transport::tcp(),
                    Transport::ssl(),
                    Transport::tls(),
                    Transport::tlsv10(),
                    Transport::tlsv11(),
                    Transport::tlsv12(),
                ),
                Set\Elements::of(
                    IPv4::localhost(),
                    IPv6::localhost(),
                ),
                Set\Integers::above(0),
            )
            ->then(function($transport, $ip, $port) {
                $inner = $this->createMock(Ports::class);
                $inner
                    ->expects($this->once())
                    ->method('open')
                    ->with($transport, $ip, Port::of($port))
                    ->willReturn($expected = $this->createMock(Server::class));
                $logger = $this->createMock(LoggerInterface::class);
                $logger
                    ->expects($this->once())
                    ->method('info')
                    ->with(
                        'Opening new port at {address}',
                        $this->callback(static function($context) use ($transport, $ip, $port) {
                            return (bool) \preg_match('~^[a-z\.0-9]+://[a-f\.0-9\:]+:\d+$~', $context['address']) &&
                                \strpos($context['address'], $transport->toString()) === 0 &&
                                \strpos($context['address'], $ip->toString()) !== false &&
                                \strpos($context['address'], (string) $port) !== false;
                        }),
                    );
                $ports = new Logger($inner, $logger);

                $this->assertSame($expected, $ports->open($transport, $ip, Port::of($port)));
            });
    }
}
