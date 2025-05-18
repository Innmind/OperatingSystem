<?php
declare(strict_types = 1);

namespace Tests\Innmind\OperatingSystem\Remote;

use Innmind\OperatingSystem\{
    Remote\Resilient,
    Remote,
    Factory,
};
use Innmind\HttpTransport\ExponentialBackoff;
use Innmind\Server\Control\Server;
use Innmind\IO\Sockets\Internet\Transport;
use Innmind\Immutable\Attempt;
use Formal\AccessLayer\Connection;
use Innmind\BlackBox\{
    PHPUnit\BlackBox,
    PHPUnit\Framework\TestCase,
    Set,
};
use Fixtures\Innmind\Url\{
    Url,
    Authority,
};

class ResilientTest extends TestCase
{
    use BlackBox;

    private $os;

    public function setUp(): void
    {
        $this->os = Factory::build();
    }

    public function testInterface()
    {
        $this->assertInstanceOf(
            Remote::class,
            Resilient::of(
                $this->os->remote(),
                $this->os->process(),
            ),
        );
    }

    public function testSsh(): BlackBox\Proof
    {
        return $this
            ->forAll(Url::any())
            ->prove(function($url) {
                $remote = Resilient::of(
                    $this->os->remote(),
                    $this->os->process(),
                );

                $this->assertInstanceOf(
                    Server::class,
                    $remote->ssh($url),
                );
            });
    }

    public function testSocket(): BlackBox\Proof
    {
        return $this
            ->forAll(
                Set::of(
                    Transport::tcp(),
                    Transport::ssl(),
                    Transport::tls(),
                    Transport::tlsv10(),
                    Transport::tlsv12(),
                ),
                Authority::any(),
            )
            ->prove(function($transport, $authority) {
                $remote = Resilient::of(
                    $this->os->remote(),
                    $this->os->process(),
                );

                $this->assertInstanceOf(
                    Attempt::class,
                    $remote->socket($transport, $authority),
                );
            });
    }

    public function testHttp()
    {
        $remote = Resilient::of(
            $this->os->remote(),
            $this->os->process(),
        );

        $this->assertInstanceOf(ExponentialBackoff::class, $remote->http());
    }

    public function testSql(): BlackBox\Proof
    {
        return $this
            ->forAll(Url::any())
            ->prove(function($server) {
                $remote = Resilient::of(
                    $this->os->remote(),
                    $this->os->process(),
                );

                $this->assertInstanceOf(Connection::class, $remote->sql($server));
            });
    }
}
