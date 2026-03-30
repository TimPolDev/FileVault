<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\File\Entity;

use DateTimeImmutable;
use FileVault\Domain\File\Entity\File;
use FileVault\Domain\File\Entity\FileVersion;
use FileVault\Domain\File\ValueObject\FileHash;
use FileVault\Domain\File\ValueObject\FileId;
use FileVault\Domain\File\ValueObject\FileName;
use FileVault\Domain\File\ValueObject\FileSize;
use FileVault\Domain\File\ValueObject\MimeType;
use FileVault\Domain\File\ValueObject\StoragePath;
use PHPUnit\Framework\TestCase;

final class FileTest extends TestCase
{
    public function test_uploads_new_file(): void
    {
        $file = File::upload(
            FileName::create('document.pdf'),
            FileSize::create(1024),
            MimeType::create('application/pdf'),
            StoragePath::create('2024/03/abc123.bin'),
            FileHash::create(str_repeat('a', 64))
        );

        $this->assertInstanceOf(File::class, $file);
        $this->assertEquals('document.pdf', (string) $file->name());
        $this->assertEquals(1024, $file->size()->toBytes());
        $this->assertEquals('application/pdf', (string) $file->mimeType());
        $this->assertInstanceOf(DateTimeImmutable::class, $file->uploadedAt());
    }

    public function test_generates_unique_id_on_upload(): void
    {
        $file1 = File::upload(
            FileName::create('file1.txt'),
            FileSize::create(100),
            MimeType::create('text/plain'),
            StoragePath::create('2024/03/file1.bin'),
            FileHash::create(str_repeat('a', 64))
        );

        $file2 = File::upload(
            FileName::create('file2.txt'),
            FileSize::create(200),
            MimeType::create('text/plain'),
            StoragePath::create('2024/03/file2.bin'),
            FileHash::create(str_repeat('b', 64))
        );

        $this->assertNotEquals((string) $file1->id(), (string) $file2->id());
    }

    public function test_reconstitutes_from_persistence(): void
    {
        $id = FileId::generate();
        $uploadedAt = new DateTimeImmutable('2024-03-30 10:00:00');

        $file = File::reconstituteFromPersistence(
            $id,
            FileName::create('document.pdf'),
            FileSize::create(1024),
            MimeType::create('application/pdf'),
            StoragePath::create('2024/03/abc123.bin'),
            FileHash::create(str_repeat('a', 64)),
            $uploadedAt
        );

        $this->assertTrue($file->id()->equals($id));
        $this->assertEquals($uploadedAt, $file->uploadedAt());
    }

    public function test_adds_version(): void
    {
        $file = File::upload(
            FileName::create('document.pdf'),
            FileSize::create(1024),
            MimeType::create('application/pdf'),
            StoragePath::create('2024/03/abc123.bin'),
            FileHash::create(str_repeat('a', 64))
        );

        $version = FileVersion::create(
            1,
            StoragePath::create('2024/03/abc456.bin'),
            FileHash::create(str_repeat('b', 64)),
            FileSize::create(2048)
        );

        $file->addVersion($version);

        $this->assertCount(1, $file->versions());
        $this->assertEquals(1, $file->versions()[0]->versionNumber());
    }

    public function test_get_latest_version_returns_null_when_no_versions(): void
    {
        $file = File::upload(
            FileName::create('document.pdf'),
            FileSize::create(1024),
            MimeType::create('application/pdf'),
            StoragePath::create('2024/03/abc123.bin'),
            FileHash::create(str_repeat('a', 64))
        );

        $this->assertNull($file->getLatestVersion());
    }

    public function test_get_latest_version_returns_most_recent_version(): void
    {
        $file = File::upload(
            FileName::create('document.pdf'),
            FileSize::create(1024),
            MimeType::create('application/pdf'),
            StoragePath::create('2024/03/abc123.bin'),
            FileHash::create(str_repeat('a', 64))
        );

        $version1 = FileVersion::create(
            1,
            StoragePath::create('2024/03/v1.bin'),
            FileHash::create(str_repeat('b', 64)),
            FileSize::create(2048)
        );

        $version2 = FileVersion::create(
            2,
            StoragePath::create('2024/03/v2.bin'),
            FileHash::create(str_repeat('c', 64)),
            FileSize::create(3072)
        );

        $version3 = FileVersion::create(
            3,
            StoragePath::create('2024/03/v3.bin'),
            FileHash::create(str_repeat('d', 64)),
            FileSize::create(4096)
        );

        $file->addVersion($version1);
        $file->addVersion($version3);
        $file->addVersion($version2);

        $latestVersion = $file->getLatestVersion();

        $this->assertNotNull($latestVersion);
        $this->assertEquals(3, $latestVersion->versionNumber());
    }

    public function test_release_events_returns_empty_array_initially(): void
    {
        $file = File::upload(
            FileName::create('document.pdf'),
            FileSize::create(1024),
            MimeType::create('application/pdf'),
            StoragePath::create('2024/03/abc123.bin'),
            FileHash::create(str_repeat('a', 64))
        );

        $events = $file->releaseEvents();

        $this->assertIsArray($events);
        // Domain events will be tested in COMMIT 05
    }

    public function test_release_events_clears_internal_events(): void
    {
        $file = File::upload(
            FileName::create('document.pdf'),
            FileSize::create(1024),
            MimeType::create('application/pdf'),
            StoragePath::create('2024/03/abc123.bin'),
            FileHash::create(str_repeat('a', 64))
        );

        $file->releaseEvents();
        $secondRelease = $file->releaseEvents();

        $this->assertCount(0, $secondRelease);
    }

    public function test_exposes_all_value_objects(): void
    {
        $file = File::upload(
            FileName::create('document.pdf'),
            FileSize::create(1024),
            MimeType::create('application/pdf'),
            StoragePath::create('2024/03/abc123.bin'),
            FileHash::create(str_repeat('a', 64))
        );

        $this->assertInstanceOf(FileId::class, $file->id());
        $this->assertInstanceOf(FileName::class, $file->name());
        $this->assertInstanceOf(FileSize::class, $file->size());
        $this->assertInstanceOf(MimeType::class, $file->mimeType());
        $this->assertInstanceOf(StoragePath::class, $file->storagePath());
        $this->assertInstanceOf(FileHash::class, $file->hash());
    }
}
