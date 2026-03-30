<?php

declare(strict_types=1);

namespace FileVault\Infrastructure\Persistence\Eloquent;

use DateTimeImmutable;
use FileVault\Domain\File\Entity\File;
use FileVault\Domain\File\Entity\FileVersion;
use FileVault\Domain\File\Repository\FileRepositoryInterface;
use FileVault\Domain\File\ValueObject\FileHash;
use FileVault\Domain\File\ValueObject\FileId;
use FileVault\Domain\File\ValueObject\FileName;
use FileVault\Domain\File\ValueObject\FileSize;
use FileVault\Domain\File\ValueObject\MimeType;
use FileVault\Domain\File\ValueObject\StoragePath;
use FileVault\Infrastructure\Persistence\Models\FileModel;
use FileVault\Infrastructure\Persistence\Models\FileVersionModel;

final class EloquentFileRepository implements FileRepositoryInterface
{
    public function findById(FileId $id): ?File
    {
        $model = FileModel::with('versions')->find((string) $id);

        if ($model === null) {
            return null;
        }

        return $this->toDomain($model);
    }

    public function save(File $file): void
    {
        $model = FileModel::findOrNew((string) $file->id());

        $model->id = (string) $file->id();
        $model->name = (string) $file->name();
        $model->size = $file->size()->toBytes();
        $model->mime_type = (string) $file->mimeType();
        $model->storage_path = (string) $file->storagePath();
        $model->hash = (string) $file->hash();
        $model->uploaded_at = $file->uploadedAt();

        $model->save();

        // Save versions
        foreach ($file->versions() as $version) {
            $versionModel = new FileVersionModel();
            $versionModel->id = \Illuminate\Support\Str::uuid()->toString();
            $versionModel->file_id = (string) $file->id();
            $versionModel->version_number = $version->versionNumber();
            $versionModel->storage_path = (string) $version->storagePath();
            $versionModel->hash = (string) $version->hash();
            $versionModel->size = $version->size()->toBytes();
            $versionModel->created_at = $version->createdAt();
            $versionModel->save();
        }
    }

    public function delete(FileId $id): void
    {
        FileModel::where('id', (string) $id)->delete();
    }

    public function findByHash(FileHash $hash): ?File
    {
        $model = FileModel::with('versions')
            ->where('hash', (string) $hash)
            ->first();

        if ($model === null) {
            return null;
        }

        return $this->toDomain($model);
    }

    private function toDomain(FileModel $model): File
    {
        $versions = $model->versions->map(function (FileVersionModel $versionModel) {
            return FileVersion::reconstituteFromPersistence(
                (int) $versionModel->version_number,
                StoragePath::create($versionModel->storage_path),
                FileHash::create($versionModel->hash),
                FileSize::create((int) $versionModel->size),
                new DateTimeImmutable($versionModel->created_at->format('Y-m-d H:i:s'))
            );
        })->toArray();

        return File::reconstituteFromPersistence(
            FileId::create($model->id),
            FileName::create($model->name),
            FileSize::create((int) $model->size),
            MimeType::create($model->mime_type),
            StoragePath::create($model->storage_path),
            FileHash::create($model->hash),
            new DateTimeImmutable($model->uploaded_at->format('Y-m-d H:i:s')),
            $versions
        );
    }
}
