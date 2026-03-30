<?php

declare(strict_types=1);

namespace FileVault\Domain\Share\Repository;

use FileVault\Domain\Share\Entity\Share;
use FileVault\Domain\Share\Entity\ShareLink;
use FileVault\Domain\Share\ValueObject\ShareId;

interface ShareRepositoryInterface
{
    public function findById(ShareId $id): ?Share;

    public function findByToken(ShareLink $link): ?Share;

    public function save(Share $share): void;

    public function delete(ShareId $id): void;
}
