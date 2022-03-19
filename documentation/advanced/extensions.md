# Extensions

The advantage of having all operating system's operations done through a single abstraction is that you can easily add behaviour on top of that.

## Debugger

The [`innmind/debug` library](https://github.com/innmind/debug) provides a decorator that will send all the operations of the operating system to your [profiler (`innmind/profiler`)](https://github.com/innmind/profiler).

This debugger works for both http requests and cli applications.

## Silent Cartographer

The [`innmind/silent-cartographer`](https://github.com/Innmind/SilentCartographer) is a CLI tool that will display all operating system's operations from all the PHP processes using this extension. This is useful when you want a glance at what's going on on your machine without the need to go through all the log files (if there's any).
