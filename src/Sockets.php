<?php
declare(strict_types = 1);

namespace Innmind\OperatingSystem;

use Innmind\Socket\{
    Address\Unix,
    Server,
    Client,
};
use Innmind\TimeContinuum\ElapsedPeriod;
use Innmind\Stream\Watch;

interface Sockets
{
    /**
     * This method will fail if the socket already exist
     */
    public function open(Unix $address): Server;

    /**
     * This will take control of the socket if it already exist (use carefully)
     */
    public function takeOver(Unix $address): Server;
    public function connectTo(Unix $address): Client;
    public function watch(ElapsedPeriod $timeout): Watch;
}
