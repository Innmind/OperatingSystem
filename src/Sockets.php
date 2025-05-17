<?php
declare(strict_types = 1);

namespace Innmind\OperatingSystem;

use Innmind\IO\{
    Sockets\Clients\Client,
    Sockets\Servers\Server,
    Sockets\Unix\Address,
};
use Innmind\Immutable\Maybe;

interface Sockets
{
    /**
     * This method will fail if the socket already exist
     *
     * @return Maybe<Server>
     */
    public function open(Address $address): Maybe;

    /**
     * This will take control of the socket if it already exist (use carefully)
     *
     * @return Maybe<Server>
     */
    public function takeOver(Address $address): Maybe;

    /**
     * @return Maybe<Client>
     */
    public function connectTo(Address $address): Maybe;
}
