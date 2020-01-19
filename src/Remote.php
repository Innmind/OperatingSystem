<?php
declare(strict_types = 1);

namespace Innmind\OperatingSystem;

use Innmind\Server\Control\Server;
use Innmind\Socket\{
    Internet\Transport,
    Client,
};
use Innmind\Url\{
    Url,
    Authority,
};
use Innmind\HttpTransport\Transport as HttpTransport;

interface Remote
{
    public function ssh(Url $server): Server;
    public function socket(Transport $transport, Authority $authority): Client;
    public function http(): HttpTransport;
}
