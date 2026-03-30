<?php

declare(strict_types=1);

namespace FileVault\Domain\File\Service;

use FileVault\Domain\File\Entity\File;
use FileVault\Domain\File\ValueObject\FileHash;
use FileVault\Domain\File\ValueObject\FileName;

final class VersioningPolicy
{
    /**
     * Determines if a new file should create a version of an existing file.
     *
     * Logic: If same name and different hash → new version
     *
     * @param File|null $existingFile The existing file (if found)
     * @param FileName $newFileName The name of the new file
     * @param FileHash $newHash The hash of the new file
     * @return bool True if should create a new version, false otherwise
     */
    public function shouldCreateVersion(
        ?File $existingFile,
        FileName $newFileName,
        FileHash $newHash
    ): bool {
        if ($existingFile === null) {
            return false;
        }

        $sameFileName = $existingFile->name()->equals($newFileName);
        $differentHash = !$existingFile->hash()->equals($newHash);

        return $sameFileName && $differentHash;
    }

    /**
     * Calculate the next version number for a file
     */
    public function nextVersionNumber(File $file): int
    {
        $latestVersion = $file->getLatestVersion();

        if ($latestVersion === null) {
            return 1;
        }

        return $latestVersion->versionNumber() + 1;
    }
}
