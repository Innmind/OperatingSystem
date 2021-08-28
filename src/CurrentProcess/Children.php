<?php
declare(strict_types = 1);

namespace Innmind\OperatingSystem\CurrentProcess;

use Innmind\Server\Control\Server\Process\Pid;
use Innmind\Immutable\{
    Map,
    Sequence,
    Maybe,
};

final class Children
{
    /** @var Map<int, Child> */
    private Map $children;

    /**
     * @no-named-arguments
     */
    public function __construct(Child ...$children)
    {
        $this->children = Map::of(
            ...Sequence::of(...$children)
                ->map(static fn($child) => [$child->id()->toInt(), $child])
                ->toList(),
        );
    }

    public function contains(Pid $pid): bool
    {
        return $this->children->contains($pid->toInt());
    }

    /**
     * @return Maybe<Child>
     */
    public function get(Pid $pid): Maybe
    {
        return $this->children->get($pid->toInt());
    }

    public function wait(): void
    {
        $_ = $this->children->values()->foreach(static function(Child $child): void {
            $child->wait();
        });
    }
}
