<?php
declare(strict_types = 1);

namespace Innmind\OperatingSystem\CurrentProcess\Signals;

use Innmind\OperatingSystem\CurrentProcess\Signals;
use Innmind\Signals\{
    Signal,
    Info,
};
use Innmind\Immutable\Map;
use Psr\Log\LoggerInterface;

final class Logger implements Signals
{
    private Signals $signals;
    private LoggerInterface $logger;
    /** @var Map<callable(Signal, Info): void, callable(Signal, Info): void> */
    private Map $decorated;

    public function __construct(Signals $signals, LoggerInterface $logger)
    {
        $this->signals = $signals;
        $this->logger = $logger;
        /** @var Map<callable(Signal, Info): void, callable(Signal, Info): void> */
        $this->decorated = Map::of('callable', 'callable');
    }

    public function listen(Signal $signal, callable $listener): void
    {
        $this->logger->info(
            'Registering a listener for signal {signal}',
            ['signal' => $signal->toInt()],
        );

        $decorated = function(Signal $signal, Info $info) use ($listener): void {
            $this->logger->info(
                'Handling signal {signal}',
                ['signal' => $signal->toInt()],
            );

            $listener($signal, $info);
        };
        $this->decorated = ($this->decorated)($listener, $decorated);

        $this->signals->listen($signal, $decorated);
    }

    public function remove(callable $listener): void
    {
        $this->logger->info('Removing a signal listener');

        // by default we alias the user listener as the decorated in case he
        // found a way to install his listener from another way than from self::listen()
        $decorated = $listener;

        if ($this->decorated->contains($listener)) {
            $decorated = $this->decorated->get($listener);
        }

        $this->signals->remove($decorated);
        $this->decorated = $this->decorated->remove($decorated);
    }
}
