<?php

declare(strict_types=1);

namespace FileVault\Domain\File\ValueObject;

use FileVault\Domain\File\Exception\InvalidFileHashException;

final class FileHash
{
    private const SHA256_LENGTH = 64;

    private function __construct(
        private readonly string $value
    ) {
    }

    public static function create(string $value): self
    {
        if (strlen($value) !== self::SHA256_LENGTH) {
            throw InvalidFileHashException::invalidLength($value);
        }

        if (!ctype_xdigit($value)) {
            throw InvalidFileHashException::invalidFormat($value);
        }

        return new self(strtolower($value));
    }

    public function __toString(): string
    {
        return $this->value;
    }

    public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }

    public function shortHash(int $length = 8): string
    {
        return substr($this->value, 0, $length);
    }
}
