<?php
declare(strict_types = 1);

namespace Innmind\OperatingSystem\Sockets;

use Innmind\OperatingSystem\{
    Sockets,
    Config,
};
use Innmind\IO\Sockets\Unix\Address;
use Innmind\Immutable\Maybe;

final class Unix implements Sockets
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
    public function open(Address $address): Maybe
    {
        return $this
            ->config
            ->io()
            ->sockets()
            ->servers()
            ->unix($address)
            ->maybe();
    }

    #[\Override]
    public function takeOver(Address $address): Maybe
    {
        return $this
            ->config
            ->io()
            ->sockets()
            ->servers()
            ->takeOver($address)
            ->maybe();
    }

    #[\Override]
    public function connectTo(Address $address): Maybe
    {
        return $this
            ->config
            ->io()
            ->sockets()
            ->clients()
            ->unix($address)
            ->maybe();
    }
}
