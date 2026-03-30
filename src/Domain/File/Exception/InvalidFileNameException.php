<?php

declare(strict_types=1);

namespace FileVault\Domain\File\Exception;

use DomainException;

final class InvalidFileNameException extends DomainException
{
    public static function empty(): self
    {
        return new self('File name cannot be empty');
    }

    public static function tooLong(string $name): self
    {
        return new self("File name is too long (max 255 characters): {$name}");
    }

    public static function invalidCharacters(string $name): self
    {
        return new self("File name contains invalid characters: {$name}");
    }
}
