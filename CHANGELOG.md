# Changelog

## [Unreleased]

### Added

- `Innmind\OperatingSystem\Config::map()`
- `Innmind\OperatingSystem\Config\Logger`
- `Innmind\OperatingSystem\Config\Resilient`
- `Innmind\OperatingSystem\Config::useHttpTransport()`
- `Innmind\OperatingSystem\Config::mapHttpTransport()`
- `Innmind\OperatingSystem\Config::openSQLConnectionVia()`
- `Innmind\OperatingSystem\Config::mapSQLConnection()`
- `Innmind\OperatingSystem\Config::mapServerControl()`
- `Innmind\OperatingSystem\Config::mapServerStatus()`
- `Innmind\OperatingSystem\Config::mapClock()`
- `Innmind\OperatingSystem\Config::mapFileWatch()`
- `Innmind\OperatingSystem\Config::mountFilesystemVia()`
- `Innmind\OperatingSystem\Config::mapFilesystem()`

### Changed

- Requires `innmind/time-continuum:^4.1.1`
- Requires `innmind/server-status:~5.0`
- Requires `innmind/server-control:~6.0`
- Requires `innmind/filesystem:~8.1`
- Requires `innmind/file-watch:~5.0`
- Requires `innmind/http-transport:~8.0`
- Requires `innmind/time-warp:~4.0`
- Requires `innmind/io:~3.2`
- Requires `innmind/immutable:~5.15`
- `Innmind\OperatingSystem\Config::withHttpHeartbeat()` period is now expressed with a `Innmind\TimeContinuum\Period`
- `Innmind\OperatingSystem\CurrentProcess::id()` now returns an `Innmind\Immutable\Attempt`
- `Innmind\OperatingSystem\CurrentProcess::halt()` now returns `Innmind\Immutable\Attempt<Innmind\Immutable\SideEffect>`
- `Innmind\OperatingSystem\Filesystem::mount()` now returns an `Innmind\Immutable\Attempt`
- `Innmind\OperatingSystem\Filesystem::temporary()` now returns an `Innmind\Immutable\Attempt`
- `Innmind\OperatingSystem\Ports::open()` now returns an `Innmind\Immutable\Attempt`
- `Innmind\OperatingSystem\Remote::socket()` now returns an `Innmind\Immutable\Attempt`
- `Innmind\OperatingSystem\Sockets::open()` now returns an `Innmind\Immutable\Attempt`
- `Innmind\OperatingSystem\Sockets::takeOver()` now returns an `Innmind\Immutable\Attempt`
- `Innmind\OperatingSystem\Sockets::connectTo()` now returns an `Innmind\Immutable\Attempt`
- `Innmind\OperatingSystem\OperatingSystem` is now a final class

### Fixed

- PHP `8.4` deprecations

### Removed

- `Innmind\OperatingSystem\Config::useStreamCapabilities()`
- `Innmind\OperatingSystem\Sockets::watch()`
- `Innmind\OperatingSystem\OperatingSystem\Resilient`
- `Innmind\OperatingSystem\Config::limitHttpConcurrencyTo()` use `::useHttpTransport()` instead
- `Innmind\OperatingSystem\Config::withHttpHeartbeat()` use `::useHttpTransport()` instead
- `Innmind\OperatingSystem\Config::disableSSLVerification()` use `::useHttpTransport()` instead
- `Innmind\OperatingSystem\Config::caseInsensitiveFilesystem()` use `::mountFilesystemVia()` instead
- The following informations are no longer logged:
    - the current process id
    - the current process memory
    - the signals listener being added/removed
    - the signals received by the current process
    - temporary files being created
    - opened ports
    - opened remote sockets
    - opened sockets

## 5.2.0 - 2024-07-14

### Changed

- Requires `formal/access-layer:~4.0`

## 5.1.0 - 2024-07-14

### Changed

- Requires `formal/access-layer:~3.0`

## 5.0.0 - 2024-03-10

### Added

- `Innmind\OperatingSystem\Filesystem::temporary()`

### Changed

- `Innmind\OperatingSystem\Remote::socket()` returned socket is now wrapped in a `Innmind\IO\Sockets\Client`
- `Innmind\OperatingSystem\Sockets::connectTo()` returned socket is now wrapped in a `Innmind\IO\Sockets\Client`
- `Innmind\OperatingSystem\Sockets::open()` returned socket is now wrapped in a `Innmind\IO\Sockets\Server`
- `Innmind\OperatingSystem\Sockets::takeOver()` returned socket is now wrapped in a `Innmind\IO\Sockets\Server`
- `Innmind\OperatingSystem\Ports::open()` returned socket is now wrapped in a `Innmind\IO\Sockets\Server`
- `Innmind\OperatingSystem\CurrentProcess\Generic::of()` is now declared `internal`
- `Innmind\OperatingSystem\Filesystem\Generic::of()` is now declared `internal`
- `Innmind\OperatingSystem\Ports\Unix::of()` is now declared `internal`
- `Innmind\OperatingSystem\Remote\Generic::of()` is now declared `internal`
- `Innmind\OperatingSystem\Ports\Sockets::of()` is now declared `internal`
- Requires `innmind/file-watch:~4.0`

## 4.2.0 - 2023-12-14

### Added

- `Innmind\OperatingSystem\Config::disableSSLVerification()`

## 4.1.0 - 2023-11-05

### Added

- `Innmind\OperatingSystem\Config::withHttpHeartbeat()`

## 4.0.0 - 2023-10-22

### Added

- `Innmind\OperatingSystem\OperatingSystem::map()`
- `Innmind\OperatingSystem\Config::haltProcessVia()`

### Changed

- `Innmind\OperatingSystem\Factory::build()` now only accept a `Config` object, use `Config::withClock()` to change the default clock

### Removed

- `Innmind\OperatingSystem\CurrentProcess::fork()`
- `Innmind\OperatingSystem\CurrentProcess::children()`
- `Innmind\OperatingSystem\CurrentProcess\Children`
- `Innmind\OperatingSystem\CurrentProcess\Child`
- `Innmind\OperatingSystem\CurrentProcess\ForkFailed`
- Support for PHP `8.1`

## 3.8.0 - 2023-09-10

### Deprecated

- `Innmind\OperatingSystem\CurrentProcess::fork()`
- `Innmind\OperatingSystem\CurrentProcess::children()`
- `Innmind\OperatingSystem\CurrentProcess\Children`
- `Innmind\OperatingSystem\CurrentProcess\Child`
- `Innmind\OperatingSystem\CurrentProcess\ForkFailed`

## 3.7.0 - 2023-02-25

### Added

- `Innmind\OperatingSystem\OperatingSystem\Unix::config()` (declared as `internal`)

### Changed

- `Innmind\OperatingSystem\Sockets\Unix` now uses the stream `Capabilities` from the `Config`

## 3.6.0 - 2023-02-11

### Added

- `Innmind\OperatingSystem\Config::limitHttpConcurrencyTo()`

### Changed

- Requires `innmind/http-transport:~6.4`

## 3.5.0 - 2023-01-29

### Added

- `Innmind\OperatingSystem\Config::useStreamCapabilities()`
- `Innmind\OperatingSystem\Config::withEnvironmentPath()`

### Changed

- Requires `innmind/server-status:~4.0`
- Requires `innmind/server-control:~5.0`
- Requires `innmind/filesystem:~6.2`
- Requires `innmind/socket:~6.0`
- Requires `innmind/http-transport:~6.3`
- Requires `innmind/file-watch:~3.1`
- Requires `innmind/stream:~4.0`

## 3.4.0 - 2023-01-02

### Added

- `Innmind\OperatingSystem\Config`
- `Innmind\OperatingSystem\Factory::build` now accepts `Config` as a second parameter

### Changed

- Requires `innmind/filesystem:~6.1`

## 3.3.0 - 2023-01-01

### Changed

- `Innmind\OperatingSystem\Remote\Resilient::halt` now use the underlying operating system way to halt the process

## 3.2.0 - 2022-12-18

### Added

- Support for `innmind/filesystem:~6.0`
