<?php
declare(strict_types = 1);

namespace Innmind\OperatingSystem;

use Innmind\Filesystem\Adapter;
use Innmind\Url\Path;
use Innmind\FileWatch\Ping;

interface Filesystem
{
    public function mount(Path $path): Adapter;
    public function contains(Path $path): bool;
    public function watch(Path $path): Ping;
}
