<?php
declare(strict_types = 1);

namespace Innmind\OperatingSystem;

use Innmind\Url\Authority\Port;
use Innmind\Socket\{
    Internet\Transport,
    Server,
};
use Innmind\IP\IP;

interface Ports
{
    public function open(
        Transport $transport,
        IP $ip,
        Port $port
    ): Server;
}
