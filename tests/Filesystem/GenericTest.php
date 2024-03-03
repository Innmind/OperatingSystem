<?php
declare(strict_types = 1);

namespace Tests\Innmind\OperatingSystem\Filesystem;

use Innmind\OperatingSystem\{
    Filesystem\Generic,
    Filesystem,
    Config,
};
use Innmind\Filesystem\{
    Adapter\Filesystem as FilesystemAdapter,
    File\Content,
};
use Innmind\Url\Path;
use Innmind\Server\Control\Server\Processes;
use Innmind\FileWatch\Ping;
use Fixtures\Innmind\Url\Path as FPath;
use Innmind\Immutable\{
    Sequence,
    Str,
    Maybe,
};
use PHPUnit\Framework\TestCase;
use Innmind\BlackBox\{
    PHPUnit\BlackBox,
    Set,
};

class GenericTest extends TestCase
{
    use BlackBox;

    public function testInterface()
    {
        $this->assertInstanceOf(
            Filesystem::class,
            Generic::of(
                $this->createMock(Processes::class),
                Config::of(),
            ),
        );
    }

    public function testMount()
    {
        $filesystem = Generic::of(
            $this->createMock(Processes::class),
            Config::of(),
        );

        $adapter = $filesystem->mount(Path::of('/tmp/'));

        $this->assertInstanceOf(FilesystemAdapter::class, $adapter);
    }

    public function testMountingTheSamePathTwiceReturnsTheSameAdapter()
    {
        $filesystem = Generic::of(
            $this->createMock(Processes::class),
            Config::of(),
        );

        $adapter = $filesystem->mount(Path::of('/tmp/'));

        $this->assertSame($adapter, $filesystem->mount(Path::of('/tmp/')));
    }

    public function testContainsFile()
    {
        $filesystem = Generic::of(
            $this->createMock(Processes::class),
            Config::of(),
        );

        $this->assertFalse($filesystem->contains(Path::of('/tmp/foo')));
        \file_put_contents('/tmp/foo', 'data');
        $this->assertTrue($filesystem->contains(Path::of('/tmp/foo')));
        \unlink('/tmp/foo');
    }

    public function testContainsDirectory()
    {
        $filesystem = Generic::of(
            $this->createMock(Processes::class),
            Config::of(),
        );

        $this->assertFalse($filesystem->contains(Path::of('/tmp/some-dir/')));
        \mkdir('/tmp/some-dir/');
        $this->assertTrue($filesystem->contains(Path::of('/tmp/some-dir/')));
        \rmdir('/tmp/some-dir/');
    }

    public function testWatch()
    {
        $filesystem = Generic::of(
            $this->createMock(Processes::class),
            Config::of(),
        );

        $this->assertInstanceOf(Ping::class, $filesystem->watch(Path::of('/somewhere')));
    }

    public function testRequireUnknownFile()
    {
        $this
            ->forAll(FPath::any())
            ->then(function($path) {
                $filesystem = Generic::of(
                    $this->createMock(Processes::class),
                    Config::of(),
                );

                $this->assertFalse($filesystem->require($path)->match(
                    static fn() => true,
                    static fn() => false,
                ));
            });
    }

    public function testRequireFile()
    {
        $filesystem = Generic::of(
            $this->createMock(Processes::class),
            Config::of(),
        );

        $this->assertSame(42, $filesystem->require(Path::of(__DIR__.'/fixture.php'))->match(
            static fn($value) => $value,
            static fn() => null,
        ));
    }

    public function testCreateTemporaryFile()
    {
        $this
            ->forAll(Set\Sequence::of(Set\Unicode::strings()))
            ->then(function($chunks) {
                $filesystem = Generic::of(
                    $this->createMock(Processes::class),
                    Config::of(),
                );

                $content = $filesystem
                    ->temporary(
                        Sequence::of(...$chunks)
                            ->map(Str::of(...))
                            ->map(Maybe::just(...)),
                    )
                    ->match(
                        static fn($content) => $content,
                        static fn() => null,
                    );

                $this->assertInstanceOf(Content::class, $content);
                $this->assertSame(
                    \implode('', $chunks),
                    $content->toString(),
                );
            });
    }

    public function testCreateTemporaryFileFailure()
    {
        $this
            ->forAll(
                Set\Sequence::of(Set\Unicode::strings())->between(0, 20), // upper bound to fit in memory
                Set\Sequence::of(Set\Unicode::strings())->between(0, 20), // upper bound to fit in memory
            )
            ->then(function($leading, $trailing) {
                $filesystem = Generic::of(
                    $this->createMock(Processes::class),
                    Config::of(),
                );

                $content = $filesystem
                    ->temporary(
                        Sequence::of(...[...$leading, null, ...$trailing])
                            ->map(Maybe::of(...))
                            ->map(static fn($chunk) => $chunk->map(Str::of(...))),
                    )
                    ->match(
                        static fn($content) => $content,
                        static fn() => null,
                    );

                $this->assertNull($content);
            });
    }
}
