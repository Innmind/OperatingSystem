<?php
declare(strict_types = 1);

namespace Innmind\OperatingSystem;

use Innmind\Filesystem\CaseSensitivity;

final class Config
{
    private CaseSensitivity $caseSensitivity;

    private function __construct(CaseSensitivity $caseSensitivity)
    {
        $this->caseSensitivity = $caseSensitivity;
    }

    public static function of(): self
    {
        return new self(CaseSensitivity::sensitive);
    }

    public function caseInsensitiveFilesystem(): self
    {
        return new self(CaseSensitivity::insensitive);
    }

    /**
     * @internal
     */
    public function filesystemCaseSensitivity(): CaseSensitivity
    {
        return $this->caseSensitivity;
    }
}
