<?php

declare(strict_types=1);

namespace FileVault\Application\File\GetFileVersion;

use FileVault\Application\File\DTO\FileVersionDTO;
use FileVault\Domain\File\Entity\FileVersion;
use FileVault\Domain\File\Repository\FileRepositoryInterface;
use FileVault\Domain\File\ValueObject\FileId;

final readonly class GetFileVersionHandler
{
    public function __construct(
        private FileRepositoryInterface $fileRepository
    ) {
    }

    public function handle(GetFileVersionQuery $query): FileVersionDTO
    {
        $fileId = FileId::create($query->fileId);

        $file = $this->fileRepository->findById($fileId);

        if ($file === null) {
            throw new \RuntimeException("File not found: {$query->fileId}");
        }

        $version = $query->versionNumber === null
            ? $this->getOriginalVersion($file)
            : $this->getSpecificVersion($file, $query->versionNumber);

        if ($version === null) {
            throw new \RuntimeException("Version not found: {$query->versionNumber}");
        }

        return new FileVersionDTO(
            storagePath: (string) $version->storagePath(),
            size: $version->size()->toBytes(),
            hash: (string) $version->hash(),
            versionNumber: $version->versionNumber(),
            createdAt: $version->createdAt()->format('Y-m-d H:i:s')
        );
    }

    private function getOriginalVersion($file): ?FileVersion
    {
        // If no versions exist, return the original file info as version 0
        $latestVersion = $file->getLatestVersion();

        if ($latestVersion !== null) {
            return $latestVersion;
        }

        // Return original file as a "virtual" version
        return FileVersion::create(
            0,
            $file->storagePath(),
            $file->hash(),
            $file->size()
        );
    }

    private function getSpecificVersion($file, int $versionNumber): ?FileVersion
    {
        foreach ($file->versions() as $version) {
            if ($version->versionNumber() === $versionNumber) {
                return $version;
            }
        }

        return null;
    }
}
