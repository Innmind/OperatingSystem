# V6 to V7

### Mounting a folder that doesn't exist

=== "Before"
    ```php
    $adapter = $os
        ->filesystem()
        ->mount(Path::of('/some/folder/'))
        ->unwrap();
    ```

=== "After"
    ```php hl_lines="1 6"
    use Innmind\Filesystem\Recover;

    $adapter = $os
        ->filesystem()
        ->mount(Path::of('/some/folder/'))
        ->recover(Recover::mount(...))
        ->unwrap();
    ```

### Accessing a SQL connection

=== "Before"
    ```php
    $connection = $os
        ->remote()
        ->sql(Url::of('mysql://127.0.0.1:3306/database'));
    ```

=== "After"
    ```php hl_lines="4"
    $connection = $os
        ->remote()
        ->sql(Url::of('mysql://127.0.0.1:3306/database'))
        ->unwrap();
    ```

### Handling process signals

=== "Before"
    ```php
    $os
        ->process()
        ->signals()
        ->listen($signal, $listener);
    $os
        ->process()
        ->signals()
        ->remove($listener);
    ```

=== "After"
    ```php hl_lines="5 10"
    $os
        ->process()
        ->signals()
        ->listen($signal, $listener)
        ->unwrap();
    $os
        ->process()
        ->signals()
        ->remove($listener)
        ->unwrap();
    ```

### HTTP client config

=== "Before"
    ```php
    use Innmind\HttpTransport\Curl;
    use Innmind\TimeContinuum\Period;

    $os = $os->map(
        static fn($config) => $config->useHttpTransport(
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

=== "After"
    ```php
    use Innmind\HttpTransport\Config;

    $os = $os->map(
        static fn($config) => $config->mapHttpTransport(
            static fn($transport) => $transport->map(
                static fn(Config $config) => $config
                    ->limitConcurrencyTo(10)
                    ->disableSSLVerification(),
            ),
        ),
    );
    ```
