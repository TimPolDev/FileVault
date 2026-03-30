<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\File\ValueObject;

use FileVault\Domain\File\Exception\InvalidFileNameException;
use FileVault\Domain\File\ValueObject\FileName;
use PHPUnit\Framework\TestCase;

final class FileNameTest extends TestCase
{
    public function test_creates_valid_file_name(): void
    {
        $fileName = FileName::create('document.pdf');

        $this->assertEquals('document.pdf', (string) $fileName);
    }

    public function test_trims_whitespace(): void
    {
        $fileName = FileName::create('  document.pdf  ');

        $this->assertEquals('document.pdf', (string) $fileName);
    }

    public function test_throws_exception_for_empty_name(): void
    {
        $this->expectException(InvalidFileNameException::class);
        $this->expectExceptionMessage('File name cannot be empty');

        FileName::create('');
    }

    public function test_throws_exception_for_whitespace_only(): void
    {
        $this->expectException(InvalidFileNameException::class);

        FileName::create('   ');
    }

    public function test_throws_exception_for_too_long_name(): void
    {
        $this->expectException(InvalidFileNameException::class);
        $this->expectExceptionMessage('File name is too long');

        $longName = str_repeat('a', 256) . '.txt';
        FileName::create($longName);
    }

    public function test_throws_exception_for_invalid_characters(): void
    {
        $this->expectException(InvalidFileNameException::class);
        $this->expectExceptionMessage('contains invalid characters');

        FileName::create('file<name>.txt');
    }

    public function test_rejects_invalid_characters_set(): void
    {
        $invalidChars = ['<', '>', ':', '"', '/', '\\', '|', '?', '*'];

        foreach ($invalidChars as $char) {
            try {
                FileName::create("file{$char}name.txt");
                $this->fail("Should have thrown exception for character: {$char}");
            } catch (InvalidFileNameException $e) {
                $this->addToAssertionCount(1);
            }
        }
    }

    public function test_equals_returns_true_for_same_name(): void
    {
        $fileName1 = FileName::create('document.pdf');
        $fileName2 = FileName::create('document.pdf');

        $this->assertTrue($fileName1->equals($fileName2));
    }

    public function test_equals_returns_false_for_different_name(): void
    {
        $fileName1 = FileName::create('document.pdf');
        $fileName2 = FileName::create('photo.jpg');

        $this->assertFalse($fileName1->equals($fileName2));
    }

    public function test_extracts_extension(): void
    {
        $fileName = FileName::create('document.pdf');

        $this->assertEquals('pdf', $fileName->extension());
    }

    public function test_extracts_extension_case_insensitive(): void
    {
        $fileName = FileName::create('document.PDF');

        $this->assertEquals('pdf', $fileName->extension());
    }

    public function test_returns_null_extension_when_no_extension(): void
    {
        $fileName = FileName::create('document');

        $this->assertNull($fileName->extension());
    }

    public function test_handles_multiple_dots_in_filename(): void
    {
        $fileName = FileName::create('my.file.name.tar.gz');

        $this->assertEquals('gz', $fileName->extension());
    }
}
