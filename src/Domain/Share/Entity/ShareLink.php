<?php

declare(strict_types=1);

namespace FileVault\Domain\Share\Entity;

final class ShareLink
{
    private const TOKEN_LENGTH = 32;

    private function __construct(
        private readonly string $token
    ) {
    }

    public static function generate(): self
    {
        $token = bin2hex(random_bytes(self::TOKEN_LENGTH));
        return new self($token);
    }

    public static function fromToken(string $token): self
    {
        if (strlen($token) !== self::TOKEN_LENGTH * 2) {
            throw new \InvalidArgumentException('Invalid token length');
        }

        if (!ctype_xdigit($token)) {
            throw new \InvalidArgumentException('Token must be hexadecimal');
        }

        return new self($token);
    }

    public function token(): string
    {
        return $this->token;
    }

    public function __toString(): string
    {
        return $this->token;
    }

    public function equals(self $other): bool
    {
        return $this->token === $other->token;
    }
}
