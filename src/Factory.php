<?php
declare(strict_types = 1);

namespace Innmind\OperatingSystem;

use Innmind\OperatingSystem\Exception\UnsupportedOperatingSystem;
use Innmind\TimeContinuum\TimeContinuumInterface;

final class Factory
{
    public static function build(TimeContinuumInterface $clock): OperatingSystem
    {
        switch (PHP_OS) {
            case 'Darwin':
            case 'Linux':
                return new OperatingSystem\Unix($clock);
        }

        throw new UnsupportedOperatingSystem;
    }
}
