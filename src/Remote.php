<?php
declare(strict_types = 1);

namespace Innmind\OperatingSystem;

use Innmind\Server\Control\Server;
use Innmind\IO\{
    Sockets\Clients\Client,
    Sockets\Internet\Transport,
};
use Innmind\Url\{
    Url,
    Authority,
};
use Innmind\HttpTransport\Transport as HttpTransport;
use Innmind\Immutable\Maybe;
use Formal\AccessLayer\Connection;

interface Remote
{
    public function ssh(Url $server): Server;

    /**
     * @return Maybe<Client>
     */
    public function socket(Transport $transport, Authority $authority): Maybe;
    public function http(): HttpTransport;
    public function sql(Url $server): Connection;
}
