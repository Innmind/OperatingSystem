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
}
