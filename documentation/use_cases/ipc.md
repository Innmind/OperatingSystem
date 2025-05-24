# Inter Process Communication (IPC)

To communicate between processes on a same system there is 2 approaches: sharing memory or passing messages through a socket.

The later is the safest of the two (but not exempt of problems) and you will find here the building blocks to communicate via a socket.

!!! tip ""
    The adage `share state through messages and not messages through state` is a pillar of the [actor model](https://en.wikipedia.org/wiki/Actor_model) and [initially of object oriented programming](https://www.youtube.com/watch?v=7erJ1DV_Tlo).

=== "Server"
    ```php
    use Innmind\IO\Sockets\Unix\Address;
    use Innmind\Url\Path;
    use Innmind\TimeContinuum\Period;
    use Innmind\Immutable\{
        Sequence,
        Str,
    };

    $server = $os
        ->sockets()
        ->open(Address::of(Path::of('/tmp/foo')))
        ->unwrap();
        ->timeoutAfter(Period::second(1));

    while (true) {
        $_ = $server
            ->accept()
            ->match(
                static fn($client) => $client
                    ->sink(Sequence::of(Str::of('Hello')))
                    ->flatMap(static fn() => $client->close())
                    ->match(
                        static fn() => null, // everyhting is ok
                        static fn(\Throwable $e) => throw $e,
                    ),
                static fn() => null, // no new connection available
            ),
    }
    ```

=== "Client"
    ```php
    use Innmind\IO\{
        Sockets\Unix\Address,
        Frame,
    };
    use Innmind\Url\Path;

    $client = $os
        ->sockets()
        ->connectTo(Address::of(Path::of('/tmp/foo')))
        ->unwrap();

    echo $client
        ->watch()
        ->frames(Frame::chunk(5)->strict())
        ->one()
        ->match(
            static fn($data) => $data->toString(),
            static fn() => 'unable to read the stream',
        );
    ```

In the case the server is started first then the client would print `Hello`.

!!! warning ""
    This is a very rough implementation of communication between processes. **DO NOT** use this simple implementation in your code, instead use a higher level API such as [`innmind/ipc`](https://github.com/innmind/ipc).
