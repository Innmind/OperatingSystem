<?php
declare(strict_types = 1);

namespace Innmind\OperatingSystem\Remote;

use Innmind\OperatingSystem\Remote;
use Innmind\Server\Control;
use Innmind\Socket\{
    Internet\Transport,
    Client,
};
use Innmind\Url\{
    Url,
    Authority,
};
use Innmind\HttpTransport;
use Innmind\Immutable\Maybe;
use Formal\AccessLayer\Connection;
use Psr\Log\LoggerInterface;

final class Logger implements Remote
{
    private Remote $remote;
    private LoggerInterface $logger;

    private function __construct(Remote $remote, LoggerInterface $logger)
    {
        $this->remote = $remote;
        $this->logger = $logger;
    }

    public static function psr(Remote $remote, LoggerInterface $logger): self
    {
        return new self($remote, $logger);
    }

    public function ssh(Url $server): Control\Server
    {
        return Control\Servers\Logger::psr(
            $this->remote->ssh($server),
            $this->logger,
        );
    }

    public function socket(Transport $transport, Authority $authority): Maybe
    {
        $this->logger->debug(
            'Opening remote socket at {address}',
            [
                'address' => \sprintf(
                    '%s://%s',
                    $transport->toString(),
                    $authority->toString(),
                ),
            ],
        );

        return $this->remote->socket($transport, $authority);
    }

    public function http(): HttpTransport\Transport
    {
        return HttpTransport\Logger::psr(
            $this->remote->http(),
            $this->logger,
        );
    }

    public function sql(Url $server): Connection
    {
        return Connection\Logger::psr(
            $this->remote->sql($server),
            $this->logger,
        );
    }
}
