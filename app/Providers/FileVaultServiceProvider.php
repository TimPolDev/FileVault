<?php

declare(strict_types=1);

namespace App\Providers;

use FileVault\Domain\File\Repository\FileRepositoryInterface;
use FileVault\Domain\Share\Repository\ShareRepositoryInterface;
use FileVault\Domain\Storage\Port\StorageAdapterInterface;
use FileVault\Infrastructure\Persistence\Eloquent\EloquentFileRepository;
use FileVault\Infrastructure\Persistence\Eloquent\EloquentShareRepository;
use FileVault\Infrastructure\Storage\LocalStorageAdapter;
use FileVault\Infrastructure\Storage\S3StorageAdapter;
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

        // Bind Storage Adapter based on configuration
        $this->app->bind(StorageAdapterInterface::class, function () {
            $driver = config('filesystems.storage_driver', 'local');

            return match ($driver) {
                's3' => new S3StorageAdapter(),
                default => new LocalStorageAdapter(),
            };
        });
    }

    public function boot(): void
    {
        //
    }
}
