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
use Psr\Log\LoggerInterface;

final class Logger implements Ports
{
    private Ports $ports;
    private LoggerInterface $logger;

    public function __construct(Ports $ports, LoggerInterface $logger)
    {
        $this->ports = $ports;
        $this->logger = $logger;
    }

    public function open(Transport $transport, IP $ip, Port $port): Server
    {
        $this->logger->info(
            'Opening new port at {address}',
            [
                'address' => \sprintf(
                    '%s://%s:%s',
                    $transport->toString(),
                    $ip->toString(),
                    $port->toString(),
                ),
            ],
        );

        return $this->ports->open($transport, $ip, $port);
    }
}
