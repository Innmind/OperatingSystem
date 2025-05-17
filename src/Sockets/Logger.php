<?php
declare(strict_types = 1);

namespace Innmind\OperatingSystem\Sockets;

use Innmind\OperatingSystem\Sockets;
use Innmind\IO\Sockets\Unix\Address;
use Innmind\Immutable\Maybe;
use Psr\Log\LoggerInterface;

final class Logger implements Sockets
{
    private Sockets $sockets;
    private LoggerInterface $logger;

    private function __construct(Sockets $sockets, LoggerInterface $logger)
    {
        $this->sockets = $sockets;
        $this->logger = $logger;
    }

    public static function psr(Sockets $sockets, LoggerInterface $logger): self
    {
        return new self($sockets, $logger);
    }

    #[\Override]
    public function open(Address $address): Maybe
    {
        $this->logger->debug(
            'Opening socket at {address}',
            ['address' => $address->toString()],
        );

        return $this->sockets->open($address);
    }

    #[\Override]
    public function takeOver(Address $address): Maybe
    {
        $this->logger->debug(
            'Taking over the socket at {address}',
            ['address' => $address->toString()],
        );

        return $this->sockets->takeOver($address);
    }

    #[\Override]
    public function connectTo(Address $address): Maybe
    {
        $this->logger->debug(
            'Connecting to socket at {address}',
            ['address' => $address->toString()],
        );

        return $this->sockets->connectTo($address);
    }
}
