<?php
declare(strict_types = 1);

namespace Innmind\OperatingSystem\CurrentProcess;

use Innmind\Server\Status\Server\Process\Pid;
use Innmind\Immutable\{
    MapInterface,
    Map,
    Sequence,
};

final class Children
{
    private Map $children;

    public function __construct(Child ...$children)
    {
        $this->children = Sequence::of(...$children)->reduce(
            Map::of('int', Child::class),
            static function(MapInterface $children, Child $child): MapInterface {
                return $children->put(
                    $child->id()->toInt(),
                    $child
                );
            }
        );
    }

    public function has(Pid $pid): bool
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
