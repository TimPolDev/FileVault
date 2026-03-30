<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\Share\ValueObject;

use DateTimeImmutable;
use FileVault\Domain\Share\ValueObject\ExpiresAt;
use PHPUnit\Framework\TestCase;

final class ExpiresAtTest extends TestCase
{
    public function test_creates_expiration_date(): void
    {
        $dateTime = new DateTimeImmutable('2024-12-31 23:59:59');
        $expiresAt = ExpiresAt::create($dateTime);

        $this->assertEquals($dateTime, $expiresAt->value());
    }

    public function test_creates_expiration_from_now(): void
    {
        $expiresAt = ExpiresAt::fromNow(7);

        $this->assertInstanceOf(ExpiresAt::class, $expiresAt);
        $this->assertFalse($expiresAt->isExpired());
    }

    public function test_is_expired_returns_true_for_past_date(): void
    {
        $pastDate = new DateTimeImmutable('-1 day');
        $expiresAt = ExpiresAt::create($pastDate);

        $this->assertTrue($expiresAt->isExpired());
    }

    public function test_is_expired_returns_false_for_future_date(): void
    {
        $futureDate = new DateTimeImmutable('+1 day');
        $expiresAt = ExpiresAt::create($futureDate);

        $this->assertFalse($expiresAt->isExpired());
    }

    public function test_never_returns_null(): void
    {
        $result = ExpiresAt::never();

        $this->assertNull($result);
    }

    public function test_to_string_formats_date(): void
    {
        $dateTime = new DateTimeImmutable('2024-03-30 15:30:45');
        $expiresAt = ExpiresAt::create($dateTime);

        $this->assertEquals('2024-03-30 15:30:45', (string) $expiresAt);
    }

    public function test_equals_returns_true_for_same_date(): void
    {
        $dateTime = new DateTimeImmutable('2024-03-30 12:00:00');
        $expiresAt1 = ExpiresAt::create($dateTime);
        $expiresAt2 = ExpiresAt::create($dateTime);

        $this->assertTrue($expiresAt1->equals($expiresAt2));
    }

    public function test_equals_returns_false_for_different_date(): void
    {
        $expiresAt1 = ExpiresAt::create(new DateTimeImmutable('2024-03-30'));
        $expiresAt2 = ExpiresAt::create(new DateTimeImmutable('2024-03-31'));

        $this->assertFalse($expiresAt1->equals($expiresAt2));
    }
}
