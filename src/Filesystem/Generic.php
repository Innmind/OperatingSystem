<?php
declare(strict_types = 1);

namespace Innmind\OperatingSystem\Filesystem;

use Innmind\OperatingSystem\Filesystem;
use Innmind\Filesystem\Adapter;
use Innmind\Url\PathInterface;

final class Generic implements Filesystem
{
    public function mount(PathInterface $path): Adapter
    {
        return new Adapter\FilesystemAdapter((string) $path);
    }
}
