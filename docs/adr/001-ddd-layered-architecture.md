# ADR 001: DDD Layered Architecture

## Status

Accepted

## Context

FileVault is designed as a demonstration project showcasing architectural best practices for a file management backend service. We need an architecture that clearly separates concerns, allows for testability without infrastructure dependencies, and demonstrates enterprise-grade code organization.

## Decision

We will structure the codebase using **Domain-Driven Design (DDD)** principles with a clear three-layer architecture:

### 1. Domain Layer (`src/Domain/`)
- **Responsibility**: Contains the pure business logic and rules
- **No dependencies**: Zero external dependencies, no frameworks (including Laravel)
- **Components**:
  - Entities: Core business objects (File, FileVersion, Share)
  - Value Objects: Immutable data types (FileName, FileSize, MimeType, etc.)
  - Repository Interfaces: Contracts for data persistence (ports)
  - Domain Events: Events that capture business-significant occurrences
  - Domain Services: Business logic that doesn't belong to a single entity

### 2. Application Layer (`src/Application/`)
- **Responsibility**: Orchestrates use cases and coordinates Domain operations
- **Pattern**: CQRS (Command Query Responsibility Segregation)
- **Components**:
  - Commands: Write operations (UploadFile, DeleteFile, CreateShare)
  - Queries: Read operations (GetFileVersion, AccessShare)
  - Handlers: Execute commands and queries using Domain objects
  - DTOs: Data Transfer Objects for input/output

### 3. Infrastructure Layer (`src/Infrastructure/`)
- **Responsibility**: Implements technical details and adapters
- **Framework integration**: Contains Laravel-specific code
- **Components**:
  - Eloquent Models & Repositories: Data persistence implementations
  - Storage Adapters: File storage implementations (Local, S3)
  - HTTP Controllers: REST API endpoints
  - Event Listeners: Async processing of Domain Events

## Consequences

### Positive
- **Testability**: Domain logic can be tested without database or framework
- **Maintainability**: Clear separation of concerns makes code easier to understand
- **Flexibility**: Infrastructure can be swapped (e.g., different storage backends)
- **Educational value**: Demonstrates professional architecture patterns
- **Framework independence**: Domain logic is not coupled to Laravel

### Negative
- **Initial complexity**: More files and structure than a simple CRUD app
- **Learning curve**: Developers need to understand DDD concepts
- **Boilerplate**: More code compared to Active Record pattern

## Compliance

- All Domain code must have zero `use Illuminate\...` statements
- Repository interfaces defined in Domain, implemented in Infrastructure
- No Eloquent models in Application or Domain layers
- Controllers in Infrastructure delegate to Application layer handlers
