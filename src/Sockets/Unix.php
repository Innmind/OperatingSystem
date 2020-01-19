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
        return new Server\Unix($address);
    }

    public function takeOver(Address $address): Server
    {
        return Server\Unix::recoverable($address);
    }

    public function connectTo(Address $address): Client
    {
        return new Client\Unix($address);
    }

    public function watch(ElapsedPeriod $timeout): Watch
    {
        return new Watch\Select($timeout);
    }
}
