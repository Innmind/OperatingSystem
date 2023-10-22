<?php
declare(strict_types = 1);

namespace Innmind\OperatingSystem;

use Innmind\TimeContinuum\{
    Clock,
    Earth,
    ElapsedPeriod,
};
use Innmind\Filesystem\CaseSensitivity;
use Innmind\Server\Status\EnvironmentPath;
use Innmind\IO\IO;
use Innmind\Stream\{
    Capabilities,
    Streams,
};
use Innmind\Immutable\Maybe;

final class Config
{
    private Clock $clock;
    private CaseSensitivity $caseSensitivity;
    private Capabilities $streamCapabilities;
    private IO $io;
    private EnvironmentPath $path;
    /** @var Maybe<positive-int> */
    private Maybe $maxHttpConcurrency;

    /**
     * @param Maybe<positive-int> $maxHttpConcurrency
     */
    private function __construct(
        Clock $clock,
        CaseSensitivity $caseSensitivity,
        Capabilities $streamCapabilities,
        IO $io,
        EnvironmentPath $path,
        Maybe $maxHttpConcurrency,
    ) {
        $this->clock = $clock;
        $this->caseSensitivity = $caseSensitivity;
        $this->streamCapabilities = $streamCapabilities;
        $this->io = $io;
        $this->path = $path;
        $this->maxHttpConcurrency = $maxHttpConcurrency;
    }

    public static function of(): self
    {
        /** @var Maybe<positive-int> */
        $maxHttpConcurrency = Maybe::nothing();

        return new self(
            new Earth\Clock,
            CaseSensitivity::sensitive,
            $streams = Streams::fromAmbientAuthority(),
            IO::of(static fn(?ElapsedPeriod $timeout) => match ($timeout) {
                null => $streams->watch()->waitForever(),
                default => $streams->watch()->timeoutAfter($timeout),
            }),
            EnvironmentPath::of(\getenv('PATH') ?: ''),
            $maxHttpConcurrency,
        );
    }

    /**
     * @psalm-mutation-free
     */
    public function withClock(Clock $clock): self
    {
        return new self(
            $clock,
            $this->caseSensitivity,
            $this->streamCapabilities,
            $this->io,
            $this->path,
            $this->maxHttpConcurrency,
        );
    }

    /**
     * @psalm-mutation-free
     */
    public function caseInsensitiveFilesystem(): self
    {
        return new self(
            $this->clock,
            CaseSensitivity::insensitive,
            $this->streamCapabilities,
            $this->io,
            $this->path,
            $this->maxHttpConcurrency,
        );
    }

    /**
     * @psalm-mutation-free
     */
    public function useStreamCapabilities(Capabilities $capabilities): self
    {
        /** @psalm-suppress ImpureMethodCall This should be solved in the next innmind/io release */
        return new self(
            $this->clock,
            $this->caseSensitivity,
            $capabilities,
            IO::of(static fn(?ElapsedPeriod $timeout) => match ($timeout) {
                null => $capabilities->watch()->waitForever(),
                default => $capabilities->watch()->timeoutAfter($timeout),
            }),
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
            $this->clock,
            $this->caseSensitivity,
            $this->streamCapabilities,
            $this->io,
            $path,
            $this->maxHttpConcurrency,
        );
    }

    /**
     * @psalm-mutation-free
     *
     * @param positive-int $max
     */
    public function limitHttpConcurrencyTo(int $max): self
    {
        return new self(
            $this->clock,
            $this->caseSensitivity,
            $this->streamCapabilities,
            $this->io,
            $this->path,
            Maybe::just($max),
        );
    }

    /**
     * @internal
     */
    public function clock(): Clock
    {
        return $this->clock;
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
    public function io(): IO
    {
        return $this->io;
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
