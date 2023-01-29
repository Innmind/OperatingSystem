<?php
declare(strict_types = 1);

namespace Innmind\OperatingSystem;

use Innmind\Filesystem\CaseSensitivity;
use Innmind\Server\Status\EnvironmentPath;
use Innmind\Stream\{
    Capabilities,
    Streams,
};

final class Config
{
    private CaseSensitivity $caseSensitivity;
    private Capabilities $streamCapabilities;
    private EnvironmentPath $path;

    private function __construct(
        CaseSensitivity $caseSensitivity,
        Capabilities $streamCapabilities,
        EnvironmentPath $path,
    ) {
        $this->caseSensitivity = $caseSensitivity;
        $this->streamCapabilities = $streamCapabilities;
        $this->path = $path;
    }

    public static function of(): self
    {
        return new self(
            CaseSensitivity::sensitive,
            Streams::fromAmbientAuthority(),
            EnvironmentPath::of(\getenv('PATH') ?: ''),
        );
    }

    /**
     * @psalm-mutation-free
     */
    public function caseInsensitiveFilesystem(): self
    {
        return new self(
            CaseSensitivity::insensitive,
            $this->streamCapabilities,
            $this->path,
        );
    }

    /**
     * @psalm-mutation-free
     */
    public function useStreamCapabilities(Capabilities $capabilities): self
    {
        return new self(
            $this->caseSensitivity,
            $capabilities,
            $this->path,
        );
    }

    /**
     * @psalm-mutation-free
     */
    public function withEnvironmentPath(EnvironmentPath $path): self
    {
        return new self(
            $this->caseSensitivity,
            $this->streamCapabilities,
            $path,
        );
    }

    /**
     * @internal
     */
    public function filesystemCaseSensitivity(): CaseSensitivity
    {
        return $this->caseSensitivity;
    }

    /**
     * @internal
     */
    public function streamCapabilities(): Capabilities
    {
        return $this->streamCapabilities;
    }

    /**
     * @internal
     */
    public function environmentPath(): EnvironmentPath
    {
        return $this->path;
    }
}
