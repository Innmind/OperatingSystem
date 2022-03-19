<?php
declare(strict_types = 1);

namespace Tests\Innmind\OperatingSystem\CurrentProcess\Signals;

use Innmind\OperatingSystem\CurrentProcess\{
    Signals\Logger,
    Signals,
};
use Innmind\Signals\{
    Signal,
    Info,
};
use Innmind\Immutable\Maybe;
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
            Signals::class,
            new Logger(
                $this->createMock(Signals::class),
                $this->createMock(LoggerInterface::class),
            ),
        );
    }

    public function testLogWhenRegisteringListener()
    {
        $this
            ->forAll($this->signals())
            ->then(function($signal) {
                $inner = $this->createMock(Signals::class);
                $inner
                    ->expects($this->once())
                    ->method('listen')
                    ->with($signal);
                $logger = $this->createMock(LoggerInterface::class);
                $logger
                    ->expects($this->once())
                    ->method('debug')
                    ->with(
                        'Registering a listener for signal {signal}',
                        ['signal' => $signal->toInt()],
                    );
                $signals = new Logger($inner, $logger);

                $this->assertNull($signals->listen($signal, static fn() => null));
            });
    }

    public function testLogWhenListenerCalled()
    {
        $this
            ->forAll($this->signals())
            ->then(function($signal) {
                $info = new Info(
                    Maybe::nothing(),
                    Maybe::nothing(),
                    Maybe::nothing(),
                    Maybe::nothing(),
                    Maybe::nothing(),
                );
                $inner = $this->createMock(Signals::class);
                $inner
                    ->expects($this->once())
                    ->method('listen')
                    ->with($signal, $this->callback(static function($listener) use ($signal, $info) {
                        $listener($signal, $info);

                        return true;
                    }));
                $logger = $this->createMock(LoggerInterface::class);
                $logger
                    ->expects($this->exactly(2))
                    ->method('debug')
                    ->withConsecutive(
                        [],
                        [
                            'Handling signal {signal}',
                            ['signal' => $signal->toInt()],
                        ],
                    );
                $signals = new Logger($inner, $logger);
                $called = false;

                $this->assertNull($signals->listen($signal, function($sig, $inf) use ($signal, $info, &$called) {
                    $called = true;

                    $this->assertSame($signal, $sig);
                    $this->assertSame($info, $inf);
                }));
                $this->assertTrue($called);
            });
    }

    public function testUseSameRegisteredListenerWhenRemovingOne()
    {
        $this
            ->forAll($this->signals())
            ->then(function($signal) {
                $registeredListener = null;
                $inner = $this->createMock(Signals::class);
                $inner
                    ->expects($this->once())
                    ->method('listen')
                    ->with($signal, $this->callback(static function($listener) use (&$registeredListener) {
                        $registeredListener = $listener;

                        return true;
                    }));
                $inner
                    ->expects($this->once())
                    ->method('remove')
                    ->with($this->callback(static function($listener) use (&$registeredListener) {
                        return $listener === $registeredListener;
                    }));
                $logger = $this->createMock(LoggerInterface::class);
                $logger
                    ->expects($this->exactly(2))
                    ->method('debug')
                    ->withConsecutive(
                        [],
                        ['Removing a signal listener'],
                    );
                $signals = new Logger($inner, $logger);
                $listener = static fn() => null;

                $this->assertNull($signals->listen($signal, $listener));
                $this->assertNull($signals->remove($listener));
            });
    }

    private function signals(): Set
    {
        return Set\Elements::of(
            Signal::hangup,
            Signal::interrupt,
            Signal::quit,
            Signal::illegal,
            Signal::trap,
            Signal::abort,
            Signal::floatingPointException,
            Signal::bus,
            Signal::segmentationViolation,
            Signal::system,
            Signal::pipe,
            Signal::alarm,
            Signal::terminate,
            Signal::urgent,
            Signal::terminalStop,
            Signal::continue,
            Signal::child,
            Signal::ttyIn,
            Signal::ttyOut,
            Signal::io,
            Signal::exceedsCpu,
            Signal::exceedsFileSize,
            Signal::virtualTimerExpired,
            Signal::profilingTimerExpired,
            Signal::terminalWindowsSizeChanged,
            Signal::userDefinedSignal1,
            Signal::userDefinedSignal2,
        );
    }
}
