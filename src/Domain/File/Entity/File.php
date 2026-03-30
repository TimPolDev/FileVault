<?php

declare(strict_types=1);

namespace FileVault\Domain\File\Entity;

use DateTimeImmutable;
use FileVault\Domain\File\ValueObject\FileHash;
use FileVault\Domain\File\ValueObject\FileId;
use FileVault\Domain\File\ValueObject\FileName;
use FileVault\Domain\File\ValueObject\FileSize;
use FileVault\Domain\File\ValueObject\MimeType;
use FileVault\Domain\File\ValueObject\StoragePath;

final class File
{
    private array $domainEvents = [];

    /** @var FileVersion[] */
    private array $versions = [];

    private function __construct(
        private readonly FileId $id,
        private readonly FileName $name,
        private readonly FileSize $size,
        private readonly MimeType $mimeType,
        private readonly StoragePath $storagePath,
        private readonly FileHash $hash,
        private readonly DateTimeImmutable $uploadedAt
    ) {
    }

    public static function upload(
        FileName $name,
        FileSize $size,
        MimeType $mimeType,
        StoragePath $storagePath,
        FileHash $hash
    ): self {
        $file = new self(
            FileId::generate(),
            $name,
            $size,
            $mimeType,
            $storagePath,
            $hash,
            new DateTimeImmutable()
        );

        $file->recordEvent(new \FileVault\Domain\File\Event\FileUploaded(
            $file->id,
            $file->name,
            $file->uploadedAt
        ));

        return $file;
    }

    public static function reconstituteFromPersistence(
        FileId $id,
        FileName $name,
        FileSize $size,
        MimeType $mimeType,
        StoragePath $storagePath,
        FileHash $hash,
        DateTimeImmutable $uploadedAt,
        array $versions = []
    ): self {
        $file = new self(
            $id,
            $name,
            $size,
            $mimeType,
            $storagePath,
            $hash,
            $uploadedAt
        );

        $file->versions = $versions;

        return $file;
    }

    public function addVersion(FileVersion $version): void
    {
        $this->versions[] = $version;

        $this->recordEvent(new \FileVault\Domain\File\Event\NewVersionCreated(
            $this->id,
            $version->versionNumber(),
            new DateTimeImmutable()
        ));
    }

    public function getLatestVersion(): ?FileVersion
    {
        if (empty($this->versions)) {
            return null;
        }

        usort($this->versions, fn(FileVersion $a, FileVersion $b) => $b->versionNumber() <=> $a->versionNumber());

        return $this->versions[0];
    }

    public function releaseEvents(): array
    {
        $events = $this->domainEvents;
        $this->domainEvents = [];

        return $events;
    }

    public function id(): FileId
    {
        return $this->id;
    }

    public function name(): FileName
    {
        return $this->name;
    }

    public function size(): FileSize
    {
        return $this->size;
    }

    public function mimeType(): MimeType
    {
        return $this->mimeType;
    }

    public function storagePath(): StoragePath
    {
        return $this->storagePath;
    }

    public function hash(): FileHash
    {
        return $this->hash;
    }

    public function uploadedAt(): DateTimeImmutable
    {
        return $this->uploadedAt;
    }

    /** @return FileVersion[] */
    public function versions(): array
    {
        return $this->versions;
    }

    private function recordEvent(object $event): void
    {
        $this->domainEvents[] = $event;
    }
}
