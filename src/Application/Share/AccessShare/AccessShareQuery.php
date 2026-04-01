<?php

declare(strict_types=1);

namespace FileVault\Application\Share\AccessShare;

final readonly class AccessShareQuery
{
    public function __construct(
        public string $token,
        public string $requiredPermission = 'read'
    ) {
    }
}
