<?php
declare(strict_types = 1);

namespace Innmind\OperatingSystem\Filesystem;

use Innmind\OperatingSystem\Filesystem;
use Innmind\Filesystem\Adapter;
use Innmind\Url\Path;

final class Generic implements Filesystem
{
    public function mount(Path $path): Adapter
    {
        return new Adapter\Filesystem($path);
    }
}
