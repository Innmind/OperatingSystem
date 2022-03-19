# Logging all operations

If you want to trace everything that is done on your operating system you can use the logger decorator that will automatically write to your log file (almost) all operations.

**Note**: data and actions done on a socket are not logged as well as processes output to prevent logging too much data (at least for now).

```php
use Innmind\OperatingSystem\OperatingSystem\Logger;
use Psr\Log\LoggerInterface;

$os = Logger::psr(
    $os,
    /* any instance of LoggerInterface */
);
```

Now operations done with the new `$os` object will be written to your logs.
