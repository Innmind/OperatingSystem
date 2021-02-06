<?php
declare(strict_types = 1);

namespace Tests\Innmind\OperatingSystem\Filesystem;

use Innmind\OperatingSystem\{
    Filesystem\Generic,
    Filesystem,
};
use Innmind\Filesystem\Adapter\Filesystem as FilesystemAdapter;
use Innmind\Url\Path;
use Innmind\Server\Control\Server\Processes;
use Innmind\TimeWarp\Halt;
use Innmind\TimeContinuum\Clock;
use Innmind\FileWatch\Ping;
use PHPUnit\Framework\TestCase;

class GenericTest extends TestCase
{
    public function testInterface()
    {
        $this->assertInstanceOf(
            Filesystem::class,
            new Generic(
                $this->createMock(Processes::class),
                $this->createMock(Halt::class),
                $this->createMock(Clock::class),
            ),
        );
    }

    public function testMount()
    {
        $filesystem = new Generic(
            $this->createMock(Processes::class),
            $this->createMock(Halt::class),
            $this->createMock(Clock::class),
        );

        $adapter = $filesystem->mount(Path::of('/tmp/'));

        $this->assertInstanceOf(FilesystemAdapter::class, $adapter);
    }

    public function testContainsFile()
    {
        $filesystem = new Generic(
            $this->createMock(Processes::class),
            $this->createMock(Halt::class),
            $this->createMock(Clock::class),
        );

        $this->assertFalse($filesystem->contains(Path::of('/tmp/foo')));
        \file_put_contents('/tmp/foo', 'data');
        $this->assertTrue($filesystem->contains(Path::of('/tmp/foo')));
        \unlink('/tmp/foo');
    }

    public function testContainsDirectory()
    {
        $filesystem = new Generic(
            $this->createMock(Processes::class),
            $this->createMock(Halt::class),
            $this->createMock(Clock::class),
        );

        $this->assertFalse($filesystem->contains(Path::of('/tmp/some-dir/')));
        \mkdir('/tmp/some-dir/');
        $this->assertTrue($filesystem->contains(Path::of('/tmp/some-dir/')));
        \rmdir('/tmp/some-dir/');
    }

    public function testWatch()
    {
        $filesystem = new Generic(
            $this->createMock(Processes::class),
            $this->createMock(Halt::class),
            $this->createMock(Clock::class),
        );

        $this->assertInstanceOf(Ping::class, $filesystem->watch(Path::of('/somewhere')));
    }
}
