<?php
declare(strict_types = 1);

namespace Innmind\OperatingSystem\Filesystem;

use Innmind\OperatingSystem\Filesystem;
use Innmind\Filesystem\Adapter;
use Innmind\Url\Path;
use Innmind\FileWatch\Ping;
use Psr\Log\LoggerInterface;

final class Logger implements Filesystem
{
    private Filesystem $filesystem;
    private LoggerInterface $logger;

    public function __construct(
        Filesystem $filesystem,
        LoggerInterface $logger
    ) {
        $this->filesystem = $filesystem;
        $this->logger = $logger;
    }

    public function mount(Path $path): Adapter
    {
        return new Adapter\Logger(
            $this->filesystem->mount($path),
            $this->logger,
        );
    }

    public function contains(Path $path): bool
    {
        $contains = $this->filesystem->contains($path);

        $this->logger->info(
            'Checking if {path} exists, answer: {answer}',
            ['answer' => $contains ? 'yes' : 'no'],
        );

        return $contains;
    }

    public function watch(Path $path): Ping
    {
        return new Ping\Logger(
            $this->filesystem->watch($path),
            $path,
            $this->logger,
        );
    }
}
