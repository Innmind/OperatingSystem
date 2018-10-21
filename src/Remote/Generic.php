<?php
declare(strict_types = 1);

namespace Innmind\OperatingSystem\Remote;

use Innmind\OperatingSystem\Remote;
use Innmind\Server\Control\{
    Server,
    Servers,
};
use Innmind\Socket\{
    Internet\Transport,
    Client,
};
use Innmind\Url\{
    UrlInterface,
    AuthorityInterface,
    Authority\NullPort,
};

final class Generic implements Remote
{
    private $server;

    public function __construct(Server $server)
    {
        $this->server = $server;
    }

    public function ssh(UrlInterface $server): Server
    {
        $port = null;

        if (!$server->authority()->port() instanceof NullPort) {
            $port = $server->authority()->port();
        }

        return new Servers\Remote(
            $this->server,
            $server->authority()->userInformation()->user(),
            $server->authority()->host(),
            $port
        );
    }

    public function socket(Transport $transport, AuthorityInterface $authority): Client
    {
        return new Client\Internet($transport, $authority);
    }
}
