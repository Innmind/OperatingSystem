# V5 to V6

### Resilient decorator

=== "Before"
    ```php
    use Innmind\OperatingSystem\OperatingSystem\Resilient;

    $os = Resilient::of($os);
    ```
=== "After"
    ```php
    use Innmind\OperatingSystem\Config\Resilient;

    $os = $os->map(Resilient::new());
    ```

### Logger decorator

=== "Before"
    ```php
    use Innmind\OperatingSystem\OperatingSystem\Logger;
    use Psr\Log\LoggerInterface

    $os = Logger::psr($os, /* instance of LoggerInterface */);
    ```
=== "After"
    ```php
    use Innmind\OperatingSystem\Config\Logger;

    $os = $os->map(Logger::psr(/* instance of LoggerInterface */));
    ```

### HTTP client config

=== "Before"
    ```php
    use Innmind\OperatingSystem\{
        Factory,
        Config,
    };
    use Innmind\TimeContinuum\Earth\ElapsedPeriod;

    $os = Factory::build(
        Config::of()
            ->disableSSLVerification()
            ->limitHttpConcurrencyTo(10)
            ->withHttpHeartbeat(
                ElapsedPeriod::of(1_000),
                static fn() => 'heartbeat',
            ),
    );
    ```

=== "After"
    ```php
    use Innmind\OperatingSystem\{
        Factory,
        Config,
    };
    use Innmind\HttpTransport\Curl;
    use Innmind\TimeContinuum\Period;

    $config = Config::new();
    $os = Factory::build(
        $config->useHttpTransport(
            Curl::of($config->clock(), $config->io())
                ->disableSSLVerification()
                ->maxConcurrency(10)
                ->heartbeat(
                    Period::second(1),
                    static fn() => 'heartbeat',
                ),
        ),
    );
    ```

### Filesystem config

=== "Before"
    ```php
    use Innmind\OperatingSystem\{
        Factory,
        Config,
    };
    use Innmind\Filesystem\CaseSensitivity;

    $os = Factory::build(
        Config::of()->caseInsensitiveFilesystem(
            CaseSensitivity::insensitive,
        ),
    );
    ```

=== "After"
    ```php
    use Innmind\OperatingSystem\{
        Factory,
        Config,
    };
    use Innmind\Filesystem\{
        Adapter\Filesystem,
        CaseSensitivity,
    };

    $os = Factory::build(
        Config::new()->mountFilesystemVia(
            static fn(Path $path, Config $config) => Filesystem::mount(
                $path,
                $config->io(),
            )->withCaseSentitivity(
                CaseSensitivity::insensitive,
            ),
        ),
    );
    ```

### Current process id

=== "Before"
    ```php
    $os->process()->id();
    ```

=== "After"
    ```php
    $os->process()->id()->unwrap();
    ```

### Halt current process

=== "Before"
    ```php
    use Innmind\TimeContinuum\Earth\Period\Second;

    $os->process()->halt(new Second(1));
    ```

=== "After"
    ```php
    use Innmind\TimeContinuum\Period;

    $os->process()->halt(Period::second(1))->unwrap();
    ```

### Mount filesystem

=== "Before"
    ```php
    use Innmind\Url\Path;

    $adapter = $os
        ->filesystem()
        ->mount(Path::of('somewhere/'));
    ```

=== "After"
    ```php
    use Innmind\Url\Path;

    $adapter = $os
        ->filesystem()
        ->mount(Path::of('somewhere/'))
        ->unwrap();
    ```
