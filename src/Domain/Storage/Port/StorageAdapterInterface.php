<?php

declare(strict_types=1);

namespace FileVault\Domain\Storage\Port;

use FileVault\Domain\File\ValueObject\StoragePath;

interface StorageAdapterInterface
{
    public function store(string $content, StoragePath $path): void;

    public function retrieve(StoragePath $path): string;

    public function delete(StoragePath $path): void;

    public function exists(StoragePath $path): bool;
}
