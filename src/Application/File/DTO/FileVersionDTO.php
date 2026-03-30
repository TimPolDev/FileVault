<?php

declare(strict_types=1);

namespace FileVault\Application\File\DTO;

final readonly class FileVersionDTO
{
    public function __construct(
        public string $storagePath,
        public int $size,
        public string $hash,
        public int $versionNumber,
        public string $createdAt
    ) {
    }
}
