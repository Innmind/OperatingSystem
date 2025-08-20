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
use Innmind\Filesystem\{
    Adapter as Filesystem,
    CaseSensitivity
};
use Innmind\FileWatch\Watch;
use Innmind\Server\Status\EnvironmentPath;
use Innmind\Signals\Handler;
use Innmind\TimeWarp\Halt;
use Innmind\IO\IO;
use Innmind\Url\{
    Url,
    Path,
};
use Innmind\Immutable\Attempt;
use Formal\AccessLayer;

final class Config
{
    /**
     * @param \Closure(Clock, self): Clock $mapClock
     * @param \Closure(Halt, self): Halt $mapHalt
     * @param \Closure(HttpTransport, self): HttpTransport $mapHttpTransport
     * @param \Closure(Url): AccessLayer\Connection $sql
     * @param \Closure(AccessLayer\Connection, self): AccessLayer\Connection $mapSql
     * @param \Closure(Control\Server, self): Control\Server $mapServerControl
     * @param \Closure(Status\Server, self): Status\Server $mapServerStatus
     * @param \Closure(Watch, self): Watch $mapFileWatch
     * @param \Closure(Path, self): Attempt<Filesystem> $filesystem
     * @param \Closure(Filesystem, self): Filesystem $mapFilesystem
     */
    private function __construct(
        private Clock $clock,
        private \Closure $mapClock,
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
        private \Closure $filesystem,
        private \Closure $mapFilesystem,
        private Handler $signals,
    ) {
    }

    public static function new(): self
    {
        return new self(
            Clock::live(),
            static fn(Clock $clock) => $clock,
            IO::fromAmbientAuthority(),
            Halt\Usleep::new(),
            static fn(Halt $halt, self $config) => $halt,
            EnvironmentPath::of(match ($path = \getenv('PATH')) {
                false => '',
                default => $path,
            }),
            null,
            static fn(HttpTransport $transport, self $config) => $transport,
            static fn(Url $server) => AccessLayer\Connection\Lazy::of(
                static fn() => AccessLayer\Connection\PDO::of($server),
            ),
            static fn(AccessLayer\Connection $connection, self $config) => $connection,
            static fn(Control\Server $server, self $config) => $server,
            static fn(Status\Server $server, self $config) => $server,
            static fn(Watch $watch, self $config) => $watch,
            static fn(Path $path, self $config) => Attempt::of(
                static fn() => Filesystem\Filesystem::mount(
                    $path,
                    $config->io(),
                )->withCaseSensitivity(CaseSensitivity::sensitive),
            ),
            static fn(Filesystem $filesystem, self $config) => $filesystem,
            Handler::main(),
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
            $this->filesystem,
            $this->mapFilesystem,
            $this->signals,
        );
    }

    /**
     * @psalm-mutation-free
     *
     * @param \Closure(Clock, self): Clock $map
     */
    public function mapClock(\Closure $map): self
    {
        $previous = $this->mapClock;

        return new self(
            $this->clock,
            static fn(Clock $clock, self $config) => $map(
                $previous($clock, $config),
                $config,
            ),
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
            $this->filesystem,
            $this->mapFilesystem,
            $this->signals,
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
            $this->filesystem,
            $this->mapFilesystem,
            $this->signals,
        );
    }

    /**
     * @psalm-mutation-free
     *
     * @param \Closure(Halt, self): Halt $map
     */
    public function mapHalt(\Closure $map): self
    {
        $previous = $this->mapHalt;

        /** @psalm-suppress ImpureFunctionCall */
        return new self(
            $this->clock,
            $this->mapClock,
            $this->io,
            $this->halt,
            static fn(Halt $halt, self $config) => $map(
                $previous($halt, $config),
                $config,
            ),
            $this->path,
            $this->httpTransport,
            $this->mapHttpTransport,
            $this->sql,
            $this->mapSql,
            $this->mapServerControl,
            $this->mapServerStatus,
            $this->mapFileWatch,
            $this->filesystem,
            $this->mapFilesystem,
            $this->signals,
        );
    }

    /**
     * @psalm-mutation-free
     */
    public function withIO(IO $io): self
    {
        return new self(
            $this->clock,
            $this->mapClock,
            $io,
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
            $this->filesystem,
            $this->mapFilesystem,
            $this->signals,
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
            $this->filesystem,
            $this->mapFilesystem,
            $this->signals,
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
            $this->filesystem,
            $this->mapFilesystem,
            $this->signals,
        );
    }

    /**
     * @psalm-mutation-free
     *
     * @param \Closure(HttpTransport, self): HttpTransport $map
     */
    public function mapHttpTransport(\Closure $map): self
    {
        $previous = $this->mapHttpTransport;

        return new self(
            $this->clock,
            $this->mapClock,
            $this->io,
            $this->halt,
            $this->mapHalt,
            $this->path,
            $this->httpTransport,
            static fn(HttpTransport $transport, self $config) => $map(
                $previous($transport, $config),
                $config,
            ),
            $this->sql,
            $this->mapSql,
            $this->mapServerControl,
            $this->mapServerStatus,
            $this->mapFileWatch,
            $this->filesystem,
            $this->mapFilesystem,
            $this->signals,
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
            $this->filesystem,
            $this->mapFilesystem,
            $this->signals,
        );
    }

    /**
     * @psalm-mutation-free
     *
     * @param \Closure(AccessLayer\Connection, self): AccessLayer\Connection $map
     */
    public function mapSQLConnection(\Closure $map): self
    {
        $previous = $this->mapSql;

        return new self(
            $this->clock,
            $this->mapClock,
            $this->io,
            $this->halt,
            $this->mapHalt,
            $this->path,
            $this->httpTransport,
            $this->mapHttpTransport,
            $this->sql,
            static fn(AccessLayer\Connection $connection, self $config) => $map(
                $previous($connection, $config),
                $config,
            ),
            $this->mapServerControl,
            $this->mapServerStatus,
            $this->mapFileWatch,
            $this->filesystem,
            $this->mapFilesystem,
            $this->signals,
        );
    }

    /**
     * @psalm-mutation-free
     *
     * @param \Closure(Control\Server, self): Control\Server $map
     */
    public function mapServerControl(\Closure $map): self
    {
        $previous = $this->mapServerControl;

        return new self(
            $this->clock,
            $this->mapClock,
            $this->io,
            $this->halt,
            $this->mapHalt,
            $this->path,
            $this->httpTransport,
            $this->mapHttpTransport,
            $this->sql,
            $this->mapSql,
            static fn(Control\Server $server, self $config) => $map(
                $previous($server, $config),
                $config,
            ),
            $this->mapServerStatus,
            $this->mapFileWatch,
            $this->filesystem,
            $this->mapFilesystem,
            $this->signals,
        );
    }

    /**
     * @psalm-mutation-free
     *
     * @param \Closure(Status\Server, self): Status\Server $map
     */
    public function mapServerStatus(\Closure $map): self
    {
        $previous = $this->mapServerStatus;

        return new self(
            $this->clock,
            $this->mapClock,
            $this->io,
            $this->halt,
            $this->mapHalt,
            $this->path,
            $this->httpTransport,
            $this->mapHttpTransport,
            $this->sql,
            $this->mapSql,
            $this->mapServerControl,
            static fn(Status\Server $server, self $config) => $map(
                $previous($server, $config),
                $config,
            ),
            $this->mapFileWatch,
            $this->filesystem,
            $this->mapFilesystem,
            $this->signals,
        );
    }

    /**
     * @psalm-mutation-free
     *
     * @param \Closure(Watch, self): Watch $map
     */
    public function mapFileWatch(\Closure $map): self
    {
        $previous = $this->mapFileWatch;

        return new self(
            $this->clock,
            $this->mapClock,
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
            static fn(Watch $watch, self $config) => $map(
                $previous($watch, $config),
                $config,
            ),
            $this->filesystem,
            $this->mapFilesystem,
            $this->signals,
        );
    }

    /**
     * @psalm-mutation-free
     *
     * @param \Closure(Path, self): Attempt<Filesystem> $filesystem
     */
    public function mountFilesystemVia(\Closure $filesystem): self
    {
        return new self(
            $this->clock,
            $this->mapClock,
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
            $filesystem,
            $this->mapFilesystem,
            $this->signals,
        );
    }

    /**
     * @psalm-mutation-free
     *
     * @param \Closure(Filesystem, self): Filesystem $map
     */
    public function mapFilesystem(\Closure $map): self
    {
        $previous = $this->mapFilesystem;

        return new self(
            $this->clock,
            $this->mapClock,
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
            $this->filesystem,
            static fn(Filesystem $filesystem, self $config) => $map(
                $previous($filesystem, $config),
                $config,
            ),
            $this->signals,
        );
    }

    /**
     * @psalm-mutation-free
     */
    public function handleSignalsVia(Handler $handler): self
    {
        return new self(
            $this->clock,
            $this->mapClock,
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
            $this->filesystem,
            $this->mapFilesystem,
            $handler,
        );
    }

    /**
     * @internal
     */
    public function clock(): Clock
    {
        return ($this->mapClock)($this->clock, $this);
    }

    /**
     * @internal
     *
     * @return Attempt<Filesystem>
     */
    public function filesystem(Path $path): Attempt
    {
        return ($this->filesystem)($path, $this)->map(
            fn($adapter) => ($this->mapFilesystem)($adapter, $this),
        );
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
        return ($this->mapHalt)($this->halt, $this);
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

        return ($this->mapHttpTransport)($transport, $this);
    }

    /**
     * @internal
     */
    public function sql(Url $url): AccessLayer\Connection
    {
        return ($this->mapSql)(
            ($this->sql)($url),
            $this,
        );
    }

    /**
     * @internal
     */
    public function serverControl(Control\Server $server): Control\Server
    {
        return ($this->mapServerControl)($server, $this);
    }

    /**
     * @internal
     */
    public function serverStatus(Status\Server $server): Status\Server
    {
        return ($this->mapServerStatus)($server, $this);
    }

    /**
     * @internal
     */
    public function fileWatch(Watch $watch): Watch
    {
        return ($this->mapFileWatch)($watch, $this);
    }

    /**
     * @internal
     */
    public function signalsHandler(): Handler
    {
        return $this->signals;
    }
}
