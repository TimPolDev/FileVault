<?php

declare(strict_types=1);

namespace FileVault\Domain\File\Service;

use FileVault\Domain\File\ValueObject\FileHash;

final class FileHasher
{
    public function hash(string $content): FileHash
    {
        $hashValue = hash('sha256', $content);

        return FileHash::create($hashValue);
    }

    public function hashFile(string $filePath): FileHash
    {
        if (!file_exists($filePath)) {
            throw new \RuntimeException("File not found: {$filePath}");
        }

        $hashValue = hash_file('sha256', $filePath);

        if ($hashValue === false) {
            throw new \RuntimeException("Failed to hash file: {$filePath}");
        }

        return FileHash::create($hashValue);
    }
}
