<?php
declare(strict_types = 1);

namespace Innmind\OperatingSystem;

use Innmind\Filesystem\CaseSensitivity;
use Innmind\Stream\{
    Capabilities,
    Streams,
};
use Innmind\Immutable\Map;

final class Config
{
    private CaseSensitivity $caseSensitivity;
    private Capabilities $streamCapabilities;
    /** @var Map<non-empty-string, string> */
    private Map $environment;

    /**
     * @param Map<non-empty-string, string> $environment
     */
    private function __construct(
        CaseSensitivity $caseSensitivity,
        Capabilities $streamCapabilities,
        Map $environment,
    ) {
        $this->caseSensitivity = $caseSensitivity;
        $this->streamCapabilities = $streamCapabilities;
        $this->environment = $environment;
    }

    public static function of(): self
    {
        /** @var Map<non-empty-string, string> */
        $environment = Map::of();

        // this is required for innmind/server-status to work
        if (\array_key_exists('PATH', $_SERVER)) {
            $environment = ($environment)('PATH', $_SERVER['PATH']);
        }

        return new self(
            CaseSensitivity::sensitive,
            Streams::fromAmbientAuthority(),
            $environment,
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
            $this->environment,
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
            $this->environment,
        );
    }

    /**
     * @psalm-mutation-free
     *
     * @param Map<non-empty-string, string> $environment
     */
    public function withEnvironment(Map $environment): self
    {
        return new self(
            $this->caseSensitivity,
            $this->streamCapabilities,
            $environment,
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
     *
     * @return Map<non-empty-string, string>
     */
    public function environment(): Map
    {
        return $this->environment;
    }
}
