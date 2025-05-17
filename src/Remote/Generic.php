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
use Innmind\IO\Sockets\Internet\Transport;
use Innmind\Url\{
    Url,
    Authority,
    Authority\Port,
};
use Innmind\HttpTransport\{
    Transport as HttpTransport,
    Curl,
};
use Innmind\Immutable\Attempt;
use Formal\AccessLayer\Connection;

final class Generic implements Remote
{
    private Server $server;
    private Config $config;
    private ?HttpTransport $http = null;

    private function __construct(Server $server, Config $config)
    {
        $this->server = $server;
        $this->config = $config;
    }

    /**
     * @internal
     */
    public static function of(Server $server, Config $config): self
    {
        return new self($server, $config);
    }

    #[\Override]
    public function ssh(Url $server): Server
    {
        $port = null;

        if ($server->authority()->port()->value() !== Port::none()->value()) {
            $port = $server->authority()->port();
        }

        return Servers\Remote::of(
            $this->server,
            $server->authority()->userInformation()->user(),
            $server->authority()->host(),
            $port,
        );
    }

    #[\Override]
    public function socket(Transport $transport, Authority $authority): Attempt
    {
        return $this
            ->config
            ->io()
            ->sockets()
            ->clients()
            ->internet($transport, $authority);
    }

    #[\Override]
    public function http(): HttpTransport
    {
        if ($this->http) {
            return $this->http;
        }

        $http = Curl::of(
            $this->config->clock(),
            $this->config->io(),
        );
        $http = $this->config->maxHttpConcurrency()->match(
            static fn($max) => $http->maxConcurrency($max),
            static fn() => $http,
        );
        $http = $this->config->httpHeartbeat()->match(
            static fn($config) => $http->heartbeat($config[0], $config[1]),
            static fn() => $http,
        );
        $http = match ($this->config->mustDisableSSLVerification()) {
            true => $http->disableSSLVerification(),
            false => $http,
        };

        return $this->http = $http;
    }

    #[\Override]
    public function sql(Url $server): Connection
    {
        return Connection\Lazy::of(static fn() => Connection\PDO::of($server));
    }
}
