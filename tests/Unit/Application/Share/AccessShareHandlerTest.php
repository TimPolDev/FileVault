<?php

declare(strict_types=1);

namespace Tests\Unit\Application\Share;

use DateTimeImmutable;
use FileVault\Application\Share\AccessShare\AccessShareHandler;
use FileVault\Application\Share\AccessShare\AccessShareQuery;
use FileVault\Application\Share\DTO\FileContentDTO;
use FileVault\Domain\File\Entity\File;
use FileVault\Domain\File\Repository\FileRepositoryInterface;
use FileVault\Domain\File\ValueObject\FileHash;
use FileVault\Domain\File\ValueObject\FileId;
use FileVault\Domain\File\ValueObject\FileName;
use FileVault\Domain\File\ValueObject\FileSize;
use FileVault\Domain\File\ValueObject\MimeType;
use FileVault\Domain\File\ValueObject\StoragePath;
use FileVault\Domain\Share\Entity\Share;
use FileVault\Domain\Share\Entity\ShareLink;
use FileVault\Domain\Share\Policy\ShareAccessPolicy;
use FileVault\Domain\Share\Repository\ShareRepositoryInterface;
use FileVault\Domain\Share\ValueObject\ExpiresAt;
use FileVault\Domain\Share\ValueObject\Permission;
use FileVault\Domain\Storage\Port\StorageAdapterInterface;
use PHPUnit\Framework\TestCase;

final class AccessShareHandlerTest extends TestCase
{
    private ShareRepositoryInterface $shareRepository;
    private FileRepositoryInterface $fileRepository;
    private StorageAdapterInterface $storageAdapter;
    private ShareAccessPolicy $accessPolicy;
    private AccessShareHandler $handler;

    protected function setUp(): void
    {
        parent::setUp();

        $this->shareRepository = $this->createMock(ShareRepositoryInterface::class);
        $this->fileRepository = $this->createMock(FileRepositoryInterface::class);
        $this->storageAdapter = $this->createMock(StorageAdapterInterface::class);
        $this->accessPolicy = new ShareAccessPolicy();

        $this->handler = new AccessShareHandler(
            $this->shareRepository,
            $this->fileRepository,
            $this->storageAdapter,
            $this->accessPolicy
        );
    }

    public function test_accesses_share_successfully(): void
    {
        $token = bin2hex(random_bytes(32));
        $query = new AccessShareQuery($token);

        $fileId = FileId::generate();
        $share = Share::create(
            $fileId,
            Permission::READ,
            null
        );

        $file = File::upload(
            FileName::create('test.pdf'),
            FileSize::create(1024),
            MimeType::create('application/pdf'),
            StoragePath::create('2024/03/abc123.bin'),
            FileHash::create('a665a45920422f9d417e4867efdc4fb8a04a1f3fff1fa07e998e86f7f7a27ae3')
        );

        $this->shareRepository
            ->expects($this->once())
            ->method('findByToken')
            ->willReturn($share);

        $this->fileRepository
            ->expects($this->once())
            ->method('findById')
            ->willReturn($file);

        $this->storageAdapter
            ->expects($this->once())
            ->method('retrieve')
            ->willReturn('file content');

        $result = $this->handler->handle($query);

        $this->assertInstanceOf(FileContentDTO::class, $result);
        $this->assertEquals('test.pdf', $result->fileName);
        $this->assertEquals(1024, $result->fileSize);
        $this->assertEquals('application/pdf', $result->mimeType);
        $this->assertEquals('file content', $result->content);
    }

    public function test_throws_exception_when_share_not_found(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Share not found');

        $token = bin2hex(random_bytes(32));
        $query = new AccessShareQuery($token);

        $this->shareRepository
            ->method('findByToken')
            ->willReturn(null);

        $this->handler->handle($query);
    }

    public function test_throws_exception_when_share_is_expired(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Access denied');

        $token = bin2hex(random_bytes(32));
        $query = new AccessShareQuery($token);

        $fileId = FileId::generate();
        $expiredDate = new DateTimeImmutable('-1 day');
        $share = Share::create(
            $fileId,
            Permission::READ,
            ExpiresAt::create($expiredDate)
        );

        $this->shareRepository
            ->method('findByToken')
            ->willReturn($share);

        $this->handler->handle($query);
    }

    public function test_throws_exception_when_permission_insufficient(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Access denied');

        $token = bin2hex(random_bytes(32));
        $query = new AccessShareQuery(
            token: $token,
            requiredPermission: 'write'
        );

        $fileId = FileId::generate();
        $share = Share::create(
            $fileId,
            Permission::READ,
            null
        );

        $this->shareRepository
            ->method('findByToken')
            ->willReturn($share);

        $this->handler->handle($query);
    }

    public function test_throws_exception_when_file_not_found(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('File not found');

        $token = bin2hex(random_bytes(32));
        $query = new AccessShareQuery($token);

        $fileId = FileId::generate();
        $share = Share::create(
            $fileId,
            Permission::READ,
            null
        );

        $this->shareRepository
            ->method('findByToken')
            ->willReturn($share);

        $this->fileRepository
            ->method('findById')
            ->willReturn(null);

        $this->handler->handle($query);
    }

    public function test_allows_access_with_higher_permission(): void
    {
        $token = bin2hex(random_bytes(32));
        $query = new AccessShareQuery(
            token: $token,
            requiredPermission: 'read'
        );

        $fileId = FileId::generate();
        $share = Share::create(
            $fileId,
            Permission::ADMIN,
            null
        );

        $file = File::upload(
            FileName::create('test.pdf'),
            FileSize::create(1024),
            MimeType::create('application/pdf'),
            StoragePath::create('2024/03/abc123.bin'),
            FileHash::create('a665a45920422f9d417e4867efdc4fb8a04a1f3fff1fa07e998e86f7f7a27ae3')
        );

        $this->shareRepository
            ->method('findByToken')
            ->willReturn($share);

        $this->fileRepository
            ->method('findById')
            ->willReturn($file);

        $this->storageAdapter
            ->method('retrieve')
            ->willReturn('file content');

        $result = $this->handler->handle($query);

        $this->assertInstanceOf(FileContentDTO::class, $result);
    }
}
