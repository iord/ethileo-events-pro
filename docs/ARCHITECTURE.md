# Architecture Documentation

## Overview

Ethileo Events Pro follows **Clean Architecture** principles, ensuring maintainability, testability, and scalability.

## Architecture Layers

### 1. Domain Layer (`src/Domain/`)

The core business logic layer, completely independent of external concerns.

**Components:**
- **Entities**: Core business objects (Event, Guest, Invitation, etc.)
- **Value Objects**: Immutable objects (UUID, Email, etc.)
- **Repository Interfaces**: Contracts for data access
- **Domain Events**: Business event notifications
- **Business Rules**: Core business logic

**Principles:**
- No dependencies on external frameworks
- Pure PHP objects
- Framework-agnostic
- Contains all business logic

### 2. Application Layer (`src/Application/`)

Orchestrates the flow of data between layers.

**Components:**
- **Use Cases**: Application-specific business rules
- **Services**: Application services
- **DTOs**: Data Transfer Objects
- **Handlers**: Event and command handlers

**Responsibilities:**
- Coordinate domain objects
- Implement use cases
- Handle application logic
- Transform data between layers

### 3. Infrastructure Layer (`src/Infrastructure/`)

Handles external concerns and implementations.

**Components:**
- **Persistence**: Database implementations
- **Storage**: File storage services
- **Email**: Email service implementations
- **External APIs**: Third-party integrations

**Technologies:**
- WordPress database (`wpdb`)
- File system
- External services

### 4. Presentation Layer (`src/Presentation/`)

Handles user interface and API endpoints.

**Components:**
- **Admin**: WordPress admin interface
- **Frontend**: Public-facing pages
- **API**: REST API controllers
- **CLI**: Command-line interface

## Design Patterns

### 1. Dependency Injection

All dependencies are injected via constructor using PHP-DI container.

```php
class EventService
{
    public function __construct(
        private EventRepositoryInterface $repository,
        private EventDispatcher $dispatcher
    ) {}
}
```

### 2. Repository Pattern

Data access is abstracted through repository interfaces.

```php
interface EventRepositoryInterface
{
    public function findById(int $id): ?Event;
    public function save(Event $event): bool;
}
```

### 3. Service Provider Pattern

Services are registered via service providers.

```php
class EventServiceProvider extends AbstractServiceProvider
{
    public function register(): void
    {
        $this->container->set(EventService::class, ...);
    }
}
```

### 4. Factory Pattern

Complex object creation is handled by factories.

```php
class EventFactory
{
    public static function create(array $data): Event
    {
        // Complex creation logic
    }
}
```

## SOLID Principles

### Single Responsibility Principle (SRP)
Each class has one reason to change.

### Open/Closed Principle (OCP)
Open for extension, closed for modification.

### Liskov Substitution Principle (LSP)
Subtypes must be substitutable for their base types.

### Interface Segregation Principle (ISP)
Clients should not depend on interfaces they don't use.

### Dependency Inversion Principle (DIP)
Depend on abstractions, not concretions.

## Data Flow

```
User Request
    ↓
Presentation Layer (Controller)
    ↓
Application Layer (Use Case)
    ↓
Domain Layer (Entity + Repository Interface)
    ↓
Infrastructure Layer (Repository Implementation)
    ↓
Database
```

## Testing Strategy

### Unit Tests
- Test domain entities and value objects
- Test use cases in isolation
- Mock dependencies

### Integration Tests
- Test repository implementations
- Test service integrations
- Use test database

### Feature Tests
- Test complete user flows
- Test API endpoints
- Test admin interfaces

## Security

- Input validation at all layers
- Sanitization in presentation layer
- WordPress nonces for CSRF protection
- Capability checks for authorization
- Prepared statements for SQL injection prevention

## Performance

- Lazy loading of dependencies
- Database query optimization
- Caching strategy
- Asset minification
- Image optimization

## Scalability

- Horizontal scaling ready
- Stateless design
- Queue support for async tasks
- CDN ready for assets
