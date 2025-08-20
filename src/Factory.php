<?php
declare(strict_types = 1);

namespace Innmind\OperatingSystem;

final class Factory
{
    #[\NoDiscard]
    public static function build(?Config $config = null): OperatingSystem
    {
        switch (\PHP_OS) {
            case 'Darwin':
            case 'Linux':
                return OperatingSystem::new($config);
        }

        throw new \LogicException('Unuspported operating system');
    }
}
