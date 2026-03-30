<?php

declare(strict_types=1);

namespace FileVault\Domain\File\Event;

use DateTimeImmutable;
use FileVault\Domain\File\ValueObject\FileId;
use FileVault\Domain\Shared\Event\DomainEvent;

final readonly class NewVersionCreated implements DomainEvent
{
    public function __construct(
        private FileId $fileId,
        private int $versionNumber,
        private DateTimeImmutable $occurredAt
    ) {
    }

    public function fileId(): FileId
    {
        return $this->fileId;
    }

    public function versionNumber(): int
    {
        return $this->versionNumber;
    }

    public function occurredAt(): DateTimeImmutable
    {
        return $this->occurredAt;
    }
}
