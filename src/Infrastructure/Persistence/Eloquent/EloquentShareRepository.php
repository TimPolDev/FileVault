<?php

declare(strict_types=1);

namespace FileVault\Infrastructure\Persistence\Eloquent;

use DateTimeImmutable;
use FileVault\Domain\File\ValueObject\FileId;
use FileVault\Domain\Share\Entity\Share;
use FileVault\Domain\Share\Entity\ShareLink;
use FileVault\Domain\Share\Repository\ShareRepositoryInterface;
use FileVault\Domain\Share\ValueObject\ExpiresAt;
use FileVault\Domain\Share\ValueObject\Permission;
use FileVault\Domain\Share\ValueObject\ShareId;
use FileVault\Infrastructure\Persistence\Models\ShareModel;

final class EloquentShareRepository implements ShareRepositoryInterface
{
    public function findById(ShareId $id): ?Share
    {
        $model = ShareModel::find((string) $id);

        if ($model === null) {
            return null;
        }

        return $this->toDomain($model);
    }

    public function findByToken(ShareLink $link): ?Share
    {
        $model = ShareModel::where('token', $link->token())->first();

        if ($model === null) {
            return null;
        }

        return $this->toDomain($model);
    }

    public function save(Share $share): void
    {
        $model = ShareModel::findOrNew((string) $share->id());

        $model->id = (string) $share->id();
        $model->file_id = (string) $share->fileId();
        $model->token = (string) $share->link();
        $model->permission = $share->permission()->value;
        $model->expires_at = $share->expiresAt()?->value();

        $model->save();
    }

    public function delete(ShareId $id): void
    {
        ShareModel::where('id', (string) $id)->delete();
    }

    private function toDomain(ShareModel $model): Share
    {
        $expiresAt = $model->expires_at !== null
            ? ExpiresAt::create(new DateTimeImmutable($model->expires_at->format('Y-m-d H:i:s')))
            : null;

        return Share::reconstituteFromPersistence(
            ShareId::create($model->id),
            FileId::create($model->file_id),
            ShareLink::fromToken($model->token),
            Permission::from($model->permission),
            $expiresAt,
            new DateTimeImmutable($model->created_at->format('Y-m-d H:i:s'))
        );
    }
}
