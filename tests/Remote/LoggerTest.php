<?php
declare(strict_types = 1);

namespace Tests\Innmind\OperatingSystem\Remote;

use Innmind\OperatingSystem\{
    Remote\Logger,
    Remote,
};
use Innmind\Server\Control\Servers;
use Innmind\HttpTransport\LoggerTransport;
use Innmind\Socket\{
    Internet\Transport,
    Client,
};
use Innmind\Url\Url;
use Psr\Log\LoggerInterface;
use PHPUnit\Framework\TestCase;
use Innmind\BlackBox\{
    PHPUnit\BlackBox,
    Set,
};
use Fixtures\Innmind\Url\Url as FUrl;

class LoggerTest extends TestCase
{
    use BlackBox;

    public function testInterface()
    {
        $this->assertInstanceOf(
            Remote::class,
            new Logger(
                $this->createMock(Remote::class),
                $this->createMock(LoggerInterface::class),
            ),
        );
    }

    public function testSsh()
    {
        $this
            ->forAll(FUrl::any())
            ->then(function($url) {
                $inner = $this->createMock(Remote::class);
                $inner
                    ->expects($this->once())
                    ->method('ssh')
                    ->with($url);
                $logger = $this->createMock(LoggerInterface::class);
                $remote = new Logger($inner, $logger);

                $this->assertInstanceOf(Servers\Logger::class, $remote->ssh($url));
            });
    }

    public function testSocket()
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
                    // using fix set of authorities to show the log is not
                    // hardcoded but not using the authority Set as it can
                    // contain characters that messes up with preg_match
                    Url::of('foo://user:pwd@example:8080')->authority(),
                    Url::of('foo://127.0.0.1:2495')->authority(),
                ),
            )
            ->then(function($transport, $authority) {
                $inner = $this->createMock(Remote::class);
                $inner
                    ->expects($this->once())
                    ->method('socket')
                    ->with($transport, $authority)
                    ->willReturn($expected = $this->createMock(Client::class));
                $logger = $this->createMock(LoggerInterface::class);
                $logger
                    ->expects($this->once())
                    ->method('info')
                    ->with(
                        'Opening remote socket at {address}',
                        $this->callback(static function($context) use ($transport, $authority) {
                            return (bool) \preg_match('~^[a-z\.0-9]+://.+$~', $context['address']) &&
                                \strpos($context['address'], $transport->toString()) === 0 &&
                                \strpos($context['address'], $authority->toString()) !== false;
                        }),
                    );
                $remote = new Logger($inner, $logger);

                $this->assertSame($expected, $remote->socket($transport, $authority));
            });
    }

    public function testHttp()
    {
        $inner = $this->createMock(Remote::class);
        $logger = $this->createMock(LoggerInterface::class);
        $remote = new Logger($inner, $logger);

        $this->assertInstanceOf(LoggerTransport::class, $remote->http());
    }
}
