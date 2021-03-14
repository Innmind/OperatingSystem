# Processes

Even though PHP started as a way to build dynamic websites it is more and more used to build CLI tools. A recurrent job is to start new processes (either PHP commands or any other cli tool), it can be done in a number of ways from the simple `exec()` function or the widespread [`symfony/process`](https://symfony.com/doc/current/components/process.html) library. Like [time](time.md) they prevent your code from being unit tested as they directly call the operating system.

This library makes the distinction from defining the user intent and the execution so it provides a higher level, more testable, API.

## Executing a process on the system

```php
use Innmind\Server\Control\Server\{
    Command,
    Signal,
};

$webserver = $os->control()->processes()->execute(
    Command::foreground('php')
        ->withShortOption('S', 'localhost:8080'),
);
// do some stuff
$os->control()->processes()->kill($webserver->pid(), Signal::kill());
```

Here we start the PHP builtin webserver and perform some imaginary action before killing it, but you could also wait the process to finish (see below) instead of killing it (in the case of the webserver it never finishes unless with a crash).

```php
use Innmind\Server\Control\Server\Command;

$webserver = $os->control()->processes()->execute(
    Command::foreground('php')
        ->withShortOption('S', 'localhost:8080'),
);
$webserver->wait();
```

Or you could start the process as an independent one (meaning you can't control it anymore) by changing `Command::foreground()` to `Command::background()`.

```php
use Innmind\Server\Control\Server\Command;

$os->control()->processes()->execute(
    Command::background('php')
        ->withShortOption('S', 'localhost:8080'),
);
```

## Executing processes on a remote machine

It uses the same abstraction as running processes on the local machine so you can easily reuse code.

```php
use Innmind\Server\Control\Server;
use Innmind\Url\Url;

$installMariadb = function(Server $server): void {
    // todo run the commands to install mariadb
};
$installMariadb($os->control());
$installMariadb($os->remote()->ssh(Url::of('ssh://user@replication1')));
$installMariadb($os->remote()->ssh(Url::of('ssh://user@replication2')));
```

## Listing all the processes running on the machine

```php
use Innmind\Server\Status\Server\Process;
use Innmind\TimeContinuum\Earth\Format\ISO8601;

$os->status()->processes()->all()->foreach(function(int $pid, Process $process): void {
    \printf(
        "Process %s started by %s at %s\n",
        $process->command()->toString(),
        $process->user()->toString(),
        $process->start()->format(new ISO8601),
    );
});
```

## Starting a new process if not already started

This is useful, though not completely safe (race condition), to start a command that shouldn't be run in parallel.

```php
use Innmind\Server\Control\Server\Command;
use Innmind\Immutable\RegExp;

$backupRunning = $os
    ->status()
    ->processes()
    ->all()
    ->any(fn($_, $process): bool => $process->command()->matches(RegExp::of('~my-backup-tool~')));

if (!$backupRunning) {
    $os->control()->processes()->execute(
        Command::background('my-backup-tool'),
    );
}
```

## Stopping or rebooting a machine

```php
use Innmind\Url\Url;

$os->control()->reboot();
$os->control()->shutdown();
$os->remote()->ssh(Url::of('ssh://user@remote-server'))->reboot();
$os->remote()->ssh(Url::of('ssh://user@remote-server'))->shutdown();
```
