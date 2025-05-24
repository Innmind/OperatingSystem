<?php
declare(strict_types = 1);

namespace Tests\Innmind\OperatingSystem;

use Innmind\OperatingSystem\{
    Filesystem,
    Config,
    Factory,
};
use Innmind\Filesystem\{
    Adapter\Filesystem as FilesystemAdapter,
    File\Content,
};
use Innmind\Url\Path;
use Innmind\FileWatch\Ping;
use Fixtures\Innmind\Url\Path as FPath;
use Innmind\Immutable\{
    Sequence,
    Str,
    Maybe,
    Attempt,
};
use Innmind\BlackBox\{
    PHPUnit\BlackBox,
    PHPUnit\Framework\TestCase,
    Set,
};

class FilesystemTest extends TestCase
{
    use BlackBox;

    public function testMount()
    {
        $filesystem = Filesystem::of(
            Factory::build()->control()->processes(),
            Config::new(),
        );

        $adapter = $filesystem->mount(Path::of('/tmp/'))->unwrap();

        $this->assertInstanceOf(FilesystemAdapter::class, $adapter);
    }

    public function testMountingTheSamePathTwiceReturnsTheSameAdapter()
    {
        $filesystem = Filesystem::of(
            Factory::build()->control()->processes(),
            Config::new(),
        );

        $adapter = $filesystem->mount(Path::of('/tmp/'))->unwrap();

        $this->assertSame($adapter, $filesystem->mount(Path::of('/tmp/'))->unwrap());
    }

    public function testContainsFile()
    {
        $filesystem = Filesystem::of(
            Factory::build()->control()->processes(),
            Config::new(),
        );

        $this->assertFalse($filesystem->contains(Path::of('/tmp/foo')));
        \file_put_contents('/tmp/foo', 'data');
        $this->assertTrue($filesystem->contains(Path::of('/tmp/foo')));
        \unlink('/tmp/foo');
    }

    public function testContainsDirectory()
    {
        $filesystem = Filesystem::of(
            Factory::build()->control()->processes(),
            Config::new(),
        );

        $this->assertFalse($filesystem->contains(Path::of('/tmp/some-dir/')));
        \mkdir('/tmp/some-dir/');
        $this->assertTrue($filesystem->contains(Path::of('/tmp/some-dir/')));
        \rmdir('/tmp/some-dir/');
    }

    public function testWatch()
    {
        $filesystem = Filesystem::of(
            Factory::build()->control()->processes(),
            Config::new(),
        );

        $this->assertInstanceOf(Ping::class, $filesystem->watch(Path::of('/somewhere')));
    }

    public function testRequireUnknownFile(): BlackBox\Proof
    {
        return $this
            ->forAll(FPath::any())
            ->prove(function($path) {
                $filesystem = Filesystem::of(
                    Factory::build()->control()->processes(),
                    Config::new(),
                );

                $this->assertFalse($filesystem->require($path)->match(
                    static fn() => true,
                    static fn() => false,
                ));
            });
    }

    public function testRequireFile()
    {
        $filesystem = Filesystem::of(
            Factory::build()->control()->processes(),
            Config::new(),
        );

        $this->assertSame(42, $filesystem->require(Path::of(__DIR__.'/fixture.php'))->match(
            static fn($value) => $value,
            static fn() => null,
        ));
    }

    public function testCreateTemporaryFile()
    {
        $this
            ->forAll(Set::sequence(Set::strings()->unicode()))
            ->then(function($chunks) {
                $filesystem = Filesystem::of(
                    Factory::build()->control()->processes(),
                    Config::new(),
                );

                $content = $filesystem
                    ->temporary(
                        Sequence::of(...$chunks)
                            ->map(Str::of(...))
                            ->map(Attempt::result(...)),
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
                Set::sequence(Set::strings()->unicode())->between(0, 20), // upper bound to fit in memory
                Set::sequence(Set::strings()->unicode())->between(0, 20), // upper bound to fit in memory
            )
            ->then(function($leading, $trailing) {
                $filesystem = Filesystem::of(
                    Factory::build()->control()->processes(),
                    Config::new(),
                );

                $content = $filesystem
                    ->temporary(
                        Sequence::of(...[...$leading, null, ...$trailing])
                            ->map(Maybe::of(...))
                            ->map(static fn($chunk) => $chunk->attempt(
                                static fn() => new \RuntimeException,
                            ))
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
