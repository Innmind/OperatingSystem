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
use Innmind\TimeWarp\Halt;
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
    private Halt $halt;
    private EnvironmentPath $path;
    /** @var Maybe<positive-int> */
    private Maybe $maxHttpConcurrency;
    /** @var Maybe<array{ElapsedPeriod, callable(): void}> */
    private Maybe $httpHeartbeat;

    /**
     * @param Maybe<positive-int> $maxHttpConcurrency
     * @param Maybe<array{ElapsedPeriod, callable(): void}> $httpHeartbeat
     */
    private function __construct(
        Clock $clock,
        CaseSensitivity $caseSensitivity,
        Capabilities $streamCapabilities,
        IO $io,
        Halt $halt,
        EnvironmentPath $path,
        Maybe $maxHttpConcurrency,
        Maybe $httpHeartbeat,
    ) {
        $this->clock = $clock;
        $this->caseSensitivity = $caseSensitivity;
        $this->streamCapabilities = $streamCapabilities;
        $this->io = $io;
        $this->halt = $halt;
        $this->path = $path;
        $this->maxHttpConcurrency = $maxHttpConcurrency;
        $this->httpHeartbeat = $httpHeartbeat;
    }

    public static function of(): self
    {
        /** @var Maybe<positive-int> */
        $maxHttpConcurrency = Maybe::nothing();
        /** @var Maybe<array{ElapsedPeriod, callable(): void}> */
        $httpHeartbeat = Maybe::nothing();

        return new self(
            new Earth\Clock,
            CaseSensitivity::sensitive,
            $streams = Streams::fromAmbientAuthority(),
            IO::of(static fn(?ElapsedPeriod $timeout) => match ($timeout) {
                null => $streams->watch()->waitForever(),
                default => $streams->watch()->timeoutAfter($timeout),
            }),
            new Halt\Usleep,
            EnvironmentPath::of(\getenv('PATH') ?: ''),
            $maxHttpConcurrency,
            $httpHeartbeat,
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
            $this->halt,
            $this->path,
            $this->maxHttpConcurrency,
            $this->httpHeartbeat,
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
            $this->halt,
            $this->path,
            $this->maxHttpConcurrency,
            $this->httpHeartbeat,
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
            $this->halt,
            $this->path,
            $this->maxHttpConcurrency,
            $this->httpHeartbeat,
        );
    }

    /**
     * @psalm-mutation-free
     */
    public function haltProcessVia(Halt $halt): self
    {
        return new self(
            $this->clock,
            $this->caseSensitivity,
            $this->streamCapabilities,
            $this->io,
            $halt,
            $this->path,
            $this->maxHttpConcurrency,
            $this->httpHeartbeat,
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
            $this->halt,
            $path,
            $this->maxHttpConcurrency,
            $this->httpHeartbeat,
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
            $this->halt,
            $this->path,
            Maybe::just($max),
            $this->httpHeartbeat,
        );
    }

    /**
     * @psalm-mutation-free
     *
     * @param callable(): void $heartbeat
     */
    public function withHttpHeartbeat(ElapsedPeriod $timeout, callable $heartbeat): self
    {
        return new self(
            $this->clock,
            $this->caseSensitivity,
            $this->streamCapabilities,
            $this->io,
            $this->halt,
            $this->path,
            $this->maxHttpConcurrency,
            Maybe::just([$timeout, $heartbeat]),
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
    public function halt(): Halt
    {
        return $this->halt;
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

    /**
     * @internal
     *
     * @return Maybe<array{ElapsedPeriod, callable(): void}>
     */
    public function httpHeartbeat(): Maybe
    {
        return $this->httpHeartbeat;
    }
}
