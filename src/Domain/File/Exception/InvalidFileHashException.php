<?php

declare(strict_types=1);

namespace FileVault\Domain\File\Exception;

use DomainException;

final class InvalidFileHashException extends DomainException
{
    public static function invalidLength(string $hash): self
    {
        return new self("Invalid hash length (expected 64 hex characters): {$hash}");
    }

    public static function invalidFormat(string $hash): self
    {
        return new self("Invalid hash format (expected hexadecimal): {$hash}");
    }
}
