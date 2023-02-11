<?php
declare(strict_types = 1);

namespace Innmind\OperatingSystem;

use Innmind\Filesystem\CaseSensitivity;
use Innmind\Server\Status\EnvironmentPath;
use Innmind\Stream\{
    Capabilities,
    Streams,
};
use Innmind\Immutable\Maybe;

final class Config
{
    private CaseSensitivity $caseSensitivity;
    private Capabilities $streamCapabilities;
    private EnvironmentPath $path;
    /** @var Maybe<positive-int> */
    private Maybe $maxHttpConcurrency;

    /**
     * @param Maybe<positive-int> $maxHttpConcurrency
     */
    private function __construct(
        CaseSensitivity $caseSensitivity,
        Capabilities $streamCapabilities,
        EnvironmentPath $path,
        Maybe $maxHttpConcurrency,
    ) {
        $this->caseSensitivity = $caseSensitivity;
        $this->streamCapabilities = $streamCapabilities;
        $this->path = $path;
        $this->maxHttpConcurrency = $maxHttpConcurrency;
    }

    public static function of(): self
    {
        /** @var Maybe<positive-int> */
        $maxHttpConcurrency = Maybe::nothing();

        return new self(
            CaseSensitivity::sensitive,
            Streams::fromAmbientAuthority(),
            EnvironmentPath::of(\getenv('PATH') ?: ''),
            $maxHttpConcurrency,
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
            $this->maxHttpConcurrency,
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
            $this->maxHttpConcurrency,
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
            $this->maxHttpConcurrency,
        );
    }

    /**
     * @param positive-int $max
     */
    public function limitHttpConcurrencyTo(int $max): self
    {
        return new self(
            $this->caseSensitivity,
            $this->streamCapabilities,
            $this->path,
            Maybe::just($max),
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

    /**
     * @internal
     *
     * @return Maybe<positive-int>
     */
    public function maxHttpConcurrency(): Maybe
    {
        return $this->maxHttpConcurrency;
    }
}
