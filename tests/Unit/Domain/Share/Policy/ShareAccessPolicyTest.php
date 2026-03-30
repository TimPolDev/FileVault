<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\Share\Policy;

use DateTimeImmutable;
use FileVault\Domain\File\ValueObject\FileId;
use FileVault\Domain\Share\Entity\Share;
use FileVault\Domain\Share\Policy\ShareAccessPolicy;
use FileVault\Domain\Share\ValueObject\ExpiresAt;
use FileVault\Domain\Share\ValueObject\Permission;
use PHPUnit\Framework\TestCase;

final class ShareAccessPolicyTest extends TestCase
{
    private ShareAccessPolicy $policy;

    protected function setUp(): void
    {
        parent::setUp();
        $this->policy = new ShareAccessPolicy();
    }

    public function test_can_access_returns_true_when_share_not_expired_and_has_permission(): void
    {
        $share = Share::create(
            FileId::generate(),
            Permission::READ,
            ExpiresAt::create(new DateTimeImmutable('+1 day'))
        );

        $this->assertTrue($this->policy->canAccess($share, Permission::READ));
    }

    public function test_can_access_returns_false_when_share_is_expired(): void
    {
        $share = Share::create(
            FileId::generate(),
            Permission::READ,
            ExpiresAt::create(new DateTimeImmutable('-1 day'))
        );

        $this->assertFalse($this->policy->canAccess($share, Permission::READ));
    }

    public function test_can_access_returns_false_when_insufficient_permission(): void
    {
        $share = Share::create(
            FileId::generate(),
            Permission::READ,
            ExpiresAt::create(new DateTimeImmutable('+1 day'))
        );

        $this->assertFalse($this->policy->canAccess($share, Permission::ADMIN));
    }

    public function test_can_access_returns_true_for_never_expiring_share(): void
    {
        $share = Share::create(
            FileId::generate(),
            Permission::READ,
            null
        );

        $this->assertTrue($this->policy->canAccess($share, Permission::READ));
    }

    public function test_is_expired_returns_true_for_expired_share(): void
    {
        $share = Share::create(
            FileId::generate(),
            Permission::READ,
            ExpiresAt::create(new DateTimeImmutable('-1 hour'))
        );

        $this->assertTrue($this->policy->isExpired($share));
    }

    public function test_is_expired_returns_false_for_active_share(): void
    {
        $share = Share::create(
            FileId::generate(),
            Permission::READ,
            ExpiresAt::create(new DateTimeImmutable('+1 hour'))
        );

        $this->assertFalse($this->policy->isExpired($share));
    }

    public function test_admin_permission_allows_all_access_levels(): void
    {
        $share = Share::create(
            FileId::generate(),
            Permission::ADMIN,
            null
        );

        $this->assertTrue($this->policy->canAccess($share, Permission::READ));
        $this->assertTrue($this->policy->canAccess($share, Permission::WRITE));
        $this->assertTrue($this->policy->canAccess($share, Permission::ADMIN));
    }

    public function test_write_permission_allows_read_and_write(): void
    {
        $share = Share::create(
            FileId::generate(),
            Permission::WRITE,
            null
        );

        $this->assertTrue($this->policy->canAccess($share, Permission::READ));
        $this->assertTrue($this->policy->canAccess($share, Permission::WRITE));
        $this->assertFalse($this->policy->canAccess($share, Permission::ADMIN));
    }
}
