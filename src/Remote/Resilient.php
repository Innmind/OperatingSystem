<?php
declare(strict_types = 1);

namespace Innmind\OperatingSystem\Remote;

use Innmind\OperatingSystem\{
    Remote,
    CurrentProcess,
};
use Innmind\Server\Control\Server;
use Innmind\Socket\Internet\Transport;
use Innmind\Url\{
    Url,
    Authority,
};
use Innmind\HttpTransport\{
    Transport as HttpTransport,
    ExponentialBackoff,
};
use Innmind\Immutable\Maybe;
use Formal\AccessLayer\Connection;

final class Resilient implements Remote
{
    private Remote $remote;
    private CurrentProcess $process;

    private function __construct(Remote $remote, CurrentProcess $process)
    {
        $this->remote = $remote;
        $this->process = $process;
    }

    public static function of(Remote $remote, CurrentProcess $process): self
    {
        return new self($remote, $process);
    }

    public function ssh(Url $server): Server
    {
        return $this->remote->ssh($server);
    }

    public function socket(Transport $transport, Authority $authority): Maybe
    {
        return $this->remote->socket($transport, $authority);
    }

    public function http(): HttpTransport
    {
        return ExponentialBackoff::of(
            $this->remote->http(),
            $this->process->halt(...),
        );
    }

    public function sql(Url $server): Connection
    {
        return $this->remote->sql($server);
    }
}
