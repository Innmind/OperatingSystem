<?php
declare(strict_types = 1);

namespace Innmind\OperatingSystem\Remote;

use Innmind\OperatingSystem\{
    Remote,
    CurrentProcess,
};
use Innmind\Server\Control\Server;
use Innmind\TimeWarp\Halt;
use Innmind\TimeContinuum\Period;
use Innmind\IO\Sockets\Internet\Transport;
use Innmind\Url\{
    Url,
    Authority,
};
use Innmind\HttpTransport\{
    Transport as HttpTransport,
    ExponentialBackoff,
};
use Innmind\Immutable\Attempt;
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

    #[\Override]
    public function ssh(Url $server): Server
    {
        return $this->remote->ssh($server);
    }

    #[\Override]
    public function socket(Transport $transport, Authority $authority): Attempt
    {
        return $this->remote->socket($transport, $authority);
    }

    #[\Override]
    public function http(): HttpTransport
    {
        return ExponentialBackoff::of(
            $this->remote->http(),
            new class($this->process) implements Halt {
                public function __construct(
                    private CurrentProcess $process,
                ) {
                }

                #[\Override]
                public function __invoke(Period $period): Attempt
                {
                    return $this->process->halt($period);
                }
            },
        );
    }

    #[\Override]
    public function sql(Url $server): Connection
    {
        return $this->remote->sql($server);
    }
}
