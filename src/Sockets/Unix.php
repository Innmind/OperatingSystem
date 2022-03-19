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
use Innmind\Immutable\Maybe;

final class Unix implements Sockets
{
    public function open(Address $address): Maybe
    {
        /** @var Maybe<Server> */
        return Server\Unix::of($address);
    }

    public function takeOver(Address $address): Maybe
    {
        /** @var Maybe<Server> */
        return Server\Unix::recoverable($address);
    }

    public function connectTo(Address $address): Maybe
    {
        /** @var Maybe<Client> */
        return Client\Unix::of($address);
    }

    public function watch(ElapsedPeriod $timeout): Watch
    {
        return Watch\Select::timeoutAfter($timeout);
    }
}
