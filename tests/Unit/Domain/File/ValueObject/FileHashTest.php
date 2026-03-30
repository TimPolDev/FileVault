<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\File\ValueObject;

use FileVault\Domain\File\Exception\InvalidFileHashException;
use FileVault\Domain\File\ValueObject\FileHash;
use PHPUnit\Framework\TestCase;

final class FileHashTest extends TestCase
{
    private const VALID_HASH = 'e3b0c44298fc1c149afbf4c8996fb92427ae41e4649b934ca495991b7852b855';

    public function test_creates_valid_file_hash(): void
    {
        $fileHash = FileHash::create(self::VALID_HASH);

        $this->assertEquals(self::VALID_HASH, (string) $fileHash);
    }

    public function test_normalizes_hash_to_lowercase(): void
    {
        $upperCaseHash = strtoupper(self::VALID_HASH);
        $fileHash = FileHash::create($upperCaseHash);

        $this->assertEquals(self::VALID_HASH, (string) $fileHash);
    }

    public function test_throws_exception_for_invalid_length(): void
    {
        $this->expectException(InvalidFileHashException::class);
        $this->expectExceptionMessage('Invalid hash length');

        FileHash::create('abc123');
    }

    public function test_throws_exception_for_non_hexadecimal(): void
    {
        $this->expectException(InvalidFileHashException::class);
        $this->expectExceptionMessage('Invalid hash format');

        $invalidHash = str_repeat('g', 64);
        FileHash::create($invalidHash);
    }

    public function test_throws_exception_for_hash_with_special_characters(): void
    {
        $this->expectException(InvalidFileHashException::class);

        $invalidHash = str_repeat('a', 63) . '!';
        FileHash::create($invalidHash);
    }

    public function test_equals_returns_true_for_same_hash(): void
    {
        $fileHash1 = FileHash::create(self::VALID_HASH);
        $fileHash2 = FileHash::create(self::VALID_HASH);

        $this->assertTrue($fileHash1->equals($fileHash2));
    }

    public function test_equals_returns_false_for_different_hash(): void
    {
        $fileHash1 = FileHash::create(self::VALID_HASH);
        $fileHash2 = FileHash::create(str_repeat('a', 64));

        $this->assertFalse($fileHash1->equals($fileHash2));
    }

    public function test_short_hash_returns_first_8_characters_by_default(): void
    {
        $fileHash = FileHash::create(self::VALID_HASH);

        $this->assertEquals('e3b0c442', $fileHash->shortHash());
    }

    public function test_short_hash_returns_custom_length(): void
    {
        $fileHash = FileHash::create(self::VALID_HASH);

        $this->assertEquals('e3b0c44298', $fileHash->shortHash(10));
    }

    public function test_accepts_all_hexadecimal_characters(): void
    {
        $hexHash = str_repeat('0123456789abcdef', 4);
        $fileHash = FileHash::create($hexHash);

        $this->assertEquals($hexHash, (string) $fileHash);
    }
}
