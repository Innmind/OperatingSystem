<?php
declare(strict_types = 1);

namespace Innmind\OperatingSystem;

use Innmind\Socket\{
    Address\Unix,
    Server,
    Client,
};

interface Sockets
{
    public function open(Unix $address): Server;
    public function takeOver(Unix $address): Server;
    public function connectTo(Unix $address): Client;
}
