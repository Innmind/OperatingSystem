# Handling process signals

Any process can receive [signals](https://en.wikipedia.org/wiki/Signal_(IPC)) either through user interaction (in a terminal), from another process or via the `kill` command. PHP processes can handle them and perform actions to safely free resources or prevent the process from being terminated.

Examples below only use one listener per signal but you can add as many as you want (which is complicated when dealing manually with PHP builtin functions).

## Free resources before stopping

This is a reuse of the [socket example](socket.md).

```php
use Innmind\Url\Url;
use Innmind\Socket\Internet\Transport;
use Innmind\TimeContinuum\Earth\ElapsedPeriod;
use Innmind\Signals\Signal;

$client = $os->remote()->socket(Transport::tcp(), Ur::of('tcp://127.0.0.1:8080')->authority())->match(
    static fn($client) => $client,
    static fn() => throw new \RuntimeException('Unable to connect to the server'),
);
$watch = $os->sockets()->watch(new ElapsedPeriod(1000))->forRead($client);
$continue = true;
$os->process()->signals()->listen(Signal::terminate, function() use (&$continue, $client) {
    $continue = false;
    $client->close();
});

do {
    $ready = $watch()
        ->flatMap(static fn($ready) => $ready->toRead()->find(static fn($ready) => $ready === $client))
        ->match(
            static fn() => true,
            static fn() => false,
        );
} while ($continue && !$ready);

if (!$client->closed()) {
    echo 'Server has responded'.
}
```

When the process receive the `SIGTERM` signal it will be paused then the anonymous function will be called and the process will then be resumed.

**Note**: signal handling is already performed when using [`innmind/ipc`](https://github.com/innmind/ipc) or [`innmind/amqp`](https://github.com/innmind/amqp) so you don't have to think about it.

## Prevent process from being stopped

```php
$prevent = function() {
    echo 'Process cannot be interrupted in the middle of a backup';
};

$os->process()->signals()->listen(Signal::terminate, $prevent);
$os->process()->signals()->listen(Signal::interrupt, $prevent);

// perform the backup here that can't be stopped to prevent data corruption

$os->process()->signals()->remove($prevent);
```

This example will prevent the process from being terminated by a `SIGTERM` or `SIGINT` while in the middle of a backup, but if the signals comes before or after the backup then the process will be terminated as expected.
