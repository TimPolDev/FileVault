<?php

declare(strict_types=1);

namespace FileVault\Application\File\GetFileVersion;

final readonly class GetFileVersionQuery
{
    public function __construct(
        public string $fileId,
        public ?int $versionNumber = null
    ) {
    }
}
