# Extensions

The advantage of having all operating system's operations done through a single abstraction is that you can easily add behaviour on top of that.

## Debugger

The [`innmind/debug` library](https://github.com/innmind/debug) provides a decorator that will send all the operations of the operating system to your [profiler (`innmind/profiler`)](https://github.com/innmind/profiler).

This debugger works for both http requests and cli applications.

**Note**: you can either add this debugger yourself or you can use [`innmind/http-framework`](https://github.com/innmind/httpframework) and [`innmind/cli-framework`](https://github.com/innmind/cliframework) that will automatically enable this debugger when there is a dsn provided in the `PROFILER` environment variable.

## Silent Cartographer

The [`innmind/silent-cartographer`](https://github.com/Innmind/SilentCartographer) is a CLI tool that will display all operating system's operations from all the PHP processes using this extension. This is useful when you want a glance at what's going on on your machine without the need to go through all the log files (if there's any).

**Note**: if you use [`innmind/http-framework`](https://github.com/innmind/httpframework) and [`innmind/cli-framework`](https://github.com/innmind/cliframework) this extension is automatically enabled.
