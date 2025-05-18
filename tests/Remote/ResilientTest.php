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

    public function testSsh()
    {
        $this
            ->forAll(Url::any())
            ->then(function($url) {
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

    public function testSql()
    {
        $this
            ->forAll(Url::any())
            ->then(function($server) {
                $remote = Resilient::of(
                    $this->os->remote(),
                    $this->os->process(),
                );

                $this->assertInstanceOf(Connection::class, $remote->sql($server));
            });
    }
}
