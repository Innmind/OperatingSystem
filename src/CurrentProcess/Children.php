<?php
declare(strict_types = 1);

namespace Innmind\OperatingSystem\CurrentProcess;

use Innmind\Server\Control\Server\Process\Pid;
use Innmind\Immutable\{
    Map,
    Sequence,
};

final class Children
{
    /** @var Map<int, Child> */
    private Map $children;

    public function __construct(Child ...$children)
    {
        /** @var Map<int, Child> */
        $this->children = Sequence::mixed(...$children)->toMapOf(
            'int',
            Child::class,
            static function(Child $child): \Generator {
                yield $child->id()->toInt() => $child;
            },
        );
    }

    public function contains(Pid $pid): bool
    {
        return $this->children->contains($pid->toInt());
    }

    public function get(Pid $pid): Child
    {
        return $this->children->get($pid->toInt());
    }

    public function wait(): void
    {
        $this->children->values()->foreach(static function(Child $child): void {
            $child->wait();
        });
    }
}
