<?php

declare(strict_types=1);

namespace FileVault\Domain\File\ValueObject;

use FileVault\Domain\File\Exception\InvalidMimeTypeException;

final class MimeType
{
    private const PATTERN = '/^[a-z]+\/[a-z0-9\-\+\.]+$/i';

    private function __construct(
        private readonly string $value
    ) {
    }

    public static function create(string $value): self
    {
        if (!preg_match(self::PATTERN, $value)) {
            throw InvalidMimeTypeException::invalidFormat($value);
        }

        return new self($value);
    }

    public function __toString(): string
    {
        return $this->value;
    }

    public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }

    public function type(): string
    {
        return explode('/', $this->value)[0];
    }

    public function subtype(): string
    {
        return explode('/', $this->value)[1];
    }

    public function isImage(): bool
    {
        return $this->type() === 'image';
    }

    public function isVideo(): bool
    {
        return $this->type() === 'video';
    }

    public function isAudio(): bool
    {
        return $this->type() === 'audio';
    }
}
