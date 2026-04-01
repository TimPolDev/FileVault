<?php

declare(strict_types=1);

namespace FileVault\Application\Share\AccessShare;

use FileVault\Application\Share\DTO\FileContentDTO;
use FileVault\Domain\File\Repository\FileRepositoryInterface;
use FileVault\Domain\Share\Entity\ShareLink;
use FileVault\Domain\Share\Policy\ShareAccessPolicy;
use FileVault\Domain\Share\Repository\ShareRepositoryInterface;
use FileVault\Domain\Share\ValueObject\Permission;
use FileVault\Domain\Storage\Port\StorageAdapterInterface;

final readonly class AccessShareHandler
{
    public function __construct(
        private ShareRepositoryInterface $shareRepository,
        private FileRepositoryInterface $fileRepository,
        private StorageAdapterInterface $storageAdapter,
        private ShareAccessPolicy $accessPolicy
    ) {
    }

    public function handle(AccessShareQuery $query): FileContentDTO
    {
        // Find share by token
        $shareLink = ShareLink::fromToken($query->token);
        $share = $this->shareRepository->findByToken($shareLink);

        if ($share === null) {
            throw new \RuntimeException("Share not found");
        }

        // Verify access via policy
        $requiredPermission = Permission::from($query->requiredPermission);
        if (!$this->accessPolicy->canAccess($share, $requiredPermission)) {
            throw new \RuntimeException("Access denied: insufficient permissions or share expired");
        }

        // Retrieve file
        $file = $this->fileRepository->findById($share->fileId());

        if ($file === null) {
            throw new \RuntimeException("File not found");
        }

        // Retrieve file content from storage
        $content = $this->storageAdapter->retrieve($file->storagePath());

        // Return DTO
        return new FileContentDTO(
            fileName: (string) $file->name(),
            fileSize: $file->size()->toBytes(),
            mimeType: (string) $file->mimeType(),
            content: $content
        );
    }
}
