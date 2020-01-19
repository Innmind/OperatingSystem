<?php
declare(strict_types = 1);

namespace Innmind\OperatingSystem\Ports;

use Innmind\OperatingSystem\Ports;
use Innmind\Url\Authority\Port;
use Innmind\Socket\{
    Internet\Transport,
    Server,
};
use Innmind\IP\IP;

final class Unix implements Ports
{
    public function open(
        Transport $transport,
        IP $ip,
        Port $port
    ): Server {
        return new Server\Internet($transport, $ip, $port);
    }
}
