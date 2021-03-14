# Inter Process Communication (IPC)

To communicate between processes on a same system there is 2 approaches: sharing memory or passing messages through a socket.

The later is the safest of the two (but not exempt of problems) and you will find here the building blocks to communicate via a socket.

**Note**: the adage `share state through messages and not messages through state` is a pillar of the [actor model](https://en.wikipedia.org/wiki/Actor_model) and [initially of object oriented programming](https://www.youtube.com/watch?v=7erJ1DV_Tlo).

```php
# process acting a server
use Innmind\Socket\Address\Unix as Address;
use Innmind\TimeContinuum\Earth\ElapsedPeriod;
use Innmind\Immutable\Str;

$server = $os->sockets()->open(Address::of('/tmp/foo'));
$watch = $os->sockets()->watch(new ElapsedPeriod(1000))->forRead($server);

while (true) {
    $ready = $watch();

    if ($ready->toRead()->contains($server)) {
        $client = $server->accept();
        $client->write(Str::of('Hello 👋'));
        $client->close();
    }
}
```

```php
# process acting as client
use Innmind\Socket\Address\Unix as Address;
use Innmind\TimeContinuum\Earth\ElapsedPeriod;

$client = $os->sockets()->connectTo(Address::of('/tmp/foo'));
$watch = $os->sockets()->watch(new ElapsedPeriod(1000))->forRead($client);

do {
    $ready = $watch();
} while (!$ready->toRead()->contains($client));

echo $client->read()->toString();
```

In the case the server is started first then the client would print `Hello 👋`.

**Important**: this is a very rough implementation of communication between processes. **DO NOT** use this simple implementation in your code, instead use a higher level API such as [`innmind/ipc`](https://github.com/innmind/ipc).
