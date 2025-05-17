<?php
declare(strict_types = 1);

namespace Innmind\OperatingSystem;

use Innmind\IO\{
    Sockets\Clients\Client,
    Sockets\Servers\Server,
    Sockets\Unix\Address,
};
use Innmind\Immutable\Attempt;

interface Sockets
{
    /**
     * This method will fail if the socket already exist
     *
     * @return Attempt<Server>
     */
    public function open(Address $address): Attempt;

    /**
     * This will take control of the socket if it already exist (use carefully)
     *
     * @return Attempt<Server>
     */
    public function takeOver(Address $address): Attempt;

    /**
     * @return Attempt<Client>
     */
    public function connectTo(Address $address): Attempt;
}
