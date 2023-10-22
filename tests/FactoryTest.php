<?php
declare(strict_types = 1);

namespace Tests\Innmind\OperatingSystem;

use Innmind\OperatingSystem\{
    Factory,
    OperatingSystem\Unix,
    Config,
};
use Innmind\TimeContinuum\{
    Clock,
    Earth,
};
use Innmind\Filesystem\{
    File,
    File\Content,
    Directory,
};
use Innmind\Url\Path;
use Symfony\Component\Filesystem\Filesystem as FS;
use PHPUnit\Framework\TestCase;

class FactoryTest extends TestCase
{
    public function testBuild()
    {
        $clock = $this->createMock(Clock::class);

        $os = Factory::build($clock);

        $this->assertInstanceOf(Unix::class, $os);
        $this->assertSame($clock, $os->clock());
    }

    public function testClockIsOptional()
    {
        $os = Factory::build();

        $this->assertInstanceOf(Earth\Clock::class, $os->clock());
    }

    public function testPersistingFileOnCaseInsensitiveFilesystem()
    {
        if (\PHP_OS !== 'Darwin') {
            $this->markTestSkipped();
        }

        $path = \sys_get_temp_dir().'/innmind/filesystem/';
        (new FS)->remove($path);

        $os = Factory::build(null, Config::of()->caseInsensitiveFilesystem());
        $adapter = $os
            ->filesystem()
            ->mount(Path::of($path));
        $adapter->add(
            $directory = Directory::named('0')
                ->add($file = File::named('L', Content::none()))
                ->remove($file->name())
                ->add($file = File::named('l', Content::none()))
                ->remove($file->name())
                ->add($file),
        );

        $this->assertTrue(
            $adapter
                ->get($directory->name())
                ->match(
                    static fn($directory) => $directory->contains($file->name()),
                    static fn() => false,
                ),
        );

        (new FS)->remove($path);
    }
}
