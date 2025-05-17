<?php
declare(strict_types = 1);

namespace Innmind\OperatingSystem;

use Innmind\Filesystem\{
    Adapter,
    File\Content,
};
use Innmind\Url\Path;
use Innmind\FileWatch\Ping;
use Innmind\Immutable\{
    Attempt,
    Maybe,
    Str,
    Sequence,
};

interface Filesystem
{
    /**
     * @return Attempt<Adapter>
     */
    public function mount(Path $path): Attempt;
    public function contains(Path $path): bool;

    /**
     * @return Maybe<mixed> Return the value returned by the file or nothing if the file doesn't exist
     */
    public function require(Path $path): Maybe;
    public function watch(Path $path): Ping;

    /**
     * This method is to be used to generate a temporary file content even if it
     * doesn't fit in memory.
     *
     * Usually the sequence of chunks comes from reading a socket meaning it
     * can't be read twice. By using this temporary file content you can read it
     * multiple times.
     *
     * @param Sequence<Maybe<Str>> $chunks
     *
     * @return Attempt<Content>
     */
    public function temporary(Sequence $chunks): Attempt;
}
