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
    Url,
    Authority,
    Authority\Port,
};
use Innmind\HttpTransport\Transport as HttpTransport;
use function Innmind\HttpTransport\bootstrap as http;

final class Generic implements Remote
{
    private Server $server;
    private ?HttpTransport $http = null;

    public function __construct(Server $server)
    {
        $this->server = $server;
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

    public function socket(Transport $transport, Authority $authority): Client
    {
        return new Client\Internet($transport, $authority);
    }

    public function http(): HttpTransport
    {
        return $this->http ??= http()['default'](null);
    }
}
