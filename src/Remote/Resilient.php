<?php
declare(strict_types = 1);

namespace Innmind\OperatingSystem\Remote;

use Innmind\OperatingSystem\Remote;
use Innmind\Server\Control\Server;
use Innmind\Socket\{
    Internet\Transport,
    Client,
};
use Innmind\Url\{
    Url,
    Authority,
};
use Innmind\HttpTransport\{
    Transport as HttpTransport,
    ExponentialBackoffTransport,
};
use Innmind\TimeContinuum\Clock;
use Innmind\TimeWarp\Halt\Usleep;

final class Resilient implements Remote
{
    private Remote $remote;
    private Clock $clock;

    public function __construct(Remote $remote, Clock $clock)
    {
        $this->remote = $remote;
        $this->clock = $clock;
    }

    public function ssh(Url $server): Server
    {
        return $this->remote->ssh($server);
    }

    public function socket(Transport $transport, Authority $authority): Client
    {
        return $this->remote->socket($transport, $authority);
    }

    public function http(): HttpTransport
    {
        return ExponentialBackoffTransport::of(
            $this->remote->http(),
            new Usleep,
            $this->clock,
        );
    }
}
