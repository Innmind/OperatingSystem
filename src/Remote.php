<?php
declare(strict_types = 1);

namespace Innmind\OperatingSystem;

use Innmind\Server\Control\{
    Server,
    Servers,
};
use Innmind\IO\{
    Sockets\Clients\Client,
    Sockets\Internet\Transport,
};
use Innmind\Url\{
    Url,
    Authority,
    Authority\Port,
};
use Innmind\HttpTransport\Transport as HttpTransport;
use Innmind\Immutable\Attempt;
use Formal\AccessLayer\Connection;

final class Remote
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

    public function ssh(Url $server): Server
    {
        $port = null;

        if ($server->authority()->port()->value() !== Port::none()->value()) {
            $port = $server->authority()->port();
        }

        return $this->config->serverControlMapper()(Servers\Remote::of(
            $this->server,
            $server->authority()->userInformation()->user(),
            $server->authority()->host(),
            $port,
        ));
    }

    /**
     * @return Attempt<Client>
     */
    public function socket(Transport $transport, Authority $authority): Attempt
    {
        return $this
            ->config
            ->io()
            ->sockets()
            ->clients()
            ->internet($transport, $authority);
    }

    public function http(): HttpTransport
    {
        return $this->http ??= $this->config->httpTransport();
    }

    public function sql(Url $server): Connection
    {
        return $this->config->sql($server);
    }
}
