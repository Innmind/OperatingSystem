<?php
declare(strict_types = 1);

namespace Innmind\OperatingSystem;

use Innmind\Url\Authority\Port;
use Innmind\Socket\{
    Internet\Transport,
    Server,
};
use Innmind\IP\IP;
use Innmind\Immutable\Maybe;

interface Ports
{
    /**
     * @return Maybe<Server>
     */
    public function open(Transport $transport, IP $ip, Port $port): Maybe;
}
