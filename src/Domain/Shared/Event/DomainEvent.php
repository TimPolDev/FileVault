<?php

declare(strict_types=1);

namespace FileVault\Domain\Shared\Event;

use DateTimeImmutable;

interface DomainEvent
{
    public function occurredAt(): DateTimeImmutable;
}
