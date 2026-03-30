<?php

declare(strict_types=1);

namespace Tests\Unit\Application\File;

use FileVault\Application\File\UploadFile\UploadFileCommand;
use FileVault\Application\File\UploadFile\UploadFileHandler;
use FileVault\Domain\File\Entity\File;
use FileVault\Domain\File\Repository\FileRepositoryInterface;
use FileVault\Domain\File\Service\FileHasher;
use FileVault\Domain\File\Service\VersioningPolicy;
use FileVault\Domain\File\ValueObject\FileHash;
use FileVault\Domain\Storage\Port\StorageAdapterInterface;
use PHPUnit\Framework\TestCase;

final class UploadFileHandlerTest extends TestCase
{
    private FileRepositoryInterface $fileRepository;
    private StorageAdapterInterface $storageAdapter;
    private FileHasher $fileHasher;
    private VersioningPolicy $versioningPolicy;
    private UploadFileHandler $handler;

    protected function setUp(): void
    {
        parent::setUp();

        $this->fileRepository = $this->createMock(FileRepositoryInterface::class);
        $this->storageAdapter = $this->createMock(StorageAdapterInterface::class);
        $this->fileHasher = new FileHasher();
        $this->versioningPolicy = new VersioningPolicy();

        $this->handler = new UploadFileHandler(
            $this->fileRepository,
            $this->storageAdapter,
            $this->fileHasher,
            $this->versioningPolicy
        );
    }

    public function test_uploads_new_file(): void
    {
        $command = new UploadFileCommand(
            fileName: 'document.pdf',
            fileSize: 1024,
            mimeType: 'application/pdf',
            fileContent: 'test content'
        );

        $this->fileRepository
            ->expects($this->once())
            ->method('findByHash')
            ->willReturn(null);

        $this->storageAdapter
            ->expects($this->once())
            ->method('store');

        $this->fileRepository
            ->expects($this->once())
            ->method('save')
            ->with($this->isInstanceOf(File::class));

        $fileId = $this->handler->handle($command);

        $this->assertNotEmpty($fileId);
        $this->assertMatchesRegularExpression('/^[0-9a-f]{8}-[0-9a-f]{4}-4[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i', $fileId);
    }

    public function test_returns_existing_file_id_when_hash_matches(): void
    {
        $command = new UploadFileCommand(
            fileName: 'document.pdf',
            fileSize: 1024,
            mimeType: 'application/pdf',
            fileContent: 'test content'
        );

        // Create a real File entity instead of mocking
        $existingFile = File::upload(
            \FileVault\Domain\File\ValueObject\FileName::create('existing.pdf'),
            \FileVault\Domain\File\ValueObject\FileSize::create(1024),
            \FileVault\Domain\File\ValueObject\MimeType::create('application/pdf'),
            \FileVault\Domain\File\ValueObject\StoragePath::create('2024/03/abc123.bin'),
            $this->fileHasher->hash('test content')
        );

        $this->fileRepository
            ->expects($this->once())
            ->method('findByHash')
            ->willReturn($existingFile);

        $this->storageAdapter
            ->expects($this->never())
            ->method('store');

        $this->fileRepository
            ->expects($this->never())
            ->method('save');

        $fileId = $this->handler->handle($command);

        $this->assertEquals((string) $existingFile->id(), $fileId);
    }

    public function test_stores_file_with_correct_path_format(): void
    {
        $command = new UploadFileCommand(
            fileName: 'document.pdf',
            fileSize: 1024,
            mimeType: 'application/pdf',
            fileContent: 'test content'
        );

        $this->fileRepository
            ->method('findByHash')
            ->willReturn(null);

        $this->storageAdapter
            ->expects($this->once())
            ->method('store')
            ->with(
                $this->equalTo('test content'),
                $this->callback(function ($storagePath) {
                    $pathString = (string) $storagePath;
                    // Should match format: YYYY/MM/hashprefix.bin
                    return preg_match('/^\d{4}\/\d{2}\/[a-f0-9]{8}\.bin$/', $pathString) === 1;
                })
            );

        $this->fileRepository->method('save');

        $this->handler->handle($command);
    }
}
