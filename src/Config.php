<?php
declare(strict_types = 1);

namespace Innmind\OperatingSystem;

use Innmind\TimeContinuum\Clock;
use Innmind\HttpTransport\{
    Transport as HttpTransport,
    Curl,
};
use Innmind\Filesystem\CaseSensitivity;
use Innmind\Server\Status\EnvironmentPath;
use Innmind\TimeWarp\Halt;
use Innmind\IO\IO;

final class Config
{
    /**
     * @param \Closure(HttpTransport): HttpTransport $mapHttpTransport
     */
    private function __construct(
        private Clock $clock,
        private CaseSensitivity $caseSensitivity,
        private IO $io,
        private Halt $halt,
        private EnvironmentPath $path,
        private ?HttpTransport $httpTransport,
        private \Closure $mapHttpTransport,
    ) {
    }

    public static function of(): self
    {
        return new self(
            Clock::live(),
            CaseSensitivity::sensitive,
            IO::fromAmbientAuthority(),
            Halt\Usleep::new(),
            EnvironmentPath::of(match ($path = \getenv('PATH')) {
                false => '',
                default => $path,
            }),
            null,
            static fn(HttpTransport $transport) => $transport,
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
            $this->httpTransport,
            $this->mapHttpTransport,
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
            $this->httpTransport,
            $this->mapHttpTransport,
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
            $this->httpTransport,
            $this->mapHttpTransport,
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
            $this->httpTransport,
            $this->mapHttpTransport,
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
            $this->httpTransport,
            $this->mapHttpTransport,
        );
    }

    /**
     * @psalm-mutation-free
     */
    public function useHttpTransport(HttpTransport $transport): self
    {
        return new self(
            $this->clock,
            $this->caseSensitivity,
            $this->io,
            $this->halt,
            $this->path,
            $transport,
            $this->mapHttpTransport,
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
            $this->httpTransport,
            $map,
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
     */
    public function httpTransport(): HttpTransport
    {
        $transport = $this->httpTransport ?? Curl::of(
            $this->clock,
            $this->io,
        );

        return ($this->mapHttpTransport)($transport);
    }
}
