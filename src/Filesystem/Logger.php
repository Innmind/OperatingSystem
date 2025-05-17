<?php
declare(strict_types = 1);

namespace Innmind\OperatingSystem\Filesystem;

use Innmind\OperatingSystem\Filesystem;
use Innmind\Filesystem\{
    Adapter,
    File\Content,
};
use Innmind\Url\Path;
use Innmind\FileWatch\Ping;
use Innmind\Immutable\{
    Attempt,
    Maybe,
    Sequence,
};
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

    #[\Override]
    public function mount(Path $path): Adapter
    {
        return Adapter\Logger::psr(
            $this->filesystem->mount($path),
            $this->logger,
        );
    }

    #[\Override]
    public function contains(Path $path): bool
    {
        $contains = $this->filesystem->contains($path);

        $this->logger->debug(
            'Checking if {path} exists, answer: {answer}',
            ['answer' => $contains ? 'yes' : 'no'],
        );

        return $contains;
    }

    #[\Override]
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

    #[\Override]
    public function watch(Path $path): Ping
    {
        // todo bring back the ping logger
        return $this->filesystem->watch($path);
    }

    #[\Override]
    public function temporary(Sequence $chunks): Attempt
    {
        return $this
            ->filesystem
            ->temporary($chunks)
            ->map(function(Content $content) {
                $this->logger->debug('Temporary file created');

                return $content;
            });
    }
}
