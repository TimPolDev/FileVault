<?php

declare(strict_types=1);

namespace App\Providers;

use FileVault\Domain\File\Repository\FileRepositoryInterface;
use FileVault\Domain\Share\Repository\ShareRepositoryInterface;
use FileVault\Infrastructure\Persistence\Eloquent\EloquentFileRepository;
use FileVault\Infrastructure\Persistence\Eloquent\EloquentShareRepository;
use Illuminate\Support\ServiceProvider;

final class FileVaultServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Bind Repository Interfaces to Eloquent implementations
        $this->app->bind(
            FileRepositoryInterface::class,
            EloquentFileRepository::class
        );

        $this->app->bind(
            ShareRepositoryInterface::class,
            EloquentShareRepository::class
        );
    }

    public function boot(): void
    {
        //
    }
}
