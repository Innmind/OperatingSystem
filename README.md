# OperatingSystem

[![CI](https://github.com/Innmind/OperatingSystem/actions/workflows/ci.yml/badge.svg?branch=master)](https://github.com/Innmind/OperatingSystem/actions/workflows/ci.yml)
[![codecov](https://codecov.io/gh/innmind/operatingsystem/branch/develop/graph/badge.svg)](https://codecov.io/gh/innmind/operatingsystem)
[![Type Coverage](https://shepherd.dev/github/innmind/operatingsystem/coverage.svg)](https://shepherd.dev/github/innmind/operatingsystem)

Abstraction for most of the operating system the PHP code run on.

The goal is to deal with the operating system in a more abstract way (instead of dealing with concrete, low level, details).

> [!IMPORTANT]
> you must use [`vimeo/psalm`](https://packagist.org/packages/vimeo/psalm) to make sure you use this library correctly.

## Installation

```sh
composer require innmind/operating-system
```

## Documentation

Documentation is located in the [`documentation/`](documentation) folder.

## Usage

```php
use Innmind\OperatingSystem\Factory;

$os = Factory::build();
```

### Want to access the system clock ?

`$os->clock()` will return an instance of [`Innmind\TimeContinuum\Clock`](https://github.com/innmind/timecontinuum#usage).

### Want to access the filesystem ?

```php
use Innmind\Url\Path;

$adapter = $os
    ->filesystem()
    ->mount(Path::of('/var/data/'))
    ->unwrap();
```

`$adater` is an instance of [`Innmind\Filesystem\Adapter`](http://innmind.github.io/Filesystem/).

### Want to list processes running on the system ?

`$os->status()->processes()->all()` will return a map of [`Inmmind\Immutable\Set<Innmind\Server\Status\Server\Process>`](https://github.com/innmind/serverstatus#usage).

### Want to run a command on the system ?

```php
use Innmind\Server\Control\Server\Command;

$process = $os
    ->control()
    ->processes()
    ->execute(Command::foreground('echo foo'))
    ->unwrap();
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
        Port::of(1337),
    )
    ->unwrap();
```

`$server` is an instance of [`Innmind\IO\Sockets\Servers\Server`](https://innmind.org/io/sockets/).

### Want to open a local socket ?

```php
# process A
use Innmind\Socket\Address\Unix;

$server = $os
    ->sockets()
    ->open(Unix::of('/tmp/foo.sock'))
    ->unwrap();
```

`$server` is an instance of [`Innmind\IO\Sockets\Servers\Server`](https://innmind.org/io/sockets/).

```php
# process B
use Innmind\Socket\Address\Unix;

$client = $os
    ->sockets()
    ->connectTo(Unix::of('/tmp/foo.sock'))
    ->unwrap();
```

`$client` is an instance of [`Innmind\IO\Sockets\Clients\Client`](https://innmind.org/io/sockets/#clients).

### Want to execute commands on a remote server ?

```php
use Innmind\Url\Url;
use Innmind\Server\Control\Server\Command;

$process = $os
    ->remote()
    ->ssh(Url::of('ssh://user@server-address:1337'))
    ->processes()
    ->execute(Command::foreground('ls'))
    ->unwrap();
```

`$process` is an instance of [`Innmind\Server\Control\Server\Process`](https://github.com/innmind/servercontrol#usage).

### Want to do a http call ?

```php
use Innmind\Http\{
    Request,
    Method,
    ProtocolVersion,
};
use Innmind\Url\Url;

$response = $os
    ->remote()
    ->http()(Request::of(
        Url::of('http://example.com'),
        Method::get,
        ProtocolVersion::v20,
    ));
```

### Want to access current process id ?

```php
$os->process()->id()->unwrap();
```

### Want to pause the current process ?

```php
use Innmind\TimeContinuum\Period;

$os->process()->halt(Period::minute(1));
```

### Want to listen for a signal ?

```php
use Innmind\Signals\Signal;

$os->process()->signals()->listen(Signal::terminate, function() {
    // handle the signal here
});
```
