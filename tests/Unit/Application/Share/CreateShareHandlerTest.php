<?php

declare(strict_types=1);

namespace Tests\Unit\Application\Share;

use FileVault\Application\Share\CreateShare\CreateShareCommand;
use FileVault\Application\Share\CreateShare\CreateShareHandler;
use FileVault\Application\Share\DTO\ShareDTO;
use FileVault\Domain\File\Entity\File;
use FileVault\Domain\File\Repository\FileRepositoryInterface;
use FileVault\Domain\File\ValueObject\FileHash;
use FileVault\Domain\File\ValueObject\FileId;
use FileVault\Domain\File\ValueObject\FileName;
use FileVault\Domain\File\ValueObject\FileSize;
use FileVault\Domain\File\ValueObject\MimeType;
use FileVault\Domain\File\ValueObject\StoragePath;
use FileVault\Domain\Share\Entity\Share;
use FileVault\Domain\Share\Repository\ShareRepositoryInterface;
use PHPUnit\Framework\TestCase;

final class CreateShareHandlerTest extends TestCase
{
    private FileRepositoryInterface $fileRepository;
    private ShareRepositoryInterface $shareRepository;
    private CreateShareHandler $handler;

    protected function setUp(): void
    {
        parent::setUp();

        $this->fileRepository = $this->createMock(FileRepositoryInterface::class);
        $this->shareRepository = $this->createMock(ShareRepositoryInterface::class);

        $this->handler = new CreateShareHandler(
            $this->fileRepository,
            $this->shareRepository
        );
    }

    public function test_creates_share_successfully(): void
    {
        $fileId = (string) FileId::generate();
        $command = new CreateShareCommand(
            fileId: $fileId,
            permission: 'read',
            expiresInDays: 7
        );

        $file = File::upload(
            FileName::create('test.pdf'),
            FileSize::create(1024),
            MimeType::create('application/pdf'),
            StoragePath::create('2024/03/abc123.bin'),
            FileHash::create('a665a45920422f9d417e4867efdc4fb8a04a1f3fff1fa07e998e86f7f7a27ae3')
        );

        $this->fileRepository
            ->expects($this->once())
            ->method('findById')
            ->willReturn($file);

        $this->shareRepository
            ->expects($this->once())
            ->method('save')
            ->with($this->isInstanceOf(Share::class));

        $result = $this->handler->handle($command);

        $this->assertInstanceOf(ShareDTO::class, $result);
        $this->assertEquals($fileId, $result->fileId);
        $this->assertEquals('read', $result->permission);
        $this->assertNotNull($result->token);
        $this->assertNotNull($result->expiresAt);
    }

    public function test_creates_share_without_expiration(): void
    {
        $fileId = (string) FileId::generate();
        $command = new CreateShareCommand(
            fileId: $fileId,
            permission: 'write',
            expiresInDays: null
        );

        $file = File::upload(
            FileName::create('test.pdf'),
            FileSize::create(1024),
            MimeType::create('application/pdf'),
            StoragePath::create('2024/03/abc123.bin'),
            FileHash::create('a665a45920422f9d417e4867efdc4fb8a04a1f3fff1fa07e998e86f7f7a27ae3')
        );

        $this->fileRepository
            ->method('findById')
            ->willReturn($file);

        $this->shareRepository
            ->expects($this->once())
            ->method('save');

        $result = $this->handler->handle($command);

        $this->assertInstanceOf(ShareDTO::class, $result);
        $this->assertEquals('write', $result->permission);
        $this->assertNull($result->expiresAt);
    }

    public function test_throws_exception_when_file_not_found(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('File not found');

        $command = new CreateShareCommand(
            fileId: (string) FileId::generate(),
            permission: 'read'
        );

        $this->fileRepository
            ->method('findById')
            ->willReturn(null);

        $this->handler->handle($command);
    }

    public function test_generates_unique_token(): void
    {
        $fileId = (string) FileId::generate();
        $command = new CreateShareCommand(
            fileId: $fileId,
            permission: 'admin'
        );

        $file = File::upload(
            FileName::create('test.pdf'),
            FileSize::create(1024),
            MimeType::create('application/pdf'),
            StoragePath::create('2024/03/abc123.bin'),
            FileHash::create('a665a45920422f9d417e4867efdc4fb8a04a1f3fff1fa07e998e86f7f7a27ae3')
        );

        $this->fileRepository
            ->method('findById')
            ->willReturn($file);

        $this->shareRepository
            ->method('save');

        $result = $this->handler->handle($command);

        $this->assertNotEmpty($result->token);
        $this->assertEquals(64, strlen($result->token));
        $this->assertTrue(ctype_xdigit($result->token));
    }
}
