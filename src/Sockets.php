<?php
declare(strict_types = 1);

namespace Innmind\OperatingSystem;

use Innmind\Socket\{
    Address\Unix,
    Server,
};
use Innmind\TimeContinuum\ElapsedPeriod;
use Innmind\IO\Sockets\Client;
use Innmind\Stream\Watch;
use Innmind\Immutable\Maybe;

interface Sockets
{
    /**
     * This method will fail if the socket already exist
     *
     * @return Maybe<Server>
     */
    public function open(Unix $address): Maybe;

    /**
     * This will take control of the socket if it already exist (use carefully)
     *
     * @return Maybe<Server>
     */
    public function takeOver(Unix $address): Maybe;

    /**
     * @return Maybe<Client>
     */
    public function connectTo(Unix $address): Maybe;
    public function watch(ElapsedPeriod $timeout = null): Watch;
}
