# Socket communication

This topic is similar to the [Inter Process Communication](ipc.md) but address talking to a socket through a specific network port (either locally or remotely).

As you'll see below working with sockets ([`.sock`](ipc.md) or a port) is always the same workflow:
- open a socket (client or server)
- watch for them to be ready to read
- perform an action on the socket when ready

## Opening a port

The use case is not very common as you need to define a protocol and implement security but can still have some use for a secure network.

```php
use Innmind\Url\Authority\Port;
use Innmind\Socket\Internet\Transport;
use Innmind\IP\IPv4;
use Innmind\TimeContinuum\Earth\ElapsedPeriod;

$server = $os->ports()->open(Transport::tcp(), IPv4::localhost(), Port::of(8080))->match(
    static fn($server) => $server,
    static fn() => throw new \RuntimeException('Unable to start the server'),
);

while (true) {
    $server
        ->timeoutAfter(ElapsedPeriod::of(1_000))
        ->accept()
        ->match(
            static fn($client) => /* talk to the client */,
            static fn() => null, // no client available yet
        );
}
```

This is similar to the [IPC example](ipc.md) but instead of using a socket file the server is exposed at `tcp://127.0.0.1:8080` and can be accessed from outside the server.

## Opening a connection to an opened port

This example will open a connection to the server defined above but can be changed to talk to an HTTP server or an [AMQP Server](https://github.com/innmind/amqp).

```php
use Innmind\Url\Url;
use Innmind\IO\Readable\Frame;
use Innmind\Socket\Internet\Transport;

$client = $os->remote()->socket(Transport::tcp(), Url::of('tcp://127.0.0.1:8080')->authority())->match(
    static fn($client) => $client,
    static fn() => throw new \RuntimeException('Unable to connect to the client'),
);

$receivedData = $client
    ->watch()
    ->frames(Frame\Chunk::of(1))
    ->one()
    ->match(
        static fn() => true,
        static fn() => false,
    );

if ($receivedData) {
    echo 'Server has responded'.
}
```
