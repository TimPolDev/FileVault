<?php

declare(strict_types=1);

namespace FileVault\Domain\Share\ValueObject;

use DateTimeImmutable;

final class ExpiresAt
{
    private function __construct(
        private readonly DateTimeImmutable $value
    ) {
    }

    public static function create(DateTimeImmutable $dateTime): self
    {
        return new self($dateTime);
    }

    public static function never(): ?self
    {
        return null;
    }

    public static function fromNow(int $days): self
    {
        $dateTime = new DateTimeImmutable("+{$days} days");
        return new self($dateTime);
    }

    public function isExpired(): bool
    {
        return $this->value < new DateTimeImmutable();
    }

    public function value(): DateTimeImmutable
    {
        return $this->value;
    }

    public function __toString(): string
    {
        return $this->value->format('Y-m-d H:i:s');
    }

    public function equals(self $other): bool
    {
        return $this->value == $other->value;
    }
}
