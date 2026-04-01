<?php

declare(strict_types=1);

namespace FileVault\Application\Share\DTO;

final readonly class ShareDTO
{
    public function __construct(
        public string $id,
        public string $fileId,
        public string $token,
        public string $permission,
        public ?string $expiresAt,
        public string $createdAt
    ) {
    }
}
