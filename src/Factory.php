<?php
declare(strict_types = 1);

namespace Innmind\OperatingSystem;

use Innmind\OperatingSystem\Exception\UnsupportedOperatingSystem;
use Innmind\TimeContinuum\{
    TimeContinuumInterface,
    TimeContinuum\Earth,
};

final class Factory
{
    public static function build(TimeContinuumInterface $clock = null): OperatingSystem
    {
        switch (PHP_OS) {
            case 'Darwin':
            case 'Linux':
                return new OperatingSystem\Unix($clock ?? new Earth);
        }

        throw new UnsupportedOperatingSystem;
    }
}
