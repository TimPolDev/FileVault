<?php

declare(strict_types=1);

namespace FileVault\Application\Share\CreateShare;

use FileVault\Application\Share\DTO\ShareDTO;
use FileVault\Domain\File\Repository\FileRepositoryInterface;
use FileVault\Domain\File\ValueObject\FileId;
use FileVault\Domain\Share\Entity\Share;
use FileVault\Domain\Share\Repository\ShareRepositoryInterface;
use FileVault\Domain\Share\ValueObject\ExpiresAt;
use FileVault\Domain\Share\ValueObject\Permission;

final readonly class CreateShareHandler
{
    public function __construct(
        private FileRepositoryInterface $fileRepository,
        private ShareRepositoryInterface $shareRepository
    ) {
    }

    public function handle(CreateShareCommand $command): ShareDTO
    {
        // Verify file exists
        $fileId = FileId::create($command->fileId);
        $file = $this->fileRepository->findById($fileId);

        if ($file === null) {
            throw new \RuntimeException("File not found: {$command->fileId}");
        }

        // Create permission value object
        $permission = Permission::from($command->permission);

        // Create expiration value object
        $expiresAt = $command->expiresInDays !== null
            ? ExpiresAt::fromNow($command->expiresInDays)
            : null;

        // Create Share entity
        $share = Share::create($fileId, $permission, $expiresAt);

        // Save to repository
        $this->shareRepository->save($share);

        // Return DTO
        return new ShareDTO(
            id: (string) $share->id(),
            fileId: (string) $share->fileId(),
            token: (string) $share->link(),
            permission: $share->permission()->value,
            expiresAt: $share->expiresAt()?->value()->format('Y-m-d H:i:s'),
            createdAt: $share->createdAt()->format('Y-m-d H:i:s')
        );
    }
}
