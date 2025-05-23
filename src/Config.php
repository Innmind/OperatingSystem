<?php
declare(strict_types = 1);

namespace Innmind\OperatingSystem;

use Innmind\TimeContinuum\{
    Clock,
    Period,
};
use Innmind\HttpTransport\Transport as HttpTransport;
use Innmind\Filesystem\CaseSensitivity;
use Innmind\Server\Status\EnvironmentPath;
use Innmind\TimeWarp\Halt;
use Innmind\IO\IO;
use Innmind\Immutable\Maybe;

final class Config
{
    /**
     * @param Maybe<positive-int> $maxHttpConcurrency
     * @param Maybe<array{Period, callable(): void}> $httpHeartbeat
     * @param \Closure(HttpTransport): HttpTransport $mapHttpTransport
     */
    private function __construct(
        private Clock $clock,
        private CaseSensitivity $caseSensitivity,
        private IO $io,
        private Halt $halt,
        private EnvironmentPath $path,
        private Maybe $maxHttpConcurrency,
        private Maybe $httpHeartbeat,
        private \Closure $mapHttpTransport,
        private bool $disableSSLVerification,
    ) {
    }

    public static function of(): self
    {
        /** @var Maybe<positive-int> */
        $maxHttpConcurrency = Maybe::nothing();
        /** @var Maybe<array{Period, callable(): void}> */
        $httpHeartbeat = Maybe::nothing();

        return new self(
            Clock::live(),
            CaseSensitivity::sensitive,
            IO::fromAmbientAuthority(),
            Halt\Usleep::new(),
            EnvironmentPath::of(match ($path = \getenv('PATH')) {
                false => '',
                default => $path,
            }),
            $maxHttpConcurrency,
            $httpHeartbeat,
            static fn(HttpTransport $transport) => $transport,
            false,
        );
    }

    /**
     * @psalm-mutation-free
     *
     * @param callable(self): self $map
     */
    public function map(callable $map): self
    {
        /** @psalm-suppress ImpureFunctionCall */
        return $map($this);
    }

    /**
     * @psalm-mutation-free
     */
    public function withClock(Clock $clock): self
    {
        return new self(
            $clock,
            $this->caseSensitivity,
            $this->io,
            $this->halt,
            $this->path,
            $this->maxHttpConcurrency,
            $this->httpHeartbeat,
            $this->mapHttpTransport,
            $this->disableSSLVerification,
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
            $this->io,
            $this->halt,
            $this->path,
            $this->maxHttpConcurrency,
            $this->httpHeartbeat,
            $this->mapHttpTransport,
            $this->disableSSLVerification,
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
            $this->io,
            $halt,
            $this->path,
            $this->maxHttpConcurrency,
            $this->httpHeartbeat,
            $this->mapHttpTransport,
            $this->disableSSLVerification,
        );
    }

    /**
     * @psalm-mutation-free
     *
     * @param callable(Halt): Halt $map
     */
    public function mapHalt(callable $map): self
    {
        /** @psalm-suppress ImpureFunctionCall */
        return new self(
            $this->clock,
            $this->caseSensitivity,
            $this->io,
            $map($this->halt),
            $this->path,
            $this->maxHttpConcurrency,
            $this->httpHeartbeat,
            $this->mapHttpTransport,
            $this->disableSSLVerification,
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
            $this->io,
            $this->halt,
            $path,
            $this->maxHttpConcurrency,
            $this->httpHeartbeat,
            $this->mapHttpTransport,
            $this->disableSSLVerification,
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
            $this->io,
            $this->halt,
            $this->path,
            Maybe::just($max),
            $this->httpHeartbeat,
            $this->mapHttpTransport,
            $this->disableSSLVerification,
        );
    }

    /**
     * @psalm-mutation-free
     *
     * @param callable(): void $heartbeat
     */
    public function withHttpHeartbeat(Period $timeout, callable $heartbeat): self
    {
        return new self(
            $this->clock,
            $this->caseSensitivity,
            $this->io,
            $this->halt,
            $this->path,
            $this->maxHttpConcurrency,
            Maybe::just([$timeout, $heartbeat]),
            $this->mapHttpTransport,
            $this->disableSSLVerification,
        );
    }

    /**
     * @psalm-mutation-free
     *
     * @param \Closure(HttpTransport): HttpTransport $map
     */
    public function mapHttpTransport(\Closure $map): self
    {
        return new self(
            $this->clock,
            $this->caseSensitivity,
            $this->io,
            $this->halt,
            $this->path,
            $this->maxHttpConcurrency,
            $this->httpHeartbeat,
            $map,
            $this->disableSSLVerification,
        );
    }

    /**
     * @psalm-mutation-free
     */
    public function disableSSLVerification(): self
    {
        return new self(
            $this->clock,
            $this->caseSensitivity,
            $this->io,
            $this->halt,
            $this->path,
            $this->maxHttpConcurrency,
            $this->httpHeartbeat,
            $this->mapHttpTransport,
            true,
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
     * @return Maybe<array{Period, callable(): void}>
     */
    public function httpHeartbeat(): Maybe
    {
        return $this->httpHeartbeat;
    }

    /**
     * @internal
     *
     * @return \Closure(HttpTransport): HttpTransport
     */
    public function httpTransportMapper(): \Closure
    {
        return $this->mapHttpTransport;
    }

    /**
     * @internal
     */
    public function mustDisableSSLVerification(): bool
    {
        return $this->disableSSLVerification;
    }
}
