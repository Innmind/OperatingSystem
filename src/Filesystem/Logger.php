<?php
declare(strict_types = 1);

namespace Innmind\OperatingSystem\Filesystem;

use Innmind\OperatingSystem\Filesystem;
use Innmind\Filesystem\Adapter;
use Innmind\Url\Path;
use Innmind\FileWatch\Ping;
use Innmind\Immutable\Maybe;
use Psr\Log\LoggerInterface;

final class Logger implements Filesystem
{
    private Filesystem $filesystem;
    private LoggerInterface $logger;

    private function __construct(
        Filesystem $filesystem,
        LoggerInterface $logger,
    ) {
        $this->filesystem = $filesystem;
        $this->logger = $logger;
    }

    public static function psr(
        Filesystem $filesystem,
        LoggerInterface $logger,
    ): self {
        return new self($filesystem, $logger);
    }

    public function mount(Path $path): Adapter
    {
        return Adapter\Logger::psr(
            $this->filesystem->mount($path),
            $this->logger,
        );
    }

    public function contains(Path $path): bool
    {
        $contains = $this->filesystem->contains($path);

        $this->logger->debug(
            'Checking if {path} exists, answer: {answer}',
            ['answer' => $contains ? 'yes' : 'no'],
        );

        return $contains;
    }

    public function require(Path $path): Maybe
    {
        return $this
            ->filesystem
            ->require($path)
            ->map(function(mixed $value) use ($path): mixed {
                $this->logger->debug(
                    'PHP file located at {path} loaded in memory',
                    ['path' => $path->toString()],
                );

                return $value;
            });
    }

    public function watch(Path $path): Ping
    {
        return Ping\Logger::psr(
            $this->filesystem->watch($path),
            $path,
            $this->logger,
        );
    }
}
