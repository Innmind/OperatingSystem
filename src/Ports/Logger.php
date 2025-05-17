<?php
declare(strict_types = 1);

namespace Innmind\OperatingSystem\Ports;

use Innmind\OperatingSystem\Ports;
use Innmind\Url\Authority\Port;
use Innmind\IO\Sockets\Internet\Transport;
use Innmind\IP\IP;
use Innmind\Immutable\Attempt;
use Psr\Log\LoggerInterface;

final class Logger implements Ports
{
    private Ports $ports;
    private LoggerInterface $logger;

    private function __construct(Ports $ports, LoggerInterface $logger)
    {
        $this->ports = $ports;
        $this->logger = $logger;
    }

    public static function psr(Ports $ports, LoggerInterface $logger): self
    {
        return new self($ports, $logger);
    }

    #[\Override]
    public function open(Transport $transport, IP $ip, Port $port): Attempt
    {
        $this->logger->debug(
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
