<?php
declare(strict_types = 1);

namespace Tests\Innmind\OperatingSystem\CurrentProcess;

use Innmind\OperatingSystem\CurrentProcess\Signals;
use Innmind\Signals\{
    Handler,
    Signal
};
use Innmind\BlackBox\PHPUnit\Framework\TestCase;

class SignalsTest extends TestCase
{
    public function testListen()
    {
        $signals = Signals::of(new Handler);
        $order = [];
        $count = 0;

        $this->fork();

        $this->assertNull($signals->listen(Signal::child, function($signal) use (&$order, &$count): void {
            $this->assertSame(Signal::child, $signal);
            $order[] = 'first';
            ++$count;
        }));
        $signals->listen(Signal::child, function($signal) use (&$order, &$count): void {
            $this->assertSame(Signal::child, $signal);
            $order[] = 'second';
            ++$count;
        });

        \sleep(2); // wait for child to stop

        $this->assertSame(2, $count);
        $this->assertSame(['first', 'second'], $order);
    }

    public function testRemoveSignal()
    {
        $signals = Signals::of(new Handler);
        $order = [];
        $count = 0;

        $this->fork();

        $first = function($signal) use (&$order, &$count): void {
            $this->assertSame(Signal::child, $signal);
            $order[] = 'first';
            ++$count;
        };
        $signals->listen(Signal::child, $first);
        $signals->listen(Signal::child, function($signal) use (&$order, &$count): void {
            $this->assertSame(Signal::child, $signal);
            $order[] = 'second';
            ++$count;
        });
        $this->assertNull($signals->remove($first));

        \sleep(2); // wait for child to stop

        $this->assertSame(1, $count);
        $this->assertSame(['second'], $order);
    }

    private function fork()
    {
        if (\pcntl_fork() === 0) {
            \sleep(1);

            exit;
        }
    }
}
