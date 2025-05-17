<?php
declare(strict_types = 1);

namespace Innmind\OperatingSystem\Sockets;

use Innmind\OperatingSystem\{
    Sockets,
    Config,
};
use Innmind\IO\Sockets\Unix\Address;
use Innmind\Immutable\Attempt;

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
    public function open(Address $address): Attempt
    {
        return $this
            ->config
            ->io()
            ->sockets()
            ->servers()
            ->unix($address);
    }

    #[\Override]
    public function takeOver(Address $address): Attempt
    {
        return $this
            ->config
            ->io()
            ->sockets()
            ->servers()
            ->takeOver($address);
    }

    #[\Override]
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
