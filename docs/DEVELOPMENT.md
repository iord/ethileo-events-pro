# Development Guide

## Prerequisites

- Docker Desktop installed
- Git
- Basic knowledge of PHP, WordPress, and Docker

## Getting Started

### 1. Clone the Repository

```bash
git clone https://github.com/yourusername/ethileo-events-pro.git
cd ethileo-events-pro
```

### 2. Start Docker Environment

```bash
docker-compose up -d
```

### 3. Install WordPress

```bash
make install
```

This will:
- Start all Docker containers
- Install WordPress
- Install Composer dependencies
- Activate the plugin

### 4. Access the Site

- **WordPress**: http://localhost:8080
- **Admin**: http://localhost:8080/wp-admin (admin/admin)
- **PHPMyAdmin**: http://localhost:8081 (root/rootpassword)

## Development Workflow

### Code Structure

```
src/
├── Core/                   # Application bootstrap
├── Domain/                 # Business logic
│   ├── Event/
│   ├── Guest/
│   ├── Invitation/
│   ├── QRCode/
│   └── Photo/
├── Application/           # Use cases & services
├── Infrastructure/        # External implementations
└── Presentation/          # UI & API
```

### Adding a New Feature

1. **Define Domain Entity**
```php
// src/Domain/NewFeature/Entity/NewFeature.php
class NewFeature {
    // Entity logic
}
```

2. **Create Repository Interface**
```php
// src/Domain/NewFeature/Repository/NewFeatureRepositoryInterface.php
interface NewFeatureRepositoryInterface {
    public function save(NewFeature $entity): bool;
}
```

3. **Implement Repository**
```php
// src/Infrastructure/Persistence/WordPress/WordPressNewFeatureRepository.php
class WordPressNewFeatureRepository implements NewFeatureRepositoryInterface {
    // Implementation
}
```

4. **Create Use Case**
```php
// src/Application/UseCase/CreateNewFeature.php
class CreateNewFeature {
    public function execute(array $data): NewFeature {
        // Business logic
    }
}
```

5. **Add Controller**
```php
// src/Presentation/Admin/NewFeatureController.php
class NewFeatureController {
    // Handle HTTP requests
}
```

### Running Tests

```bash
# All tests
make test

# Unit tests only
docker-compose exec wordpress composer test:unit

# Integration tests
docker-compose exec wordpress composer test:integration

# With coverage
docker-compose exec wordpress composer test:coverage
```

### Code Quality

```bash
# Check code style
composer phpcs

# Fix code style
composer phpcbf

# Static analysis
composer phpstan

# Run all checks
composer check
```

### Working with Database

```bash
# Access MySQL
docker-compose exec mysql mysql -u wordpress -pwordpress wordpress

# Or use PHPMyAdmin
open http://localhost:8081
```

### WP-CLI Commands

```bash
# List plugins
make wp ARGS="plugin list"

# Create user
make wp ARGS="user create john john@example.com"

# Clear cache
make wp ARGS="cache flush"
```

### Debugging

Enable debugging in `wp-config.php`:

```php
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);
define('WP_DEBUG_DISPLAY', false);
```

Logs are located at: `wp-content/debug.log`

### Frontend Assets

```bash
# Install NPM dependencies
docker-compose exec wordpress npm install

# Watch for changes (development)
docker-compose exec wordpress npm run dev

# Build for production
docker-compose exec wordpress npm run build
```

## Best Practices

### 1. Follow PSR Standards
- PSR-4: Autoloading
- PSR-12: Coding style

### 2. Write Tests
- Test all business logic
- Aim for 80%+ coverage
- Use meaningful test names

### 3. Document Your Code
- PHPDoc for all public methods
- README for each module
- Update CHANGELOG

### 4. Use Type Hints
```php
public function create(string $title, DateTimeImmutable $date): Event
{
    // ...
}
```

### 5. Dependency Injection
Never use `new` for dependencies - always inject them.

### 6. Follow SOLID Principles
- Single Responsibility
- Open/Closed
- Liskov Substitution
- Interface Segregation
- Dependency Inversion

## Troubleshooting

### Docker Issues

```bash
# Restart containers
make restart

# Clean and restart
make clean
make install
```

### Permission Issues

```bash
docker-compose exec wordpress chown -R www-data:www-data /var/www/html
```

### Database Issues

```bash
# Reset database
docker-compose down -v
make install
```

## Resources

- [WordPress Coding Standards](https://developer.wordpress.org/coding-standards/)
- [PHP-DI Documentation](https://php-di.org/)
- [PHPUnit Documentation](https://phpunit.de/)
- [Clean Architecture](https://blog.cleancoder.com/uncle-bob/2012/08/13/the-clean-architecture.html)
