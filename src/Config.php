<?php
declare(strict_types = 1);

namespace Innmind\OperatingSystem;

use Innmind\Server\Control;
use Innmind\Server\Status;
use Innmind\TimeContinuum\Clock;
use Innmind\HttpTransport\{
    Transport as HttpTransport,
    Curl,
};
use Innmind\Filesystem\CaseSensitivity;
use Innmind\FileWatch\Watch;
use Innmind\Server\Status\EnvironmentPath;
use Innmind\TimeWarp\Halt;
use Innmind\IO\IO;
use Innmind\Url\Url;
use Formal\AccessLayer;

final class Config
{
    /**
     * @param \Closure(Clock): Clock $mapClock
     * @param \Closure(Halt): Halt $mapHalt
     * @param \Closure(HttpTransport): HttpTransport $mapHttpTransport
     * @param \Closure(Url): AccessLayer\Connection $sql
     * @param \Closure(AccessLayer\Connection): AccessLayer\Connection $mapSql
     * @param \Closure(Control\Server): Control\Server $mapServerControl
     * @param \Closure(Status\Server): Status\Server $mapServerStatus
     * @param \Closure(Watch): Watch $mapFileWatch
     */
    private function __construct(
        private Clock $clock,
        private \Closure $mapClock,
        private CaseSensitivity $caseSensitivity,
        private IO $io,
        private Halt $halt,
        private \Closure $mapHalt,
        private EnvironmentPath $path,
        private ?HttpTransport $httpTransport,
        private \Closure $mapHttpTransport,
        private \Closure $sql,
        private \Closure $mapSql,
        private \Closure $mapServerControl,
        private \Closure $mapServerStatus,
        private \Closure $mapFileWatch,
    ) {
    }

    public static function of(): self
    {
        return new self(
            Clock::live(),
            static fn(Clock $clock) => $clock,
            CaseSensitivity::sensitive,
            IO::fromAmbientAuthority(),
            Halt\Usleep::new(),
            static fn(Halt $halt) => $halt,
            EnvironmentPath::of(match ($path = \getenv('PATH')) {
                false => '',
                default => $path,
            }),
            null,
            static fn(HttpTransport $transport) => $transport,
            static fn(Url $server) => AccessLayer\Connection\Lazy::of(
                static fn() => AccessLayer\Connection\PDO::of($server),
            ),
            static fn(AccessLayer\Connection $connection) => $connection,
            static fn(Control\Server $server) => $server,
            static fn(Status\Server $server) => $server,
            static fn(Watch $watch) => $watch,
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
            $this->mapClock,
            $this->caseSensitivity,
            $this->io,
            $this->halt,
            $this->mapHalt,
            $this->path,
            $this->httpTransport,
            $this->mapHttpTransport,
            $this->sql,
            $this->mapSql,
            $this->mapServerControl,
            $this->mapServerStatus,
            $this->mapFileWatch,
        );
    }

    /**
     * @psalm-mutation-free
     *
     * @param \Closure(Clock): Clock $map
     */
    public function mapClock(\Closure $map): self
    {
        $previous = $this->mapClock;

        return new self(
            $this->clock,
            static fn(Clock $clock) => $map($previous($clock)),
            $this->caseSensitivity,
            $this->io,
            $this->halt,
            $this->mapHalt,
            $this->path,
            $this->httpTransport,
            $this->mapHttpTransport,
            $this->sql,
            $this->mapSql,
            $this->mapServerControl,
            $this->mapServerStatus,
            $this->mapFileWatch,
        );
    }

    /**
     * @psalm-mutation-free
     */
    public function caseInsensitiveFilesystem(): self
    {
        return new self(
            $this->clock,
            $this->mapClock,
            CaseSensitivity::insensitive,
            $this->io,
            $this->halt,
            $this->mapHalt,
            $this->path,
            $this->httpTransport,
            $this->mapHttpTransport,
            $this->sql,
            $this->mapSql,
            $this->mapServerControl,
            $this->mapServerStatus,
            $this->mapFileWatch,
        );
    }

    /**
     * @psalm-mutation-free
     */
    public function haltProcessVia(Halt $halt): self
    {
        return new self(
            $this->clock,
            $this->mapClock,
            $this->caseSensitivity,
            $this->io,
            $halt,
            $this->mapHalt,
            $this->path,
            $this->httpTransport,
            $this->mapHttpTransport,
            $this->sql,
            $this->mapSql,
            $this->mapServerControl,
            $this->mapServerStatus,
            $this->mapFileWatch,
        );
    }

    /**
     * @psalm-mutation-free
     *
     * @param \Closure(Halt): Halt $map
     */
    public function mapHalt(\Closure $map): self
    {
        $previous = $this->mapHalt;

        /** @psalm-suppress ImpureFunctionCall */
        return new self(
            $this->clock,
            $this->mapClock,
            $this->caseSensitivity,
            $this->io,
            $this->halt,
            static fn(Halt $halt) => $map($previous($halt)),
            $this->path,
            $this->httpTransport,
            $this->mapHttpTransport,
            $this->sql,
            $this->mapSql,
            $this->mapServerControl,
            $this->mapServerStatus,
            $this->mapFileWatch,
        );
    }

    /**
     * @psalm-mutation-free
     */
    public function withEnvironmentPath(EnvironmentPath $path): self
    {
        return new self(
            $this->clock,
            $this->mapClock,
            $this->caseSensitivity,
            $this->io,
            $this->halt,
            $this->mapHalt,
            $path,
            $this->httpTransport,
            $this->mapHttpTransport,
            $this->sql,
            $this->mapSql,
            $this->mapServerControl,
            $this->mapServerStatus,
            $this->mapFileWatch,
        );
    }

    /**
     * @psalm-mutation-free
     */
    public function useHttpTransport(HttpTransport $transport): self
    {
        return new self(
            $this->clock,
            $this->mapClock,
            $this->caseSensitivity,
            $this->io,
            $this->halt,
            $this->mapHalt,
            $this->path,
            $transport,
            $this->mapHttpTransport,
            $this->sql,
            $this->mapSql,
            $this->mapServerControl,
            $this->mapServerStatus,
            $this->mapFileWatch,
        );
    }

    /**
     * @psalm-mutation-free
     *
     * @param \Closure(HttpTransport): HttpTransport $map
     */
    public function mapHttpTransport(\Closure $map): self
    {
        $previous = $this->mapHttpTransport;

        return new self(
            $this->clock,
            $this->mapClock,
            $this->caseSensitivity,
            $this->io,
            $this->halt,
            $this->mapHalt,
            $this->path,
            $this->httpTransport,
            static fn(HttpTransport $transport) => $map($previous($transport)),
            $this->sql,
            $this->mapSql,
            $this->mapServerControl,
            $this->mapServerStatus,
            $this->mapFileWatch,
        );
    }

    /**
     * @psalm-mutation-free
     *
     * @param \Closure(Url): AccessLayer\Connection $sql
     */
    public function openSQLConnectionVia(\Closure $sql): self
    {
        return new self(
            $this->clock,
            $this->mapClock,
            $this->caseSensitivity,
            $this->io,
            $this->halt,
            $this->mapHalt,
            $this->path,
            $this->httpTransport,
            $this->mapHttpTransport,
            $sql,
            $this->mapSql,
            $this->mapServerControl,
            $this->mapServerStatus,
            $this->mapFileWatch,
        );
    }

    /**
     * @psalm-mutation-free
     *
     * @param \Closure(AccessLayer\Connection): AccessLayer\Connection $map
     */
    public function mapSQLConnection(\Closure $map): self
    {
        $previous = $this->mapSql;

        return new self(
            $this->clock,
            $this->mapClock,
            $this->caseSensitivity,
            $this->io,
            $this->halt,
            $this->mapHalt,
            $this->path,
            $this->httpTransport,
            $this->mapHttpTransport,
            $this->sql,
            static fn(AccessLayer\Connection $connection) => $map($previous($connection)),
            $this->mapServerControl,
            $this->mapServerStatus,
            $this->mapFileWatch,
        );
    }

    /**
     * @psalm-mutation-free
     *
     * @param \Closure(Control\Server): Control\Server $map
     */
    public function mapServerControl(\Closure $map): self
    {
        $previous = $this->mapServerControl;

        return new self(
            $this->clock,
            $this->mapClock,
            $this->caseSensitivity,
            $this->io,
            $this->halt,
            $this->mapHalt,
            $this->path,
            $this->httpTransport,
            $this->mapHttpTransport,
            $this->sql,
            $this->mapSql,
            static fn(Control\Server $server) => $map($previous($server)),
            $this->mapServerStatus,
            $this->mapFileWatch,
        );
    }

    /**
     * @psalm-mutation-free
     *
     * @param \Closure(Status\Server): Status\Server $map
     */
    public function mapServerStatus(\Closure $map): self
    {
        $previous = $this->mapServerStatus;

        return new self(
            $this->clock,
            $this->mapClock,
            $this->caseSensitivity,
            $this->io,
            $this->halt,
            $this->mapHalt,
            $this->path,
            $this->httpTransport,
            $this->mapHttpTransport,
            $this->sql,
            $this->mapSql,
            $this->mapServerControl,
            static fn(Status\Server $server) => $map($previous($server)),
            $this->mapFileWatch,
        );
    }

    /**
     * @psalm-mutation-free
     *
     * @param \Closure(Watch): Watch $map
     */
    public function mapFileWatch(\Closure $map): self
    {
        $previous = $this->mapFileWatch;

        return new self(
            $this->clock,
            $this->mapClock,
            $this->caseSensitivity,
            $this->io,
            $this->halt,
            $this->mapHalt,
            $this->path,
            $this->httpTransport,
            $this->mapHttpTransport,
            $this->sql,
            $this->mapSql,
            $this->mapServerControl,
            $this->mapServerStatus,
            static fn(Watch $watch) => $map($previous($watch)),
        );
    }

    /**
     * @internal
     */
    public function clock(): Clock
    {
        return ($this->mapClock)($this->clock);
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
        return ($this->mapHalt)($this->halt);
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

    /**
     * @internal
     */
    public function sql(Url $url): AccessLayer\Connection
    {
        return ($this->mapSql)(
            ($this->sql)($url),
        );
    }

    /**
     * @internal
     *
     * @return \Closure(Control\Server): Control\Server
     */
    public function serverControlMapper(): \Closure
    {
        return $this->mapServerControl;
    }

    /**
     * @internal
     *
     * @return \Closure(Status\Server): Status\Server
     */
    public function serverStatusMapper(): \Closure
    {
        return $this->mapServerStatus;
    }

    /**
     * @internal
     *
     * @return \Closure(Watch): Watch
     */
    public function fileWatchMapper(): \Closure
    {
        return $this->mapFileWatch;
    }
}
