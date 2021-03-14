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

$server = $os->ports()->open(Transport::tcp(), IPv4::localhost(), Port::of(8080));
$watch = $os->sockets()->watch(new ElapsedPeriod(1000))->forRead($server);

while (true) {
    $ready = $watch();

    if ($ready->toRead()->contains($server)) {
        $client = $server->accept();
        // talk to the client here
    }
}
```

This is similar to the [IPC example](ipc.md) but instead of using a socket file the server is exposed at `tcp://127.0.0.1:8080` and can be accessed from outside the server.

## Opening a connection to an opened port

This example will open a connection to the server defined above but can be changed to talk to an HTTP server or an [AMQP Server](https://github.com/innmind/amqp).

```php
use Innmind\Url\Url;
use Innmind\Socket\Internet\Transport;
use Innmind\TimeContinuum\Earth\ElapsedPeriod;

$client = $os->remote()->socket(Transport::tcp(), Ur::of('tcp://127.0.0.1:8080')->authority());
$watch = $os->sockets()->watch(new ElapsedPeriod(1000))->forRead($client);

do {
    $ready = $watch();
} while (!$ready->toRead()->contains($client));

echo 'Server has responded'.
```
