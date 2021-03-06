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
use Psr\Log\LoggerInterface;

final class Logger implements Remote
{
    private Remote $remote;
    private LoggerInterface $logger;

    public function __construct(Remote $remote, LoggerInterface $logger)
    {
        $this->remote = $remote;
        $this->logger = $logger;
    }

    public function ssh(Url $server): Control\Server
    {
        return new Control\Servers\Logger(
            $this->remote->ssh($server),
            $this->logger,
        );
    }

    public function socket(Transport $transport, Authority $authority): Client
    {
        $this->logger->info(
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
        return new HttpTransport\LoggerTransport(
            $this->remote->http(),
            $this->logger,
        );
    }
}
