<?php
declare(strict_types = 1);

namespace Innmind\OperatingSystem\CurrentProcess;

use Innmind\Server\Control\Server\Process\{
    Pid,
    ExitCode,
};
use Innmind\Immutable\{
    Map,
    Sequence,
    Maybe,
};

/**
 * @deprecated This Class will be removed in the next major version
 * @psalm-suppress DeprecatedClass
 */
final class Children
{
    /** @var Map<int<2, max>, Child> */
    private Map $children;

    /**
     * @no-named-arguments
     */
    private function __construct(Child ...$children)
    {
        $this->children = Map::of(
            ...Sequence::of(...$children)
                ->map(static fn($child) => [$child->id()->toInt(), $child])
                ->toList(),
        );
    }

    /**
     * @no-named-arguments
     */
    public static function of(Child ...$children): self
    {
        return new self(...$children);
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

    /**
     * @return Map<Pid, ExitCode>
     */
    public function wait(): Map
    {
        return $this->children->flatMap(
            static fn($_, $child) => Map::of([$child->id(), $child->wait()]),
        );
    }
}
