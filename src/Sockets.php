<?php
declare(strict_types = 1);

namespace Innmind\OperatingSystem;

use Innmind\IO\{
    Sockets\Clients\Client,
    Sockets\Servers\Server,
    Sockets\Unix\Address,
};
use Innmind\Immutable\Attempt;

final class Sockets
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
     * This method will fail if the socket already exist
     *
     * @return Attempt<Server>
     */
    #[\NoDiscard]
    public function open(Address $address): Attempt
    {
        return $this
            ->config
            ->io()
            ->sockets()
            ->servers()
            ->unix($address);
    }

    /**
     * This will take control of the socket if it already exist (use carefully)
     *
     * @return Attempt<Server>
     */
    #[\NoDiscard]
    public function takeOver(Address $address): Attempt
    {
        return $this
            ->config
            ->io()
            ->sockets()
            ->servers()
            ->takeOver($address);
    }

    /**
     * @return Attempt<Client>
     */
    #[\NoDiscard]
    public function connectTo(Address $address): Attempt
    {
        return $this
            ->config
            ->io()
            ->sockets()
            ->clients()
            ->unix($address);
    }
}
