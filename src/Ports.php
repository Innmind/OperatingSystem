<?php
declare(strict_types = 1);

namespace Innmind\OperatingSystem;

use Innmind\Url\Authority\Port;
use Innmind\IO\{
    Sockets\Servers\Server,
    Sockets\Internet\Transport,
};
use Innmind\IP\IP;
use Innmind\Immutable\Attempt;

final class Ports
{
    private Config $config;

    private function __construct(Config $config)
    {
        $this->config = $config;
    }

    /**
     * @internal
     */
    #[\NoDiscard]
    public static function of(Config $config): self
    {
        return new self($config);
    }

    /**
     * @return Attempt<Server>
     */
    #[\NoDiscard]
    public function open(Transport $transport, IP $ip, Port $port): Attempt
    {
        return $this
            ->config
            ->io()
            ->sockets()
            ->servers()
            ->internet($transport, $ip, $port);
    }
}
