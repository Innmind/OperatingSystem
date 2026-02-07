<?php
declare(strict_types = 1);

namespace Tests\Innmind\OperatingSystem;

use Innmind\OperatingSystem\{
    Factory,
    OperatingSystem,
    Config,
};
use Innmind\TimeContinuum\Clock;
use Innmind\Filesystem\{
    Adapter,
    File,
    File\Content,
    Directory,
    CaseSensitivity,
    Recover,
};
use Innmind\Url\Path;
use Symfony\Component\Filesystem\Filesystem as FS;
use Innmind\BlackBox\PHPUnit\Framework\TestCase;

class FactoryTest extends TestCase
{
    public function testBuild()
    {
        $clock = Clock::live();

        $os = Factory::build(Config::new()->withClock($clock));

        $this->assertInstanceOf(OperatingSystem::class, $os);
        $this->assertSame($clock, $os->clock());
    }

    public function testClockIsOptional()
    {
        $os = Factory::build();

        $this->assertInstanceOf(Clock::class, $os->clock());
    }

    public function testPersistingFileOnCaseInsensitiveFilesystem()
    {
        if (\PHP_OS !== 'Darwin') {
            $this->assertTrue(true);

            return;
        }

        $path = \sys_get_temp_dir().'/innmind/filesystem/';
        (new FS)->remove($path);

        $os = Factory::build(
            Config::new()->mountFilesystemVia(
                static fn($path, $config) => Adapter::mount(
                    $path,
                    CaseSensitivity::insensitive,
                    $config->io(),
                ),
            ),
        );
        $adapter = $os
            ->filesystem()
            ->mount(Path::of($path))
            ->recover(Recover::mount(...))
            ->unwrap();
        $_ = $adapter->add(
            $directory = Directory::named('0')
                ->add($file = File::named('L', Content::none()))
                ->remove($file->name())
                ->add($file = File::named('l', Content::none()))
                ->remove($file->name())
                ->add($file),
        )->unwrap();

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
