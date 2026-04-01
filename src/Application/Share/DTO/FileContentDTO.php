<?php

declare(strict_types=1);

namespace FileVault\Application\Share\DTO;

final readonly class FileContentDTO
{
    public function __construct(
        public string $fileName,
        public int $fileSize,
        public string $mimeType,
        public string $content
    ) {
    }
}
