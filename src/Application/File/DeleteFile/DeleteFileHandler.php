<?php

declare(strict_types=1);

namespace FileVault\Application\File\DeleteFile;

use FileVault\Domain\File\Repository\FileRepositoryInterface;
use FileVault\Domain\File\ValueObject\FileId;
use FileVault\Domain\Storage\Port\StorageAdapterInterface;

final readonly class DeleteFileHandler
{
    public function __construct(
        private FileRepositoryInterface $fileRepository,
        private StorageAdapterInterface $storageAdapter
    ) {
    }

    public function handle(DeleteFileCommand $command): void
    {
        $fileId = FileId::create($command->fileId);

        $file = $this->fileRepository->findById($fileId);

        if ($file === null) {
            throw new \RuntimeException("File not found: {$command->fileId}");
        }

        // Delete from storage
        $this->storageAdapter->delete($file->storagePath());

        // Delete versions from storage
        foreach ($file->versions() as $version) {
            $this->storageAdapter->delete($version->storagePath());
        }

        // Delete from repository (will cascade delete versions via DB constraint)
        $this->fileRepository->delete($fileId);

        // Domain event FileDeleted would be dispatched here
    }
}
