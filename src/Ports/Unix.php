<?php
declare(strict_types = 1);

namespace Innmind\OperatingSystem\Ports;

use Innmind\OperatingSystem\Ports;
use Innmind\Url\Authority\Port;
use Innmind\Socket\{
    Internet\Transport,
    Server,
};
use Innmind\IP\IP;
use Innmind\Immutable\Maybe;

final class Unix implements Ports
{
    private function __construct()
    {
    }

    public static function of(): self
    {
        return new self;
    }

    public function open(Transport $transport, IP $ip, Port $port): Maybe
    {
        /** @var Maybe<Server> */
        return Server\Internet::of($transport, $ip, $port);
    }
}
