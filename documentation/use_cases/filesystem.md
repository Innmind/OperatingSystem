# Filesystem

Like [time](time.md) the filesystem can be easily accessed with PHP builtin functions but is a source of implicits and state. To move away from these problems the filesystem here is considered as an immutable structure that you _mount_ in your code. This forces you explicit what directories you are accessing and always verify that the structure you want to manipulate is as you expect.

## Examples

### Doing a filesystem backup

```php
use Innmind\Filesystem\Adapter;
use Innmind\Url\Path;

$backup = function(Adapter $source, Adapter $target): void {
    $source->all()->foreach(function($file) use ($target): void {
        // $file can be either a concrete file or directory
        $target->add($file);
    });
};
$backup(
    $os->filesystem()->mount(Path::of('/path/to/source/')),
    $os->filesystem()->mount(Path::of('/path/to/target/')),
);
```

Here we copy all the files from a local directory to another, but the `backup` function isn't aware of the locality of filesystems meaning that the source or target could be swapped to use remote filesystem (such as [S3](https://github.com/innmind/s3)).

### Adding a file in a subdirectory

```php
use Innmind\Filesystem\{
    Adapter,
    File,
    File\Content,
    Directory,
    Name,
};
use Innmind\Url\Path;
use Innmind\Immutable\Predicate\Instance;

$addUserPicture = function(Adapter $filesystem, string $userId, File $picture): void {
    $filesystem
        ->get(Name::of($userId))
        ->keep(Instance::of(Directory::class))
        ->otherwise(static fn() => Directory::named($userId))
        ->map(static fn($directory) => $directory->add($picture))
        ->match(
            static fn($directory) => $filesystem->add($directory),
            static fn() => null,
        );
};
$addUserPicture(
    $os->filesystem()->mount(Path::of('/path/to/users/data/')),
    'some-unique-id',
    File::named(
        'picture.png',
        Content::ofString(
            \file_get_contents($_FILES['some_file']['tmp_name']),
        ),
    ),
);
```

Here you are forced to explicit the creation of the user directory instead of implicitly assuming it has been created by a previous process.

Once again the function is unaware where the file comes from or where it will be stored and simply how the process of adding the picture works.

### Checking if a file exists

Sometimes you don't want to mount a filesystem in order to know if a file or directory exist as you only want to check for their existence.

Example of checking if a `maintenance.lock` exist to prevent your webapp from running:

```php
use Innmind\Url\Path;

if ($os->filesystem()->contains(Path::of('/path/to/project/maintenance.lock'))) {
    throw new \RuntimeException('Application still in maintenance');
}

// run normal webapp here
```

Or you could check the existence of a directory that is required for another sub process to run correctly:

```php
use Innmind\Url\Path;

if (!$os->filesystem()->contains(Path::of('/path/to/some/required/folder/'))) {
    $os->control()->processes()->execute($mkdirCommand);
}

$os->control()->processes()->execute($subProcessCommand);
```

See [processes](processes.md) section on how to execute commands on your operating system.

### Mounting the `tmp` folder

Sometimes you want to use the `tmp` folder to write down files such as cache that can be safely lost in case of a system reboot. You can easily mount this folder as any other folder like so:

```php
use Innmind\Filesystem\Adapter;

$tmp = $os->filesystem()->mount($os->status()->tmp());
$tmp instanceof Adapter; // true
```

It is a great way to forget about where the tmp folder is located and simply focus on what you need to do. And since you can use it as any other mounted filesystem you can change it for tests purposes.

### Watching for changes on the filesystem

A pattern we don't see much in PHP is an infinite loop to react to an event to perform another task. Here we can build such pattern by watching for changes in a file or a directory.

```php
use Innmind\FileWatch\Stop;
use Innmind\Immutable\Either;

$runTests = $os->filesystem()->watch(Path::of('/path/to/project/src/'));

$count = $runTests(0, function(int $count) use ($os): Either {
    if ($count === 42) {
        return Either::left(Stop::of($count));
    }

    $os->control()->processes()->execute($phpunitCommand);

    return Either::right(++$count);
});
```

Here it will run phpunit tests every time the `src/` folder changes. Concrete examples of this pattern can be found in [`innmind/lab-station`](https://github.com/Innmind/LabStation/blob/develop/src/Agent/WatchSources.php#L38) to run a suite of tools when sources change or in [`halsey/journal`](https://github.com/halsey-php/journal/blob/develop/src/Command/Preview.php#L58) to rebuild the website when the markdown files change.

This operation is a bit like an `array_reduce` as you can keep a state record between each calls of the callable via the first argument (here `0`, but it can be anything) and the argument of your callable will be the previous value returned by `Either::right()`.

**Important**: since there is not builtin way to watch for changes in a directory it checks the directory every second, so use it with care. Watching an individual file is a bit safer as it uses the `tail` command so there is no `sleep()` used.
