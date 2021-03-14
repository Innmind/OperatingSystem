<?php
declare(strict_types = 1);

namespace Tests\Innmind\OperatingSystem\CurrentProcess;

use Innmind\OperatingSystem\{
    CurrentProcess\Logger,
    CurrentProcess\Signals,
    CurrentProcess\ForkSide,
    CurrentProcess\Children,
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
            new Logger(
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
                    ->method('info')
                    ->with(
                        'Current process id is {pid}',
                        ['pid' => $id],
                    );
                $process = new Logger($inner, $logger);

                $this->assertSame($expected, $process->id());
            });
    }

    public function testAlwaysUseSameSignalInstance()
    {
        $inner = $this->createMock(CurrentProcess::class);
        $logger = $this->createMock(LoggerInterface::class);
        $process = new Logger($inner, $logger);

        $this->assertInstanceOf(Signals\Logger::class, $process->signals());
        $this->assertSame($process->signals(), $process->signals());
    }

    public function testFork()
    {
        $this
            ->forAll(Set\Integers::above(0)->filter(static fn($id) => $id !== 1))
            ->then(function($result) {
                $expected = ForkSide::of($result);
                $inner = $this->createMock(CurrentProcess::class);
                $inner
                    ->expects($this->once())
                    ->method('fork')
                    ->willReturn($expected);
                $logger = $this->createMock(LoggerInterface::class);
                $logger
                    ->expects($this->once())
                    ->method('info')
                    ->with('Forking process');
                $process = new Logger($inner, $logger);

                $this->assertSame($expected, $process->fork());
            });
    }

    public function testSignalsInstanceIsResettedInTheChildProcessWhenForking()
    {
        $inner = $this->createMock(CurrentProcess::class);
        $inner
            ->expects($this->once())
            ->method('fork')
            ->willReturn(ForkSide::of(0));
        $logger = $this->createMock(LoggerInterface::class);
        $process = new Logger($inner, $logger);
        $original = $process->signals();
        $process->fork();

        $this->assertNotSame($original, $process->signals());
    }

    public function testSignalsInstanceIsKeptInTheParentProcessWhenForking()
    {
        $this
            ->forAll(Set\Integers::above(2))
            ->then(function($child) {
                $inner = $this->createMock(CurrentProcess::class);
                $inner
                    ->expects($this->once())
                    ->method('fork')
                    ->willReturn(ForkSide::of($child));
                $logger = $this->createMock(LoggerInterface::class);
                $process = new Logger($inner, $logger);
                $original = $process->signals();
                $process->fork();

                $this->assertSame($original, $process->signals());
            });
    }

    public function testChildren()
    {
        $inner = $this->createMock(CurrentProcess::class);
        $inner
            ->expects($this->once())
            ->method('children')
            ->willReturn($expected = new Children);
        $process = new Logger($inner, $this->createMock(LoggerInterface::class));

        $this->assertSame($expected, $process->children());
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
            ->method('info')
            ->with('Halting current process...');
        $process = new Logger($inner, $logger);

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
                    ->method('info')
                    ->with(
                        'Current process memory at {memory}',
                        $this->callback(static function($context) {
                            return \array_key_exists('memory', $context) &&
                                \is_string($context['memory']) &&
                                $context['memory'] !== '';
                        }),
                    );
                $process = new Logger($inner, $logger);

                $this->assertSame($expected, $process->memory());
            });
    }
}
