<?php
declare(strict_types = 1);

namespace Innmind\OperatingSystem;

use Innmind\Filesystem\Adapter;
use Innmind\Url\PathInterface;

interface Filesystem
{
    public function mount(PathInterface $path): Adapter;
}
