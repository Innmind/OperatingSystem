<?php
declare(strict_types = 1);

namespace Innmind\OperatingSystem\Remote;

use Innmind\OperatingSystem\{
    Remote,
    Config,
};
use Innmind\Server\Control\{
    Server,
    Servers,
};
use Innmind\Filesystem\Chunk;
use Innmind\TimeContinuum\Clock;
use Innmind\Socket\{
    Internet\Transport,
    Client,
};
use Innmind\Url\{
    Url,
    Authority,
    Authority\Port,
};
use Innmind\HttpTransport\{
    Transport as HttpTransport,
    Curl,
};
use Innmind\Immutable\Maybe;
use Formal\AccessLayer\Connection;

final class Generic implements Remote
{
    private Server $server;
    private Clock $clock;
    private Config $config;
    private ?HttpTransport $http = null;

    private function __construct(Server $server, Clock $clock, Config $config)
    {
        $this->server = $server;
        $this->clock = $clock;
        $this->config = $config;
    }

    public static function of(
        Server $server,
        Clock $clock,
        Config $config = null,
    ): self {
        return new self($server, $clock, $config ?? Config::of());
    }

    public function ssh(Url $server): Server
    {
        $port = null;

        if ($server->authority()->port()->value() !== Port::none()->value()) {
            $port = $server->authority()->port();
        }

        return new Servers\Remote(
            $this->server,
            $server->authority()->userInformation()->user(),
            $server->authority()->host(),
            $port,
        );
    }

    public function socket(Transport $transport, Authority $authority): Maybe
    {
        /** @var Maybe<Client> */
        return Client\Internet::of($transport, $authority);
    }

    public function http(): HttpTransport
    {
        if ($this->http) {
            return $this->http;
        }

        $http = Curl::of(
            $this->clock,
            new Chunk,
            $this->config->streamCapabilities(),
        );

        return $this->http = $this->config->maxHttpConcurrency()->match(
            static fn($max) => $http->maxConcurrency($max),
            static fn() => $http,
        );
    }

    public function sql(Url $server): Connection
    {
        return new Connection\Lazy(static fn() => Connection\PDO::of($server));
    }
}
