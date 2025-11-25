<?php
declare(strict_types = 1);

namespace Innmind\OperatingSystem;

use Innmind\Filesystem\{
    Adapter,
    File\Content,
    Directory,
    Name,
};
use Innmind\Url\Path;
use Innmind\Server\Control\Server\Processes;
use Innmind\FileWatch\{
    Ping,
    Watch,
};
use Innmind\Immutable\{
    Maybe,
    Attempt,
    Sequence,
    Str,
};

final class Filesystem
{
    private Watch $watch;
    private Config $config;
    /** @var \WeakMap<Adapter, string> */
    private \WeakMap $mounted;

    private function __construct(Processes $processes, Config $config)
    {
        $this->watch = $config->fileWatch($processes);
        $this->config = $config;
        /** @var \WeakMap<Adapter, string> */
        $this->mounted = new \WeakMap;
    }

    /**
     * @internal
     */
    #[\NoDiscard]
    public static function of(Processes $processes, Config $config): self
    {
        return new self($processes, $config);
    }

    /**
     * @return Attempt<Adapter>
     */
    #[\NoDiscard]
    public function mount(Path $path): Attempt
    {
        /**
         * @var Adapter $adapter
         * @var string $mounted
         */
        foreach ($this->mounted as $adapter => $mounted) {
            if ($path->toString() === $mounted) {
                return Attempt::result($adapter);
            }
        }

        return $this
            ->config
            ->filesystem($path)
            ->map(function($adapter) use ($path) {
                $this->mounted[$adapter] = $path->toString();

                return $adapter;
            });
    }

    #[\NoDiscard]
    public function contains(Path $path): bool
    {
        $dir = \rtrim(\dirname($path->toString()), '/').'/';
        $name = \basename($path->toString());

        $exists = $this
            ->config
            ->filesystem(Path::of($dir))
            ->maybe();

        // empty when the path === '/'
        if ($name !== '') {
            $exists = $exists
                ->flatMap(static fn($adapter) => $adapter->get(Name::of($name)))
                ->filter(static fn($file) => match (true) {
                    $path->directory() && !($file instanceof Directory) => false,
                    default => true,
                });
        }

        return $exists->match(
            static fn() => true,
            static fn() => false,
        );
    }

    /**
     * @return Maybe<mixed> Return the value returned by the file or nothing if the file doesn't exist
     */
    #[\NoDiscard]
    public function require(Path $path): Maybe
    {
        $path = $path->toString();

        if (!\file_exists($path) || \is_dir($path)) {
            /** @var Maybe<mixed> */
            return Maybe::nothing();
        }

        /**
         * @psalm-suppress UnresolvableInclude
         * @psalm-suppress MixedArgument
         * @var Maybe<mixed>
         */
        return Maybe::just(require $path);
    }

    #[\NoDiscard]
    public function watch(Path $path): Ping
    {
        return ($this->watch)($path);
    }

    /**
     * This method is to be used to generate a temporary file content even if it
     * doesn't fit in memory.
     *
     * Usually the sequence of chunks comes from reading a socket meaning it
     * can't be read twice. By using this temporary file content you can read it
     * multiple times.
     *
     * @param Sequence<Attempt<Str>> $chunks
     *
     * @return Attempt<Content>
     */
    #[\NoDiscard]
    public function temporary(Sequence $chunks): Attempt
    {
        return $this
            ->config
            ->io()
            ->files()
            ->temporary(Sequence::of())
            ->flatMap(
                static fn($tmp) => $chunks
                    ->sink($tmp->push()->chunk(...))
                    ->attempt(
                        static fn($push, $chunk) => $chunk
                            ->flatMap($push)
                            ->map(static fn() => $push),
                    )
                    ->map(static fn() => $tmp->read()),
            )
            ->map(Content::io(...));
    }
}
