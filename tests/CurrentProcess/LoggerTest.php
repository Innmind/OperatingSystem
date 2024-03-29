<?php
declare(strict_types = 1);

namespace Tests\Innmind\OperatingSystem\CurrentProcess;

use Innmind\OperatingSystem\{
    CurrentProcess\Logger,
    CurrentProcess\Signals,
    CurrentProcess,
};
use Innmind\Server\Control\Server\Process\Pid;
use Innmind\Server\Status\Server\Memory\Bytes;
use Innmind\TimeContinuum\Period;
use Psr\Log\LoggerInterface;
use PHPUnit\Framework\TestCase;
use Innmind\BlackBox\{
    PHPUnit\BlackBox,
    Set,
};

class LoggerTest extends TestCase
{
    use BlackBox;

    public function testInterface()
    {
        $this->assertInstanceOf(
            CurrentProcess::class,
            Logger::psr(
                $this->createMock(CurrentProcess::class),
                $this->createMock(LoggerInterface::class),
            ),
        );
    }

    public function testId()
    {
        $this
            ->forAll(Set\Integers::above(2))
            ->then(function($id) {
                $inner = $this->createMock(CurrentProcess::class);
                $inner
                    ->expects($this->once())
                    ->method('id')
                    ->willReturn($expected = new Pid($id));
                $logger = $this->createMock(LoggerInterface::class);
                $logger
                    ->expects($this->once())
                    ->method('debug')
                    ->with(
                        'Current process id is {pid}',
                        ['pid' => $id],
                    );
                $process = Logger::psr($inner, $logger);

                $this->assertSame($expected, $process->id());
            });
    }

    public function testAlwaysUseSameSignalInstance()
    {
        $inner = $this->createMock(CurrentProcess::class);
        $logger = $this->createMock(LoggerInterface::class);
        $process = Logger::psr($inner, $logger);

        $this->assertInstanceOf(Signals\Logger::class, $process->signals());
        $this->assertSame($process->signals(), $process->signals());
    }

    public function testHalt()
    {
        $period = $this->createMock(Period::class);
        $inner = $this->createMock(CurrentProcess::class);
        $inner
            ->expects($this->once())
            ->method('halt')
            ->with($period);
        $logger = $this->createMock(LoggerInterface::class);
        $logger
            ->expects($this->once())
            ->method('debug')
            ->with('Halting current process...');
        $process = Logger::psr($inner, $logger);

        $this->assertNull($process->halt($period));
    }

    public function testMemory()
    {
        $this
            ->forAll(Set\Integers::above(0))
            ->then(function($memory) {
                $inner = $this->createMock(CurrentProcess::class);
                $inner
                    ->expects($this->once())
                    ->method('memory')
                    ->willReturn($expected = new Bytes($memory));
                $logger = $this->createMock(LoggerInterface::class);
                $logger
                    ->expects($this->once())
                    ->method('debug')
                    ->with(
                        'Current process memory at {memory}',
                        $this->callback(static function($context) {
                            return \array_key_exists('memory', $context) &&
                                \is_string($context['memory']) &&
                                $context['memory'] !== '';
                        }),
                    );
                $process = Logger::psr($inner, $logger);

                $this->assertSame($expected, $process->memory());
            });
    }
}
