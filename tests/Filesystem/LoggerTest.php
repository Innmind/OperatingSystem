<?php
declare(strict_types = 1);

namespace Tests\Innmind\OperatingSystem\Filesystem;

use Innmind\OperatingSystem\{
    Filesystem\Logger,
    Filesystem,
};
use Innmind\Filesystem\Adapter;
use Innmind\FileWatch\Ping;
use Psr\Log\LoggerInterface;
use PHPUnit\Framework\TestCase;
use Innmind\BlackBox\{
    PHPUnit\BlackBox,
    Set,
};
use Fixtures\Innmind\Url\Path;

class LoggerTest extends TestCase
{
    use BlackBox;

    public function testInterface()
    {
        $this->assertInstanceOf(
            Filesystem::class,
            new Logger(
                $this->createMock(Filesystem::class),
                $this->createMock(LoggerInterface::class),
            ),
        );
    }

    public function testMount()
    {
        $this
            ->forAll(Path::any())
            ->then(function($path) {
                $inner = $this->createMock(Filesystem::class);
                $inner
                    ->expects($this->once())
                    ->method('mount')
                    ->with($path);
                $logger = $this->createMock(LoggerInterface::class);
                $filesystem = new Logger($inner, $logger);

                $this->assertInstanceOf(Adapter\Logger::class, $filesystem->mount($path));
            });
    }

    public function testContainsPath()
    {
        $this
            ->forAll(Path::any())
            ->then(function($path) {
                $inner = $this->createMock(Filesystem::class);
                $inner
                    ->expects($this->once())
                    ->method('contains')
                    ->with($path)
                    ->willReturn(true);
                $logger = $this->createMock(LoggerInterface::class);
                $logger
                    ->expects($this->once())
                    ->method('info')
                    ->with(
                        'Checking if {path} exists, answer: {answer}',
                        ['answer' => 'yes'],
                    );
                $filesystem = new Logger($inner, $logger);

                $this->assertTrue($filesystem->contains($path));
            });
    }

    public function testDoesntContainPath()
    {
        $this
            ->forAll(Path::any())
            ->then(function($path) {
                $inner = $this->createMock(Filesystem::class);
                $inner
                    ->expects($this->once())
                    ->method('contains')
                    ->with($path)
                    ->willReturn(false);
                $logger = $this->createMock(LoggerInterface::class);
                $logger
                    ->expects($this->once())
                    ->method('info')
                    ->with(
                        'Checking if {path} exists, answer: {answer}',
                        ['answer' => 'no'],
                    );
                $filesystem = new Logger($inner, $logger);

                $this->assertFalse($filesystem->contains($path));
            });
    }

    public function testWatch()
    {
        $this
            ->forAll(Path::any())
            ->then(function($path) {
                $inner = $this->createMock(Filesystem::class);
                $inner
                    ->expects($this->once())
                    ->method('watch')
                    ->with($path);
                $logger = $this->createMock(LoggerInterface::class);
                $filesystem = new Logger($inner, $logger);

                $this->assertInstanceOf(Ping\Logger::class, $filesystem->watch($path));
            });
    }
}
