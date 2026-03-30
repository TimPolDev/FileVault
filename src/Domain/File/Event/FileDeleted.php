<?php

declare(strict_types=1);

namespace FileVault\Domain\File\Event;

use DateTimeImmutable;
use FileVault\Domain\File\ValueObject\FileId;
use FileVault\Domain\Shared\Event\DomainEvent;

final readonly class FileDeleted implements DomainEvent
{
    public function __construct(
        private FileId $fileId,
        private DateTimeImmutable $occurredAt
    ) {
    }

    public function fileId(): FileId
    {
        return $this->fileId;
    }

    public function occurredAt(): DateTimeImmutable
    {
        return $this->occurredAt;
    }
}
