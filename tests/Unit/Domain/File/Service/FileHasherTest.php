<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\File\Service;

use FileVault\Domain\File\Service\FileHasher;
use FileVault\Domain\File\ValueObject\FileHash;
use PHPUnit\Framework\TestCase;

final class FileHasherTest extends TestCase
{
    private FileHasher $hasher;

    protected function setUp(): void
    {
        parent::setUp();
        $this->hasher = new FileHasher();
    }

    public function test_hashes_string_content(): void
    {
        $content = 'Hello World';
        $hash = $this->hasher->hash($content);

        $this->assertInstanceOf(FileHash::class, $hash);
        // SHA-256 of "Hello World"
        $this->assertEquals('a591a6d40bf420404a011733cfb7b190d62c65bf0bcda32b57b277d9ad9f146e', (string) $hash);
    }

    public function test_returns_consistent_hash_for_same_content(): void
    {
        $content = 'test content';
        $hash1 = $this->hasher->hash($content);
        $hash2 = $this->hasher->hash($content);

        $this->assertTrue($hash1->equals($hash2));
    }

    public function test_returns_different_hash_for_different_content(): void
    {
        $hash1 = $this->hasher->hash('content A');
        $hash2 = $this->hasher->hash('content B');

        $this->assertFalse($hash1->equals($hash2));
    }

    public function test_hashes_empty_string(): void
    {
        $hash = $this->hasher->hash('');

        $this->assertInstanceOf(FileHash::class, $hash);
        // SHA-256 of empty string
        $this->assertEquals('e3b0c44298fc1c149afbf4c8996fb92427ae41e4649b934ca495991b7852b855', (string) $hash);
    }

    public function test_hash_is_64_characters_long(): void
    {
        $hash = $this->hasher->hash('some content');

        $this->assertEquals(64, strlen((string) $hash));
    }

    public function test_hash_file_throws_exception_when_file_not_found(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('File not found');

        $this->hasher->hashFile('/non/existent/file.txt');
    }

    public function test_hash_file_computes_hash_from_file(): void
    {
        $tempFile = tempnam(sys_get_temp_dir(), 'test_');
        file_put_contents($tempFile, 'Test file content');

        try {
            $hash = $this->hasher->hashFile($tempFile);

            $this->assertInstanceOf(FileHash::class, $hash);
            // Verify it's a valid hash (64 hex characters)
            $this->assertEquals(64, strlen((string) $hash));
            $this->assertMatchesRegularExpression('/^[a-f0-9]{64}$/', (string) $hash);
        } finally {
            unlink($tempFile);
        }
    }

    public function test_hash_file_returns_same_hash_for_same_content(): void
    {
        $tempFile1 = tempnam(sys_get_temp_dir(), 'test1_');
        $tempFile2 = tempnam(sys_get_temp_dir(), 'test2_');
        $content = 'Identical content in both files';

        file_put_contents($tempFile1, $content);
        file_put_contents($tempFile2, $content);

        try {
            $hash1 = $this->hasher->hashFile($tempFile1);
            $hash2 = $this->hasher->hashFile($tempFile2);

            $this->assertTrue($hash1->equals($hash2));
        } finally {
            unlink($tempFile1);
            unlink($tempFile2);
        }
    }
}
