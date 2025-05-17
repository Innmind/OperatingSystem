<?php
declare(strict_types = 1);

namespace Innmind\OperatingSystem\Ports;

use Innmind\OperatingSystem\{
    Ports,
    Config,
};
use Innmind\Url\Authority\Port;
use Innmind\IO\Sockets\Internet\Transport;
use Innmind\IP\IP;
use Innmind\Immutable\Maybe;

final class Unix implements Ports
{
    private Config $config;

    private function __construct(Config $config)
    {
        $this->config = $config;
    }

    /**
     * @internal
     */
    public static function of(Config $config): self
    {
        return new self($config);
    }

    #[\Override]
    public function open(Transport $transport, IP $ip, Port $port): Maybe
    {
        return $this
            ->config
            ->io()
            ->sockets()
            ->servers()
            ->internet($transport, $ip, $port)
            ->maybe();
    }
}
