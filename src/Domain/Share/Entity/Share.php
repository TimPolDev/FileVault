<?php

declare(strict_types=1);

namespace FileVault\Domain\Share\Entity;

use DateTimeImmutable;
use FileVault\Domain\File\ValueObject\FileId;
use FileVault\Domain\Share\ValueObject\ExpiresAt;
use FileVault\Domain\Share\ValueObject\Permission;
use FileVault\Domain\Share\ValueObject\ShareId;

final class Share
{
    private function __construct(
        private readonly ShareId $id,
        private readonly FileId $fileId,
        private readonly ShareLink $link,
        private readonly Permission $permission,
        private readonly ?ExpiresAt $expiresAt,
        private readonly DateTimeImmutable $createdAt
    ) {
    }

    public static function create(
        FileId $fileId,
        Permission $permission,
        ?ExpiresAt $expiresAt
    ): self {
        return new self(
            ShareId::generate(),
            $fileId,
            ShareLink::generate(),
            $permission,
            $expiresAt,
            new DateTimeImmutable()
        );
    }

    public static function reconstituteFromPersistence(
        ShareId $id,
        FileId $fileId,
        ShareLink $link,
        Permission $permission,
        ?ExpiresAt $expiresAt,
        DateTimeImmutable $createdAt
    ): self {
        return new self(
            $id,
            $fileId,
            $link,
            $permission,
            $expiresAt,
            $createdAt
        );
    }

    public function isAccessible(): bool
    {
        if ($this->expiresAt === null) {
            return true;
        }

        return !$this->expiresAt->isExpired();
    }

    public function hasPermission(Permission $requiredPermission): bool
    {
        return $this->permission->allows($requiredPermission);
    }

    public function id(): ShareId
    {
        return $this->id;
    }

    public function fileId(): FileId
    {
        return $this->fileId;
    }

    public function link(): ShareLink
    {
        return $this->link;
    }

    public function permission(): Permission
    {
        return $this->permission;
    }

    public function expiresAt(): ?ExpiresAt
    {
        return $this->expiresAt;
    }

    public function createdAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }
}
