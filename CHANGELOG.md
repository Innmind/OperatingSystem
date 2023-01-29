# Changelog

## [Unreleased]

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
