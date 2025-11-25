<?php
declare(strict_types = 1);

namespace Innmind\OperatingSystem;

use Innmind\Server\{
    Control,
    Status,
    Status\EnvironmentPath,
    Status\ServerFactory,
};
use Innmind\TimeContinuum\Clock;
use Innmind\HttpTransport\Transport as HttpTransport;
use Innmind\Filesystem\{
    Adapter as Filesystem,
    CaseSensitivity,
    Exception\RecoverMount,
};
use Innmind\FileWatch\Watch;
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
     * @psalm-mutation-free
     *
     * @param \Closure(Clock, self): Clock $mapClock
     * @param \Closure(Halt, self): Halt $mapHalt
     * @param \Closure(HttpTransport, self): HttpTransport $mapHttpTransport
     * @param \Closure(Url): Attempt<AccessLayer\Connection> $sql
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
        private ?Control\Server $serverControl,
        private \Closure $mapServerControl,
        private ?Status\Server $serverStatus,
        private \Closure $mapServerStatus,
        private ?Watch $watch,
        private \Closure $mapFileWatch,
        private \Closure $filesystem,
        private \Closure $mapFilesystem,
        private Handler $signals,
    ) {
    }

    #[\NoDiscard]
    public static function new(): self
    {
        return new self(
            Clock::live(),
            static fn(Clock $clock) => $clock,
            IO::fromAmbientAuthority(),
            Halt::new(),
            static fn(Halt $halt, self $config) => $halt,
            EnvironmentPath::of(match ($path = \getenv('PATH')) {
                false => '',
                default => $path,
            }),
            null,
            static fn(HttpTransport $transport, self $config) => $transport,
            static fn(Url $server) => AccessLayer\Connection::new($server),
            static fn(AccessLayer\Connection $connection, self $config) => $connection,
            null,
            static fn(Control\Server $server, self $config) => $server,
            null,
            static fn(Status\Server $server, self $config) => $server,
            null,
            static fn(Watch $watch, self $config) => $watch,
            static fn(Path $path, self $config) => Filesystem::mount(
                $path,
                CaseSensitivity::sensitive,
                $config->io(),
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
    #[\NoDiscard]
    public function map(callable $map): self
    {
        /** @psalm-suppress ImpureFunctionCall */
        return $map($this);
    }

    /**
     * @psalm-mutation-free
     */
    #[\NoDiscard]
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
            $this->serverControl,
            $this->mapServerControl,
            $this->serverStatus,
            $this->mapServerStatus,
            $this->watch,
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
    #[\NoDiscard]
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
            $this->serverControl,
            $this->mapServerControl,
            $this->serverStatus,
            $this->mapServerStatus,
            $this->watch,
            $this->mapFileWatch,
            $this->filesystem,
            $this->mapFilesystem,
            $this->signals,
        );
    }

    /**
     * @psalm-mutation-free
     */
    #[\NoDiscard]
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
            $this->serverControl,
            $this->mapServerControl,
            $this->serverStatus,
            $this->mapServerStatus,
            $this->watch,
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
    #[\NoDiscard]
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
            $this->serverControl,
            $this->mapServerControl,
            $this->serverStatus,
            $this->mapServerStatus,
            $this->watch,
            $this->mapFileWatch,
            $this->filesystem,
            $this->mapFilesystem,
            $this->signals,
        );
    }

    /**
     * @psalm-mutation-free
     */
    #[\NoDiscard]
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
            $this->serverControl,
            $this->mapServerControl,
            $this->serverStatus,
            $this->mapServerStatus,
            $this->watch,
            $this->mapFileWatch,
            $this->filesystem,
            $this->mapFilesystem,
            $this->signals,
        );
    }

    /**
     * @psalm-mutation-free
     */
    #[\NoDiscard]
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
            $this->serverControl,
            $this->mapServerControl,
            $this->serverStatus,
            $this->mapServerStatus,
            $this->watch,
            $this->mapFileWatch,
            $this->filesystem,
            $this->mapFilesystem,
            $this->signals,
        );
    }

    /**
     * @psalm-mutation-free
     */
    #[\NoDiscard]
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
            $this->serverControl,
            $this->mapServerControl,
            $this->serverStatus,
            $this->mapServerStatus,
            $this->watch,
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
    #[\NoDiscard]
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
            $this->serverControl,
            $this->mapServerControl,
            $this->serverStatus,
            $this->mapServerStatus,
            $this->watch,
            $this->mapFileWatch,
            $this->filesystem,
            $this->mapFilesystem,
            $this->signals,
        );
    }

    /**
     * @psalm-mutation-free
     *
     * @param \Closure(Url): Attempt<AccessLayer\Connection> $sql
     */
    #[\NoDiscard]
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
            $this->serverControl,
            $this->mapServerControl,
            $this->serverStatus,
            $this->mapServerStatus,
            $this->watch,
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
    #[\NoDiscard]
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
            $this->serverControl,
            $this->mapServerControl,
            $this->serverStatus,
            $this->mapServerStatus,
            $this->watch,
            $this->mapFileWatch,
            $this->filesystem,
            $this->mapFilesystem,
            $this->signals,
        );
    }

    /**
     * @psalm-mutation-free
     */
    #[\NoDiscard]
    public function useServerControl(Control\Server $server): self
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
            $server,
            $this->mapServerControl,
            $this->serverStatus,
            $this->mapServerStatus,
            $this->watch,
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
    #[\NoDiscard]
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
            $this->serverControl,
            static fn(Control\Server $server, self $config) => $map(
                $previous($server, $config),
                $config,
            ),
            $this->serverStatus,
            $this->mapServerStatus,
            $this->watch,
            $this->mapFileWatch,
            $this->filesystem,
            $this->mapFilesystem,
            $this->signals,
        );
    }

    /**
     * @psalm-mutation-free
     */
    #[\NoDiscard]
    public function useServerStatus(Status\Server $server): self
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
            $this->serverControl,
            $this->mapServerControl,
            $server,
            $this->mapServerStatus,
            $this->watch,
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
    #[\NoDiscard]
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
            $this->serverControl,
            $this->mapServerControl,
            $this->serverStatus,
            static fn(Status\Server $server, self $config) => $map(
                $previous($server, $config),
                $config,
            ),
            $this->watch,
            $this->mapFileWatch,
            $this->filesystem,
            $this->mapFilesystem,
            $this->signals,
        );
    }

    /**
     * @psalm-mutation-free
     */
    #[\NoDiscard]
    public function useFileWatch(Watch $watch): self
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
            $this->serverControl,
            $this->mapServerControl,
            $this->serverStatus,
            $this->mapServerStatus,
            $watch,
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
    #[\NoDiscard]
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
            $this->serverControl,
            $this->mapServerControl,
            $this->serverStatus,
            $this->mapServerStatus,
            $this->watch,
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
    #[\NoDiscard]
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
            $this->serverControl,
            $this->mapServerControl,
            $this->serverStatus,
            $this->mapServerStatus,
            $this->watch,
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
    #[\NoDiscard]
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
            $this->serverControl,
            $this->mapServerControl,
            $this->serverStatus,
            $this->mapServerStatus,
            $this->watch,
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
    #[\NoDiscard]
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
            $this->serverControl,
            $this->mapServerControl,
            $this->serverStatus,
            $this->mapServerStatus,
            $this->watch,
            $this->mapFileWatch,
            $this->filesystem,
            $this->mapFilesystem,
            $handler,
        );
    }

    /**
     * @internal
     */
    #[\NoDiscard]
    public function clock(): Clock
    {
        return ($this->mapClock)($this->clock, $this);
    }

    /**
     * @internal
     *
     * @return Attempt<Filesystem>
     */
    #[\NoDiscard]
    public function filesystem(Path $path): Attempt
    {
        $map = fn(Filesystem $adapter): Filesystem => ($this->mapFilesystem)(
            $adapter,
            $this,
        );

        return ($this->filesystem)($path, $this)
            ->mapError(static fn($e) => match (true) {
                $e instanceof RecoverMount => new RecoverMount(
                    static fn() => $e
                        ->recover()
                        ->map($map),
                ),
                default => $e,
            })
            ->map($map);
    }

    /**
     * @internal
     */
    #[\NoDiscard]
    public function io(): IO
    {
        return $this->io;
    }

    /**
     * @internal
     */
    #[\NoDiscard]
    public function halt(): Halt
    {
        return ($this->mapHalt)($this->halt, $this);
    }

    /**
     * @internal
     */
    #[\NoDiscard]
    public function environmentPath(): EnvironmentPath
    {
        return $this->path;
    }

    /**
     * @internal
     */
    #[\NoDiscard]
    public function httpTransport(): HttpTransport
    {
        $transport = $this->httpTransport ?? HttpTransport::curl(
            $this->clock,
            $this->io,
        );

        return ($this->mapHttpTransport)($transport, $this);
    }

    /**
     * @internal
     *
     * @return Attempt<AccessLayer\Connection>
     */
    #[\NoDiscard]
    public function sql(Url $url): Attempt
    {
        $map = $this->mapSql;
        $self = $this;

        return ($this->sql)($url)->map(
            static fn($connection) => $map($connection, $self),
        );
    }

    /**
     * @internal
     */
    #[\NoDiscard]
    public function serverControl(): Control\Server
    {
        return ($this->mapServerControl)(
            $this->serverControl ?? Control\Server::new(
                $this->clock,
                $this->io,
                $this->halt(),
            ),
            $this,
        );
    }

    /**
     * @internal
     */
    #[\NoDiscard]
    public function serverStatus(Control\Server $server): Status\Server
    {
        return ($this->mapServerStatus)(
            $this->serverStatus ?? ServerFactory::build(
                $this->clock(),
                $server,
                $this->path,
            ),
            $this,
        );
    }

    /**
     * @internal
     */
    #[\NoDiscard]
    public function fileWatch(Control\Server\Processes $processes): Watch
    {
        return ($this->mapFileWatch)(
            $this->watch ?? Watch::of($processes, $this->halt()),
            $this,
        );
    }

    /**
     * @internal
     */
    #[\NoDiscard]
    public function signalsHandler(): Handler
    {
        return $this->signals;
    }
}
