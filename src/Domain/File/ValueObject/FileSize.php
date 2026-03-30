<?php

declare(strict_types=1);

namespace FileVault\Domain\File\ValueObject;

use FileVault\Domain\File\Exception\InvalidFileSizeException;

final class FileSize
{
    private function __construct(
        private readonly int $bytes
    ) {
    }

    public static function create(int $bytes): self
    {
        if ($bytes < 0) {
            throw InvalidFileSizeException::negative($bytes);
        }

        if ($bytes === 0) {
            throw InvalidFileSizeException::zero();
        }

        return new self($bytes);
    }

    public function toBytes(): int
    {
        return $this->bytes;
    }

    public function __toString(): string
    {
        return (string) $this->bytes;
    }

    public function equals(self $other): bool
    {
        return $this->bytes === $other->bytes;
    }

    public function toHumanReadable(): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $bytes = $this->bytes;

        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, 2) . ' ' . $units[$i];
    }
}
