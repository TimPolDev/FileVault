<?php

declare(strict_types=1);

namespace FileVault\Infrastructure\Storage;

use FileVault\Domain\File\ValueObject\StoragePath;
use FileVault\Domain\Storage\Port\StorageAdapterInterface;
use Illuminate\Support\Facades\Storage;

final class S3StorageAdapter implements StorageAdapterInterface
{
    private const DISK = 's3';

    public function store(string $content, StoragePath $path): void
    {
        Storage::disk(self::DISK)->put((string) $path, $content);
    }

    public function retrieve(StoragePath $path): string
    {
        if (!$this->exists($path)) {
            throw new \RuntimeException("File not found: {$path}");
        }

        $content = Storage::disk(self::DISK)->get((string) $path);

        if ($content === null) {
            throw new \RuntimeException("Failed to retrieve file: {$path}");
        }

        return $content;
    }

    public function delete(StoragePath $path): void
    {
        if ($this->exists($path)) {
            Storage::disk(self::DISK)->delete((string) $path);
        }
    }

    public function exists(StoragePath $path): bool
    {
        return Storage::disk(self::DISK)->exists((string) $path);
    }
}
