<?php
declare(strict_types = 1);

namespace Innmind\OperatingSystem\CurrentProcess;

use Innmind\OperatingSystem\Exception\ForkFailed;
use Innmind\Server\Status\Server\Process\Pid;

final class ForkSide
{
    private ?Pid $child;

    private function __construct(Pid $child = null)
    {
        $this->child = $child;
    }

    /**
     * @throws ForkFailed
     */
    public static function of(int $status): self
    {
        switch ($status) {
            case -1:
                throw new ForkFailed;

            case 0:
                return new self;

            default:
                return new self(new Pid($status));
        }
    }

    public function parent(): bool
    {
        return $this->child instanceof Pid;
    }

    public function child(): Pid
    {
        return $this->child;
    }
}
