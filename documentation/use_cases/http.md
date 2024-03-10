# HTTP Client

## Calling an HTTP server

```php
use Innmind\Http\{
    Request,
    Method,
    Response,
    ProtocolVersion,
};
use Innmind\Url\Url;

$http = $os->remote()->http();

$response = $http(Request::of(
    Url::of('https://github.com'),
    Method::get,
    ProtocolVersion::v20,
))
    ->map(static fn($success) => $success->response())
    ->match(
        static fn($response) => $response,
        static fn($error) => throw new \RuntimeException($error::class),
    );
$response instanceof Response; // true
```

All elements of a request/response call is built using objects to enforce correctness of the formatted messages.

> [!NOTE]
> since request and responses messages can be viewed either from a client or a server the model is abstracted in the standalone [`innmind/http` library](https://github.com/innmind/http).

## Resiliency in a distributed system

One of the first things taught when working with distributed systems is that they will intermittently fail. To prevent your app to crash for an occasional failure a common pattern is the _retry pattern_ with a backoff strategy allowing the client to retry safe requests a certain amount of time before giving up. You can use this pattern like so:

```php
use Innmind\OperatingSystem\OperatingSystem\Resilient;
use Innmind\HttpTransport\ExponentialBackoff;

$os = Resilient::of($os);
$http = $os->remote()->http();
$http instanceof ExponentialBackoff; // true
```

Another strategy you can add on top of that is the [circuit breaker pattern](https://en.wikipedia.org/wiki/Circuit_breaker_design_pattern) that will stop sending request to a server known to have failed.

```php
use Innmind\HttpTransport\CircuitBreaker;
use Innmind\TimeContinuum\Earth\Period\Minute;

$http = CircuitBreaker::of(
    $http,
    $os->clock(),
    new Minute(1),
);
$request = Request::of(/* ...args */)
$response = $http($request);
// if the previous call failed then the next call wil not even be sent to the
// server and the client will respond immediately unless 1 minute has elapsed
// between the 2 calls
$response = $http($request);
```

> [!NOTE]
> the circuit breaker works on host per host basis meaning if `server1.com` fails then calls to `server2.com` will still be sent.
