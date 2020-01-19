<?php
declare(strict_types = 1);

namespace Tests\Innmind\OperatingSystem\Filesystem;

use Innmind\OperatingSystem\{
    Filesystem\Generic,
    Filesystem,
};
use Innmind\Filesystem\Adapter\Filesystem as FilesystemAdapter;
use Innmind\Url\Path;
use PHPUnit\Framework\TestCase;

class GenericTest extends TestCase
{
    public function testInterface()
    {
        $this->assertInstanceOf(Filesystem::class, new Generic);
    }

    public function testMount()
    {
        $filesystem = new Generic;

        $adapter = $filesystem->mount(Path::of('/tmp/'));

        $this->assertInstanceOf(FilesystemAdapter::class, $adapter);
    }

    public function testContainsFile()
    {
        $filesystem = new Generic;

        $this->assertFalse($filesystem->contains(Path::of('/tmp/foo')));
        file_put_contents('/tmp/foo', 'data');
        $this->assertTrue($filesystem->contains(Path::of('/tmp/foo')));
        unlink('/tmp/foo');
    }

    public function testContainsDirectory()
    {
        $filesystem = new Generic;

        $this->assertFalse($filesystem->contains(Path::of('/tmp/some-dir/')));
        mkdir('/tmp/some-dir/');
        $this->assertTrue($filesystem->contains(Path::of('/tmp/some-dir/')));
        rmdir('/tmp/some-dir/');
    }
}
