<?php
declare(strict_types = 1);

namespace Tests\Innmind\OperatingSystem\Remote;

use Innmind\OperatingSystem\{
    Remote\Resilient,
    Remote,
    CurrentProcess,
};
use Innmind\HttpTransport\ExponentialBackoff;
use Innmind\Server\Control\Server;
use Innmind\Socket\{
    Internet\Transport,
    Client,
};
use Innmind\Immutable\Maybe;
use Formal\AccessLayer\Connection;
use PHPUnit\Framework\TestCase;
use Innmind\BlackBox\{
    PHPUnit\BlackBox,
    Set,
};
use Fixtures\Innmind\Url\{
    Url,
    Authority,
};

class ResilientTest extends TestCase
{
    use BlackBox;

    public function testInterface()
    {
        $this->assertInstanceOf(
            Remote::class,
            Resilient::of(
                $this->createMock(Remote::class),
                $this->createMock(CurrentProcess::class),
            ),
        );
    }

    public function testSsh()
    {
        $this
            ->forAll(Url::any())
            ->then(function($url) {
                $remote = Resilient::of(
                    $inner = $this->createMock(Remote::class),
                    $this->createMock(CurrentProcess::class),
                );
                $inner
                    ->expects($this->once())
                    ->method('ssh')
                    ->with($url)
                    ->willReturn($expected = $this->createMock(Server::class));

                $this->assertSame($expected, $remote->ssh($url));
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
                    Transport::tlsv12(),
                ),
                Authority::any(),
            )
            ->then(function($transport, $authority) {
                $remote = Resilient::of(
                    $inner = $this->createMock(Remote::class),
                    $this->createMock(CurrentProcess::class),
                );
                $inner
                    ->expects($this->once())
                    ->method('socket')
                    ->with($transport, $authority)
                    ->willReturn($expected = Maybe::just($this->createMock(Client::class)));

                $this->assertSame($expected, $remote->socket($transport, $authority));
            });
    }

    public function testHttp()
    {
        $remote = Resilient::of(
            $this->createMock(Remote::class),
            $this->createMock(CurrentProcess::class),
        );

        $this->assertInstanceOf(ExponentialBackoff::class, $remote->http());
    }

    public function testSql()
    {
        $this
            ->forAll(Url::any())
            ->then(function($server) {
                $remote = Resilient::of(
                    $this->createMock(Remote::class),
                    $this->createMock(CurrentProcess::class),
                );

                $this->assertInstanceOf(Connection::class, $remote->sql($server));
            });
    }
}
