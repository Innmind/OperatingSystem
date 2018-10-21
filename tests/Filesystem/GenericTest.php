<?php
declare(strict_types = 1);

namespace Tests\Innmind\OperatingSystem\Filesystem;

use Innmind\OperatingSystem\{
    Filesystem\Generic,
    Filesystem,
};
use Innmind\Filesystem\Adapter\FilesystemAdapter;
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

        $adapter = $filesystem->mount(new Path('/tmp'));

        $this->assertInstanceOf(FilesystemAdapter::class, $adapter);
    }
}
