# Manipulating time

Directly accessing time in a PHP code is straightforward (either via `DateTime` or time functions) but it prevents you to build testable code or require to use some hard to understand hacks. Instead it is simpler to think of time as another dependency that you need to inject in your code, thus easier to change the implementation when testing.

> [!TIP]
> for a more in length presentation of why directly accessing time is problematic you can watch this [talk](https://www.youtube.com/watch?v=T_I6HhP9-6w) (in french).

## Accessing time

```php
use Innmind\TimeContinuum\PointInTime;

$isItMonday = function(PointInTime $point): bool {
    return $point->day()->weekNumber() === 1; // 0 for sunday
};

$now = $os->clock()->now();
$now instanceof PointInTime; // true
$isItMonday($now);
```

To access the time you need to go through the system clock. The `PointInTime` objects are immutable it prevents you from having side effects.

For such example you could write unit tests by manually instaciating instances of `PointInTime` and verify that the function works as expected for every day of the week.

## Haltering the time in your app

In some cases you may want your program to wait for a certain amount of time before continuing its job. A good example is a web crawler where you want to respect the crawler delay specified in the website `robots.txt` to avoid overloading the website server.

```php
use Innmind\OperatingSystem\CurrentProcess;
use Innmind\TimeContinuum\Earth\Period\Second;

$crawl = function(CurrentProcess $process, string ...$urls): void {
    foreach ($urls as $url) {
        // crawl the $url and do something with the result

        // here for the sake of simplicity we specify 1 second but it can be
        // any instance of Innmind\TimeContinuum\Period and you could build it
        // from a robots.txt Crawler-Delay directive
        $process->halt(new Second(1));
    }
};
$crawl($os->process(), 'http://google.com', 'http://github.com');
```

Once again you can change the implementation of `CurrentProcess` when unit testing your function so it doesn't have to really wait for the specified amount of time while still verifying it is instructed to wait for a given period.
