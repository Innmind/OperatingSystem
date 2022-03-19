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
use Innmind\Immutable\Maybe;
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

    public function open(Address $address): Maybe
    {
        $this->logger->debug(
            'Opening socket at {address}',
            ['address' => $address->toString()],
        );

        return $this->sockets->open($address);
    }

    public function takeOver(Address $address): Maybe
    {
        $this->logger->debug(
            'Taking over the socket at {address}',
            ['address' => $address->toString()],
        );

        return $this->sockets->takeOver($address);
    }

    public function connectTo(Address $address): Maybe
    {
        $this->logger->debug(
            'Connecting to socket at {address}',
            ['address' => $address->toString()],
        );

        return $this->sockets->connectTo($address);
    }

    public function watch(ElapsedPeriod $timeout = null): Watch
    {
        return Watch\Logger::psr(
            $this->sockets->watch($timeout),
            $this->logger,
        );
    }
}
