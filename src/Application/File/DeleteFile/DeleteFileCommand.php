<?php

declare(strict_types=1);

namespace FileVault\Application\File\DeleteFile;

final readonly class DeleteFileCommand
{
    public function __construct(
        public string $fileId
    ) {
    }
}
