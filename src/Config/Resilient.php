<?php
declare(strict_types = 1);

namespace Innmind\OperatingSystem\Config;

use Innmind\OperatingSystem\Config;
use Innmind\HttpTransport\Transport;
use Innmind\Time\Period;
use Innmind\Immutable\Sequence;

/**
 * @psalm-immutable
 */
final class Resilient
{
    /**
     * @param ?Sequence<Period> $retries
     */
    private function __construct(
        private ?Sequence $retries,
    ) {
    }

    #[\NoDiscard]
    public function __invoke(Config $config): Config
    {
        $retries = $this->retries;

        return $config
            ->mapHttpTransport(static fn($transport, $config) => Transport::exponentialBackoff(
                $transport,
                $config->halt(),
                $retries,
            ));
    }

    /**
     * This config helps retry certain _safe_ operations on remote systems
     *
     * @psalm-pure
     */
    #[\NoDiscard]
    public static function new(): self
    {
        return new self(null);
    }

    /**
     * @param Sequence<Period> $retries
     */
    #[\NoDiscard]
    public function withHttpRetries(Sequence $retries): self
    {
        return new self($retries);
    }
}
