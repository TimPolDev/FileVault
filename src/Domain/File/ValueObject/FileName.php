<?php

declare(strict_types=1);

namespace FileVault\Domain\File\ValueObject;

use FileVault\Domain\File\Exception\InvalidFileNameException;

final class FileName
{
    private const MAX_LENGTH = 255;
    private const INVALID_CHARS_PATTERN = '/[<>:"\/\\\\\|?*\x00-\x1F]/';

    private function __construct(
        private readonly string $value
    ) {
    }

    public static function create(string $value): self
    {
        $trimmed = trim($value);

        if ($trimmed === '') {
            throw InvalidFileNameException::empty();
        }

        if (mb_strlen($trimmed) > self::MAX_LENGTH) {
            throw InvalidFileNameException::tooLong($trimmed);
        }

        if (preg_match(self::INVALID_CHARS_PATTERN, $trimmed)) {
            throw InvalidFileNameException::invalidCharacters($trimmed);
        }

        return new self($trimmed);
    }

    public function __toString(): string
    {
        return $this->value;
    }

    public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }

    public function extension(): ?string
    {
        $parts = explode('.', $this->value);

        if (count($parts) < 2) {
            return null;
        }

        return strtolower(end($parts));
    }
}
