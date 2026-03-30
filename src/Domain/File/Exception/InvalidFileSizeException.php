<?php

declare(strict_types=1);

namespace FileVault\Domain\File\Exception;

use DomainException;

final class InvalidFileSizeException extends DomainException
{
    public static function negative(int $size): self
    {
        return new self("File size must be positive: {$size}");
    }

    public static function zero(): self
    {
        return new self('File size cannot be zero');
    }
}
