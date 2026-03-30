<?php

declare(strict_types=1);

namespace FileVault\Application\File\UploadFile;

final readonly class UploadFileCommand
{
    public function __construct(
        public string $fileName,
        public int $fileSize,
        public string $mimeType,
        public string $fileContent
    ) {
    }
}
