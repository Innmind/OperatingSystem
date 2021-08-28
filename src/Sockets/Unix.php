<?php
declare(strict_types = 1);

namespace Innmind\OperatingSystem\Sockets;

use Innmind\OperatingSystem\Sockets;
use Innmind\Socket\{
    Address\Unix as Address,
    Server,
    Client,
};
use Innmind\TimeContinuum\ElapsedPeriod;
use Innmind\Stream\Watch;

final class Unix implements Sockets
{
    public function open(Address $address): Server
    {
        return Server\Unix::of($address)->match(
            static fn($server) => $server,
            static fn() => throw new \RuntimeException, // todo change interface
        );
    }

    public function takeOver(Address $address): Server
    {
        return Server\Unix::recoverable($address)->match(
            static fn($server) => $server,
            static fn() => throw new \RuntimeException, // todo change interface
        );
    }

    public function connectTo(Address $address): Client
    {
        return Client\Unix::of($address)->match(
            static fn($client) => $client,
            static fn() => throw new \RuntimeException, // todo change interface
        );
    }

    public function watch(ElapsedPeriod $timeout): Watch
    {
        return new Watch\Select($timeout);
    }
}
