<?php
declare(strict_types = 1);

namespace Innmind\OperatingSystem\Config;

use Innmind\OperatingSystem\Config;
use Innmind\HttpTransport\ExponentialBackoff;

enum Resilient
{
    case instance;

    public function __invoke(Config $config): Config
    {
        return $config
            ->mapHttpTransport(static fn($transport, $config) => ExponentialBackoff::of(
                $transport,
                $config->halt(),
            ));
    }

    /**
     * This config helps retry certain _safe_ operations on remote systems
     */
    public static function new(): self
    {
        return self::instance;
    }
}
