<?php
declare(strict_types = 1);

namespace Innmind\OperatingSystem\Sockets;

use Innmind\OperatingSystem\{
    Sockets,
    Config,
};
use Innmind\Socket\{
    Address\Unix as Address,
    Server,
    Client,
};
use Innmind\TimeContinuum\ElapsedPeriod;
use Innmind\Stream\Watch;
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

    public function open(Address $address): Maybe
    {
        return Server\Unix::of($address)->map(
            $this->config->io()->sockets()->servers()->wrap(...),
        );
    }

    public function takeOver(Address $address): Maybe
    {
        return Server\Unix::recoverable($address)->map(
            $this->config->io()->sockets()->servers()->wrap(...),
        );
    }

    public function connectTo(Address $address): Maybe
    {
        return Client\Unix::of($address)->map(
            $this->config->io()->sockets()->clients()->wrap(...),
        );
    }

    public function watch(?ElapsedPeriod $timeout = null): Watch
    {
        if (\is_null($timeout)) {
            return $this
                ->config
                ->streamCapabilities()
                ->watch()
                ->waitForever();
        }

        return $this
            ->config
            ->streamCapabilities()
            ->watch()
            ->timeoutAfter($timeout);
    }
}
