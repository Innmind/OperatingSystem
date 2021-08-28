<?php
declare(strict_types = 1);

namespace Innmind\OperatingSystem;

use Innmind\TimeContinuum\{
    Clock,
    Earth,
};

final class Factory
{
    public static function build(Clock $clock = null): OperatingSystem
    {
        switch (\PHP_OS) {
            case 'Darwin':
            case 'Linux':
                return new OperatingSystem\Unix($clock ?? new Earth\Clock);
        }

        throw new \LogicException('Unuspported operating system');
    }
}
