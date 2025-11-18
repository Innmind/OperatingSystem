<?php
declare(strict_types = 1);

namespace Innmind\OperatingSystem\Config;

use Innmind\OperatingSystem\Config;
use Innmind\HttpTransport\Transport;

/**
 * @psalm-immutable
 */
enum Resilient
{
    case instance;

    #[\NoDiscard]
    public function __invoke(Config $config): Config
    {
        return $config
            ->mapHttpTransport(static fn($transport, $config) => Transport::exponentialBackoff(
                $transport,
                $config->halt(),
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
        return self::instance;
    }
}
