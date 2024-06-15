# Inter Process Communication (IPC)

To communicate between processes on a same system there is 2 approaches: sharing memory or passing messages through a socket.

The later is the safest of the two (but not exempt of problems) and you will find here the building blocks to communicate via a socket.

!!! tip ""
    The adage `share state through messages and not messages through state` is a pillar of the [actor model](https://en.wikipedia.org/wiki/Actor_model) and [initially of object oriented programming](https://www.youtube.com/watch?v=7erJ1DV_Tlo).

```php
# process acting as a server
use Innmind\Socket\Address\Unix as Address;
use Innmind\TimeContinuum\Earth\ElapsedPeriod;
use Innmind\Immutable\{
    Sequence,
    Str,
};

$server = $os->sockets()->open(Address::of('/tmp/foo'))->match(
    static fn($server) => $server,
    static fn() => throw new \RuntimeException('Unable to start the server'),
);
$watch = $os->sockets()->watch(new ElapsedPeriod(1000))->forRead($server);

while (true) {
    $_ = $server
        ->timeoutAfter(ElapsedPeriod::of(1_000))
        ->accept()
        ->match(
            static fn($client) => $client
                ->send(Sequence::of(Str::of('Hello')))
                ->flatMap(static fn() => $client->close())
                ->match(
                    static fn() => null, // everyhting is ok
                    static fn() => throw new \RuntimeException('Unable to send data or close the connection'),
                ),
            static fn() => null, // no new connection available
        ),
}
```

```php
# process acting as client
use Innmind\Socket\Address\Unix as Address;
use Innmind\IO\Readable\Frame;

$client = $os->sockets()->connectTo(Address::of('/tmp/foo'))->match(
    static fn($client) => $client,
    static fn() => throw new \RuntimeException('Unable to connect to the server'),
);

echo $client
    ->watch()
    ->frames(Frame\Chunk::of(5))
    ->one()
    ->match(
        static fn($data) => $data->toString(),
        static fn() => 'unable to read the stream',
    );
```

In the case the server is started first then the client would print `Hello`.

!!! warning ""
    This is a very rough implementation of communication between processes. **DO NOT** use this simple implementation in your code, instead use a higher level API such as [`innmind/ipc`](https://github.com/innmind/ipc).
