# ADR 003: Swappable Storage Adapter Pattern (Ports & Adapters)

## Status

Accepted

## Context

FileVault needs to store file content in a persistent manner. Different deployment environments may require different storage backends:
- **Development/Testing**: Local filesystem for simplicity and speed
- **Production**: Cloud object storage (S3, MinIO) for scalability and durability

We need a design that allows switching storage backends without modifying application or domain logic.

## Decision

We will implement the **Ports & Adapters pattern** (also known as Hexagonal Architecture) for storage abstraction:

### Port (Domain Layer)
```php
// src/Domain/Storage/Port/StorageAdapterInterface.php
interface StorageAdapterInterface {
    public function store(string $content, StoragePath $path): void;
    public function retrieve(StoragePath $path): string;
    public function delete(StoragePath $path): void;
    public function exists(StoragePath $path): bool;
}
```

This interface is defined in the **Domain layer** and represents the contract that any storage implementation must fulfill.

### Adapters (Infrastructure Layer)
Two concrete implementations in the Infrastructure layer:

1. **LocalStorageAdapter**: Uses Laravel's local filesystem driver
2. **S3StorageAdapter**: Uses Laravel's S3 driver (compatible with AWS S3 and MinIO)

### Configuration-Based Switching
The active adapter is determined by the `STORAGE_DRIVER` environment variable:

```env
STORAGE_DRIVER=local  # Use LocalStorageAdapter
STORAGE_DRIVER=s3     # Use S3StorageAdapter
```

Binding is configured in `FileVaultServiceProvider`:
```php
$this->app->bind(StorageAdapterInterface::class, function () {
    $driver = config('filesystems.storage_driver', 'local');

    return match ($driver) {
        's3' => new S3StorageAdapter(),
        default => new LocalStorageAdapter(),
    };
});
```

## Consequences

### Positive
- **Testability**: Can easily mock `StorageAdapterInterface` in tests
- **Flexibility**: Switch storage backends without code changes (only config)
- **Domain purity**: Domain layer has zero knowledge of Laravel Storage or cloud providers
- **Development speed**: Local storage is faster for development and testing
- **Production ready**: Can use S3/MinIO for production deployments
- **Open/Closed Principle**: Easy to add new adapters (e.g., FTP, SFTP) without modifying existing code

### Negative
- **Abstraction overhead**: Small performance cost from interface indirection
- **Learning curve**: Developers must understand the Ports & Adapters pattern
- **Configuration required**: Must remember to set `STORAGE_DRIVER` in production

### Trade-offs
- We abstracted only the 4 essential operations (store, retrieve, delete, exists)
- More advanced features (streaming, presigned URLs) would require extending the interface
- This is acceptable for our current use case

## Implementation Notes

1. Both adapters delegate to Laravel's `Storage` facade internally
2. The `StoragePath` Value Object ensures type safety across all storage operations
3. Exceptions are thrown for file-not-found scenarios (fail fast)
4. The adapters use the same filesystem disk names as configured in `config/filesystems.php`

## Alternatives Considered

1. **Direct Laravel Storage facade usage**: Rejected because it couples the domain to Laravel
2. **Repository pattern for storage**: Over-engineering; simple adapter is sufficient
3. **Flysystem directly**: Laravel already wraps Flysystem nicely; no need to bypass it

## References

- Hexagonal Architecture: https://alistair.cockburn.us/hexagonal-architecture/
- Dependency Inversion Principle (SOLID)
- Clean Architecture by Robert C. Martin
