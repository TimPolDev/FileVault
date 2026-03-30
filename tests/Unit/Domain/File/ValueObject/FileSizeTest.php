<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\File\ValueObject;

use FileVault\Domain\File\Exception\InvalidFileSizeException;
use FileVault\Domain\File\ValueObject\FileSize;
use PHPUnit\Framework\TestCase;

final class FileSizeTest extends TestCase
{
    public function test_creates_valid_file_size(): void
    {
        $fileSize = FileSize::create(1024);

        $this->assertEquals(1024, $fileSize->toBytes());
    }

    public function test_throws_exception_for_negative_size(): void
    {
        $this->expectException(InvalidFileSizeException::class);
        $this->expectExceptionMessage('File size must be positive');

        FileSize::create(-100);
    }

    public function test_throws_exception_for_zero_size(): void
    {
        $this->expectException(InvalidFileSizeException::class);
        $this->expectExceptionMessage('File size cannot be zero');

        FileSize::create(0);
    }

    public function test_to_string_returns_bytes(): void
    {
        $fileSize = FileSize::create(2048);

        $this->assertEquals('2048', (string) $fileSize);
    }

    public function test_equals_returns_true_for_same_size(): void
    {
        $fileSize1 = FileSize::create(1024);
        $fileSize2 = FileSize::create(1024);

        $this->assertTrue($fileSize1->equals($fileSize2));
    }

    public function test_equals_returns_false_for_different_size(): void
    {
        $fileSize1 = FileSize::create(1024);
        $fileSize2 = FileSize::create(2048);

        $this->assertFalse($fileSize1->equals($fileSize2));
    }

    public function test_converts_to_human_readable_bytes(): void
    {
        $fileSize = FileSize::create(500);

        $this->assertEquals('500 B', $fileSize->toHumanReadable());
    }

    public function test_converts_to_human_readable_kilobytes(): void
    {
        $fileSize = FileSize::create(2048);

        $this->assertEquals('2 KB', $fileSize->toHumanReadable());
    }

    public function test_converts_to_human_readable_megabytes(): void
    {
        $fileSize = FileSize::create(5 * 1024 * 1024);

        $this->assertEquals('5 MB', $fileSize->toHumanReadable());
    }

    public function test_converts_to_human_readable_gigabytes(): void
    {
        $fileSize = FileSize::create(3 * 1024 * 1024 * 1024);

        $this->assertEquals('3 GB', $fileSize->toHumanReadable());
    }

    public function test_converts_to_human_readable_with_decimals(): void
    {
        $fileSize = FileSize::create(1536);

        $this->assertEquals('1.5 KB', $fileSize->toHumanReadable());
    }
}
