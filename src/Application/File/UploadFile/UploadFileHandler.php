<?php

declare(strict_types=1);

namespace FileVault\Application\File\UploadFile;

use DateTimeImmutable;
use FileVault\Domain\File\Entity\File;
use FileVault\Domain\File\Entity\FileVersion;
use FileVault\Domain\File\Repository\FileRepositoryInterface;
use FileVault\Domain\File\Service\FileHasher;
use FileVault\Domain\File\Service\VersioningPolicy;
use FileVault\Domain\File\ValueObject\FileName;
use FileVault\Domain\File\ValueObject\FileSize;
use FileVault\Domain\File\ValueObject\MimeType;
use FileVault\Domain\File\ValueObject\StoragePath;
use FileVault\Domain\Storage\Port\StorageAdapterInterface;

final readonly class UploadFileHandler
{
    public function __construct(
        private FileRepositoryInterface $fileRepository,
        private StorageAdapterInterface $storageAdapter,
        private FileHasher $fileHasher,
        private VersioningPolicy $versioningPolicy
    ) {
    }

    public function handle(UploadFileCommand $command): string
    {
        // Create value objects
        $fileName = FileName::create($command->fileName);
        $fileSize = FileSize::create($command->fileSize);
        $mimeType = MimeType::create($command->mimeType);

        // Calculate hash
        $hash = $this->fileHasher->hash($command->fileContent);

        // Check for deduplication
        $existingFile = $this->fileRepository->findByHash($hash);
        if ($existingFile !== null) {
            // File with same content already exists, return its ID
            return (string) $existingFile->id();
        }

        // Generate storage path: {year}/{month}/{hash}.bin
        $now = new DateTimeImmutable();
        $storagePath = StoragePath::create(
            sprintf(
                '%s/%s/%s.bin',
                $now->format('Y'),
                $now->format('m'),
                $hash->shortHash()
            )
        );

        // Store the file
        $this->storageAdapter->store($command->fileContent, $storagePath);

        // Create File entity
        $file = File::upload(
            $fileName,
            $fileSize,
            $mimeType,
            $storagePath,
            $hash
        );

        // Save to repository
        $this->fileRepository->save($file);

        // Dispatch domain events (will be handled by event listeners)
        // For now, events are just collected, they'll be dispatched in infrastructure

        return (string) $file->id();
    }
}
