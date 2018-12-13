# OperatingSystem

| `master` | `develop` |
|----------|-----------|
| [![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/Innmind/OperatingSystem/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/Innmind/OperatingSystem/?branch=master) | [![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/Innmind/OperatingSystem/badges/quality-score.png?b=develop)](https://scrutinizer-ci.com/g/Innmind/OperatingSystem/?branch=develop) |
| [![Code Coverage](https://scrutinizer-ci.com/g/Innmind/OperatingSystem/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/Innmind/OperatingSystem/?branch=master) | [![Code Coverage](https://scrutinizer-ci.com/g/Innmind/OperatingSystem/badges/coverage.png?b=develop)](https://scrutinizer-ci.com/g/Innmind/OperatingSystem/?branch=develop) |
| [![Build Status](https://scrutinizer-ci.com/g/Innmind/OperatingSystem/badges/build.png?b=master)](https://scrutinizer-ci.com/g/Innmind/OperatingSystem/build-status/master) | [![Build Status](https://scrutinizer-ci.com/g/Innmind/OperatingSystem/badges/build.png?b=develop)](https://scrutinizer-ci.com/g/Innmind/OperatingSystem/build-status/develop) |

Abstraction for most of the operating system the PHP code run on.

The goal is to deal with the operating system in a more abstract way (instead of dealing with concrete, low level, details).

## Installation

```sh
composer require innmind/operating-system
```

As this package is a combination af other packages and you may not need all of them, this package doesn't require all of them. So if you need to access part of the system you'll need to require the underlying dependency in your project.

* `OperatingSystem::filesystem()`: `innmind/filesystem`
* `OperatingSystem::status()`: `innmind/server-status`
* `OperatingSystem::control()`: `innmind/server-control`
* `OperatingSystem::ports()`: `innmind/socket`
* `OperatingSystem::sockets()`: `innmind/socket`
* `OperatingSystem::remote()`: `innmind/socket`, `innmind/server-control` and `innmind/http-transport`

## Usage

```php
use Innmind\OperatingSystem\Factory;
use Innmind\TimeContinuum\TimeContinuum\Earth;

$os = Factory::build(new Earth);
```

### Want to access the system clock ?

`$os->clock()` will return an instance of [`Innmind\TimeContinuum\TimeContinuumInterface`](https://github.com/innmind/timecontinuum#usage).

### Want to access the filesystem ?

```php
use Innmind\Url\Path;

$adapter = $os->filesystem()->mount(new Path('/var/data'));
```

`$adater` is an instance of [`Innmind\Filesystem\Adapter`](https://github.com/innmind/filesystem#filesystem).

### Want to list processes running on the system ?

`$os->status()->processes()->all()` will return a map of [`Inmmind\Immutable\MapInterface<int, Innmind\Server\Status\Server\Process>`](https://github.com/innmind/serverstatus#usage).

### Want to run a command on the system ?

```php
use Innmind\Server\Control\Server\Command;

$process = $os
    ->control()
    ->processes()
    ->execute(Command::foreground('echo foo'));
```

`$process` is an instance of [`Innmind\Server\Control\Server\Process`](https://github.com/innmind/servercontrol#usage).

### Want to open a port to the outside world ?

```php
use Innmind\Socket\Internet\Transport;
use Innmind\IP\IPv4;
use Innmind\Url\Authority\Port;

$server = $os
    ->ports()
    ->open(
        Transport::tcp(),
        IPv4::localhost(),
        new Port(1337)
    );
```

`$server` is an instance of [`Innmind\Socket\Server`](https://github.com/innmind/socket#internet-socket).

### Want to open a local socket ?

```php
# process A
use Innmind\Socket\Address\Unix;

$server = $os->sockets()->open(new Unix('/tmp/foo.sock'));
```

`$server` is an instance of [`Innmind\Socket\Server`](https://github.com/innmind/socket#unix-socket).

```php
# process B
use Innmind\Socket\Address\Unix;

$client = $os->sockets()->connectTo(new Unix('/tmp/foo.sock'));
```

`$client` is an instance of `Innmind\Socket\Client`.

### Want to execute commands on a remote server ?

```php
use Innmind\Url\Url;
use Innmind\Server\Control\Server\Command;

$process = $os
    ->remote()
    ->ssh(Url::fromString('ssh://user@server-address:1337'))
    ->processes()
    ->execute(Command::foreground('ls'));
```

`$process` is an instance of [`Innmind\Server\Control\Server\Process`](https://github.com/innmind/servercontrol#usage).

### Want to do a http call ?

```php
use Innmind\Http\{
    Message\Request\Request,
    Message\Method\Method,
    ProtocolVersion\ProtocolVersion,
};
use Innmind\Url\Url;

$response = $os
    ->remote()
    ->http()
    ->fulfill(new Request(
        Url::fromString('http://example.com'),
        Method::get(),
        new ProtocolVersion(2, 0)
    ));
```
