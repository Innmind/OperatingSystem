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

interface Remote
{
    public function ssh(UrlInterface $server): Server;
    public function socket(Transport $transport, AuthorityInterface $authority): Client;
}
