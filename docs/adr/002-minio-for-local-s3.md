# ADR 002: MinIO for Local S3-Compatible Storage

## Status

Accepted

## Context

FileVault needs to support cloud object storage (S3) for file persistence, both for production environments and local development. Developers need a way to test S3 integration locally without requiring AWS credentials or incurring cloud costs.

## Decision

We will use **MinIO** as an S3-compatible storage solution for local development environments.

MinIO is an open-source, high-performance object storage system that implements the Amazon S3 API. It runs locally in a Docker container and provides:

- Full S3 API compatibility
- Web-based management console (available on port 9001)
- Zero-cost local development
- Easy bucket management and file browsing
- No AWS account or credentials required for development

## Configuration

### Docker Setup
- MinIO runs as a service in `docker-compose.yml`
- API endpoint: `http://localhost:9000`
- Console endpoint: `http://localhost:9001`
- Default credentials: `minioadmin` / `minioadmin` (configurable via environment variables)

### Environment Variables
```env
AWS_ENDPOINT=http://minio:9000
AWS_USE_PATH_STYLE_ENDPOINT=true
AWS_ACCESS_KEY_ID=minioadmin
AWS_SECRET_ACCESS_KEY=minioadmin
AWS_BUCKET=filevault
MINIO_ROOT_USER=minioadmin
MINIO_ROOT_PASSWORD=minioadmin
```

### Storage Driver Selection
The application uses a `STORAGE_DRIVER` environment variable to switch between:
- `local` - Laravel's local filesystem driver (default for development)
- `s3` - S3-compatible storage (MinIO locally, AWS S3 in production)

## Consequences

### Positive
- **Development parity**: Developers can test S3 integration locally
- **Cost-effective**: No AWS charges during development
- **Fast feedback**: No network latency to cloud services
- **Offline development**: Works without internet connection
- **Visual debugging**: MinIO console allows browsing uploaded files
- **Production-ready**: Same S3 API used in production (AWS S3)

### Negative
- **Additional service**: Adds one more container to the Docker stack
- **Different implementation**: MinIO may have subtle differences from AWS S3
- **Not 100% feature parity**: Some advanced S3 features may not be available in MinIO

## Alternatives Considered

1. **LocalStack**: More comprehensive AWS emulation but heavier and more complex
2. **AWS S3 directly**: Requires credentials and incurs costs during development
3. **Filesystem only**: Simpler but doesn't test cloud storage integration

## Notes

For production deployments, set `STORAGE_DRIVER=s3` and configure AWS credentials to use real AWS S3 buckets. The `StorageAdapterInterface` abstraction ensures the application code remains unchanged.
