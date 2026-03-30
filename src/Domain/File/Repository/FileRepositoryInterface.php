<?php

declare(strict_types=1);

namespace FileVault\Domain\File\Repository;

use FileVault\Domain\File\Entity\File;
use FileVault\Domain\File\ValueObject\FileHash;
use FileVault\Domain\File\ValueObject\FileId;

interface FileRepositoryInterface
{
    public function findById(FileId $id): ?File;

    public function save(File $file): void;

    public function delete(FileId $id): void;

    public function findByHash(FileHash $hash): ?File;
}
