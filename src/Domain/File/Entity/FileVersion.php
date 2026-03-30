<?php

declare(strict_types=1);

namespace FileVault\Domain\File\Entity;

use DateTimeImmutable;
use FileVault\Domain\File\ValueObject\FileHash;
use FileVault\Domain\File\ValueObject\FileSize;
use FileVault\Domain\File\ValueObject\StoragePath;

final class FileVersion
{
    private function __construct(
        private readonly int $versionNumber,
        private readonly StoragePath $storagePath,
        private readonly FileHash $hash,
        private readonly FileSize $size,
        private readonly DateTimeImmutable $createdAt
    ) {
    }

    public static function create(
        int $versionNumber,
        StoragePath $storagePath,
        FileHash $hash,
        FileSize $size
    ): self {
        return new self(
            $versionNumber,
            $storagePath,
            $hash,
            $size,
            new DateTimeImmutable()
        );
    }

    public static function reconstituteFromPersistence(
        int $versionNumber,
        StoragePath $storagePath,
        FileHash $hash,
        FileSize $size,
        DateTimeImmutable $createdAt
    ): self {
        return new self(
            $versionNumber,
            $storagePath,
            $hash,
            $size,
            $createdAt
        );
    }

    public function versionNumber(): int
    {
        return $this->versionNumber;
    }

    public function storagePath(): StoragePath
    {
        return $this->storagePath;
    }

    public function hash(): FileHash
    {
        return $this->hash;
    }

    public function size(): FileSize
    {
        return $this->size;
    }

    public function createdAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }
}
