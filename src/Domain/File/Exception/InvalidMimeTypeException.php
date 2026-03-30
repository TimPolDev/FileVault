<?php

declare(strict_types=1);

namespace FileVault\Domain\File\Exception;

use DomainException;

final class InvalidMimeTypeException extends DomainException
{
    public static function invalidFormat(string $mimeType): self
    {
        return new self("Invalid MIME type format (expected type/subtype): {$mimeType}");
    }
}
