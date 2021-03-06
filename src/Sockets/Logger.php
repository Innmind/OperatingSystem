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
use Psr\Log\LoggerInterface;

final class Logger implements Sockets
{
    private Sockets $sockets;
    private LoggerInterface $logger;

    public function __construct(Sockets $sockets, LoggerInterface $logger)
    {
        $this->sockets = $sockets;
        $this->logger = $logger;
    }

    public function open(Address $address): Server
    {
        $this->logger->info(
            'Opening socket at {address}',
            ['address' => $address->toString()],
        );

        return $this->sockets->open($address);
    }

    public function takeOver(Address $address): Server
    {
        $this->logger->info(
            'Taking over the socket at {address}',
            ['address' => $address->toString()],
        );

        return $this->sockets->takeOver($address);
    }

    public function connectTo(Address $address): Client
    {
        $this->logger->info(
            'Connecting to socket at {address}',
            ['address' => $address->toString()],
        );

        return $this->sockets->connectTo($address);
    }

    public function watch(ElapsedPeriod $timeout): Watch
    {
        return new Watch\Logger(
            $this->sockets->watch($timeout),
            $this->logger,
        );
    }
}
