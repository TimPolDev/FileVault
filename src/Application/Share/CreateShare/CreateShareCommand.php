<?php

declare(strict_types=1);

namespace FileVault\Application\Share\CreateShare;

final readonly class CreateShareCommand
{
    public function __construct(
        public string $fileId,
        public string $permission,
        public ?int $expiresInDays = null
    ) {
    }
}
