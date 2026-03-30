<?php

declare(strict_types=1);

namespace FileVault\Domain\File\ValueObject;

use FileVault\Domain\File\Exception\InvalidStoragePathException;

final class StoragePath
{
    private const PATTERN = '/^[a-zA-Z0-9\/_\-\.]+$/';

    private function __construct(
        private readonly string $value
    ) {
    }

    public static function create(string $value): self
    {
        $normalized = self::normalize($value);

        if ($normalized === '') {
            throw InvalidStoragePathException::empty();
        }

        if (!preg_match(self::PATTERN, $normalized)) {
            throw InvalidStoragePathException::invalidFormat($value);
        }

        return new self($normalized);
    }

    public function __toString(): string
    {
        return $this->value;
    }

    public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }

    public function directory(): string
    {
        $parts = explode('/', $this->value);
        array_pop();

        return implode('/', $parts);
    }

    public function filename(): string
    {
        $parts = explode('/', $this->value);

        return end($parts);
    }

    private static function normalize(string $path): string
    {
        $path = str_replace('\\', '/', $path);
        $path = trim($path, '/');

        return $path;
    }
}
