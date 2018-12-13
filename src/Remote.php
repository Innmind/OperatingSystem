<?php
declare(strict_types = 1);

namespace Innmind\OperatingSystem;

use Innmind\Server\Control\Server;
use Innmind\Socket\{
    Internet\Transport,
    Client,
};
use Innmind\Url\{
    UrlInterface,
    AuthorityInterface,
};
use Innmind\HttpTransport\Transport as HttpTransport;

interface Remote
{
    public function ssh(UrlInterface $server): Server;
    public function socket(Transport $transport, AuthorityInterface $authority): Client;
    public function http(): HttpTransport;
}
