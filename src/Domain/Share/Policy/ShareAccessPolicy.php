<?php

declare(strict_types=1);

namespace FileVault\Domain\Share\Policy;

use FileVault\Domain\Share\Entity\Share;
use FileVault\Domain\Share\ValueObject\Permission;

final class ShareAccessPolicy
{
    public function canAccess(Share $share, Permission $requiredPermission): bool
    {
        if (!$share->isAccessible()) {
            return false;
        }

        return $share->hasPermission($requiredPermission);
    }

    public function isExpired(Share $share): bool
    {
        return !$share->isAccessible();
    }
}
