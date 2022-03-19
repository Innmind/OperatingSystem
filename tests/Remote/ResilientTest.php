<?php
declare(strict_types = 1);

namespace Tests\Innmind\OperatingSystem\Remote;

use Innmind\OperatingSystem\{
    Remote\Resilient,
    Remote,
};
use Innmind\TimeContinuum\Clock;
use Innmind\HttpTransport\ExponentialBackoff;
use Innmind\Server\Control\Server;
use Innmind\Socket\{
    Internet\Transport,
    Client,
};
use Innmind\Immutable\Maybe;
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
                $this->createMock(Clock::class),
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
                    $this->createMock(Clock::class),
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
                    $this->createMock(Clock::class),
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
            $this->createMock(Clock::class),
        );

        $this->assertInstanceOf(ExponentialBackoff::class, $remote->http());
    }
}
