<?php
declare(strict_types = 1);

namespace Innmind\OperatingSystem;

use Innmind\Filesystem\Adapter;
use Innmind\Url\Path;
use Innmind\FileWatch\Ping;
use Innmind\Immutable\Maybe;

interface Filesystem
{
    public function mount(Path $path): Adapter;
    public function contains(Path $path): bool;

    /**
     * @return Maybe<mixed> Return the value returned by the file or nothing if the file doesn't exist
     */
    public function require(Path $path): Maybe;
    public function watch(Path $path): Ping;
}
