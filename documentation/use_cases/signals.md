# Handling process signals

Any process can receive [signals](https://en.wikipedia.org/wiki/Signal_(IPC)) either through user interaction (in a terminal), from another process or via the `kill` command. PHP processes can handle them and perform actions to safely free resources or prevent the process from being terminated.

Examples below only use one listener per signal but you can add as many as you wish (which is complicated when dealing manually with PHP builtin functions).

## Free resources before stopping

This is a reuse of the [socket example](socket.md).

```php
use Innmind\Url\Url;
use Innmind\IO\{
    Sockets\Internet\Transport,
    Frame,
};
use Innmind\TimeContinuum\Period;
use Innmind\Signals\Signal;
use Innmind\Immutable\{
    Sequence,
    Str,
};

$client = $os
    ->remote()
    ->socket(Transport::tcp(), Url::of('tcp://127.0.0.1:8080')->authority())
    ->unwrap();
$signaled = true;
$os
    ->process()
    ->signals()
    ->listen(Signal::terminate, function() use (&$signaled) {
        $signaled = false;
    });

$receivedData = $client
    ->timeoutAfter(Period::second(1))
    // it sends this every second to keep the connection alive
    ->heartbeatWith(static fn() => Sequence::of(Str::of('foo')))
    ->abortWhen(function() use (&$signaled) {
        return $signaled;
    })
    ->frames(Frame::chunk(1)->strict())
    ->one()
    ->match(
        static fn() => true,
        static fn() => false,
    );

if ($receivedData) {
    echo 'Server has responded'.
}

$client->close()->unwrap();
```

When the process receive the `SIGTERM` signal it will be paused then the anonymous function will be called and the process will then be resumed.

!!! note ""
    Signal handling is already performed when using [`innmind/ipc`](https://github.com/innmind/ipc) or [`innmind/amqp`](https://github.com/innmind/amqp) so you don't have to think about it.

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
