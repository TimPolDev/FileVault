<?php

declare(strict_types=1);

namespace FileVault\Domain\Share\ValueObject;

enum Permission: string
{
    case READ = 'read';
    case WRITE = 'write';
    case ADMIN = 'admin';

    public function allows(Permission $requiredPermission): bool
    {
        $hierarchy = [
            self::READ->value => 1,
            self::WRITE->value => 2,
            self::ADMIN->value => 3,
        ];

        return $hierarchy[$this->value] >= $hierarchy[$requiredPermission->value];
    }

    public function isReadOnly(): bool
    {
        return $this === self::READ;
    }

    public function canWrite(): bool
    {
        return $this === self::WRITE || $this === self::ADMIN;
    }

    public function isAdmin(): bool
    {
        return $this === self::ADMIN;
    }
}
