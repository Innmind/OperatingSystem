# Logging all operations

If you want to trace everything that is done on your operating system you can use the logger decorator that will automatically write to your log file (almost) all operations.

```php
use Innmind\OperatingSystem\Config\Logger;
use Psr\Log\LoggerInterface;

$os = $os->map(
    Logger::psr(/* any instance of LoggerInterface */),
);
```

Now operations done with the new `$os` object will be written to your logs.
