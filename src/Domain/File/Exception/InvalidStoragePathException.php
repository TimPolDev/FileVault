<?php

declare(strict_types=1);

namespace FileVault\Domain\File\Exception;

use DomainException;

final class InvalidStoragePathException extends DomainException
{
    public static function empty(): self
    {
        return new self('Storage path cannot be empty');
    }

    public static function invalidFormat(string $path): self
    {
        return new self("Invalid storage path format: {$path}");
    }
}
