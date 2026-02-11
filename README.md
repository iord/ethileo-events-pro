# Ethileo Events Pro

**Enterprise-Grade Event Management System for WordPress**

[![License: GPL v3](https://img.shields.io/badge/License-GPLv3-blue.svg)](https://www.gnu.org/licenses/gpl-3.0)
[![PHP Version](https://img.shields.io/badge/PHP-7.4%2B-blue.svg)](https://php.net)
[![WordPress](https://img.shields.io/badge/WordPress-5.8%2B-blue.svg)](https://wordpress.org)

## 🏗️ Architecture

Built with **Clean Architecture** principles, following **SOLID**, **DDD** (Domain-Driven Design), and modern PHP best practices.

### Architecture Layers

```
┌─────────────────────────────────────────────────────────┐
│                   Presentation Layer                     │
│           (Controllers, Views, API, CLI)                 │
├─────────────────────────────────────────────────────────┤
│                   Application Layer                      │
│          (Use Cases, Services, DTOs, Handlers)           │
├─────────────────────────────────────────────────────────┤
│                     Domain Layer                         │
│  (Entities, Value Objects, Repository Interfaces,        │
│              Domain Events, Business Logic)              │
├─────────────────────────────────────────────────────────┤
│                  Infrastructure Layer                    │
│  (Repository Implementations, External Services,         │
│        Database, File System, Third-party APIs)          │
└─────────────────────────────────────────────────────────┘
```

## 🎯 Features

- ✅ **Event Management** - Create, manage, and customize events
- ✅ **Digital Invitations** - Beautiful e-invites with RSVP tracking
- ✅ **Guest Management** - Import, organize, and track guests
- ✅ **QR Code Generation** - Unique QR codes for guests and photo sharing
- ✅ **Photo Sharing** - Guest photo uploads with instant gallery
- ✅ **Calendar Integration** - iCal (.ics) export for all calendars
- ✅ **Membership Integration** - Works with Paid Memberships Pro
- ✅ **Multi-language** - WPML compatible
- ✅ **RESTful API** - Full API for integrations
- ✅ **WooCommerce Integration** - Sell event packages

## 📁 Project Structure

```
ethileo-events-pro/
├── src/
│   ├── Core/                   # Core framework (DI, Config, Bootstrap)
│   ├── Domain/                 # Business logic & entities
│   │   ├── Event/
│   │   ├── Guest/
│   │   ├── Invitation/
│   │   ├── QRCode/
│   │   ├── Photo/
│   │   └── Shared/            # Shared domain concepts
│   ├── Application/           # Use cases & application services
│   │   ├── UseCase/
│   │   ├── Service/
│   │   └── DTO/
│   ├── Infrastructure/        # External concerns
│   │   ├── Persistence/
│   │   ├── Storage/
│   │   ├── Email/
│   │   └── ExternalAPI/
│   └── Presentation/          # User interface
│       ├── Admin/
│       ├── Frontend/
│       ├── API/
│       └── CLI/
├── assets/
│   ├── css/
│   ├── js/
│   └── images/
├── config/                    # Configuration files
├── database/
│   └── migrations/            # Database migrations
├── resources/
│   ├── views/                 # Blade-style templates
│   └── lang/                  # Translations
├── tests/
│   ├── Unit/
│   ├── Integration/
│   └── Feature/
├── docker/                    # Docker configuration
├── docs/                      # Documentation
├── composer.json
├── package.json
└── ethileo-events-pro.php    # Main plugin file
```

## 🚀 Quick Start (Local Development)

### Prerequisites

- Docker Desktop
- Git

### Installation

```bash
# Clone the repository
git clone https://github.com/yourusername/ethileo-events-pro.git
cd ethileo-events-pro

# Start Docker environment
docker-compose up -d

# Install dependencies
docker-compose exec wordpress composer install
docker-compose exec wordpress npm install

# Access the site
open http://localhost:8080
```

**WordPress Admin:**
- URL: http://localhost:8080/wp-admin
- Username: `admin`
- Password: `admin`

## 📚 Documentation

- [Architecture Guide](docs/ARCHITECTURE.md)
- [Development Guide](docs/DEVELOPMENT.md)
- [API Documentation](docs/API.md)
- [Deployment Guide](docs/DEPLOYMENT.md)

## 🧪 Testing

```bash
# Run all tests
composer test

# Unit tests only
composer test:unit

# Integration tests
composer test:integration

# Code coverage
composer test:coverage
```

## 🛠️ Tech Stack

- **PHP 7.4+** with modern OOP patterns
- **WordPress 5.8+**
- **Composer** for dependency management
- **PSR-4** autoloading
- **PSR-12** coding standards
- **Dependency Injection** container
- **Repository Pattern** for data access
- **Event-Driven Architecture**
- **REST API** with JWT authentication
- **Docker** for local development

## 📦 Key Dependencies

- `php-di/php-di` - Dependency injection container
- `vlucas/phpdotenv` - Environment configuration
- `endroid/qr-code` - QR code generation
- `phpmailer/phpmailer` - Email handling
- `league/fractal` - API transformations
- `monolog/monolog` - Logging

## 🔧 Development

### Code Standards

```bash
# Check code style
composer phpcs

# Fix code style
composer phpcbf

# Static analysis
composer phpstan
```

### Git Workflow

```bash
# Create feature branch
git checkout -b feature/new-feature

# Make changes and commit
git add .
git commit -m "feat: add new feature"

# Push to remote
git push origin feature/new-feature
```

## 🤝 Contributing

Contributions are welcome! Please read our [Contributing Guide](CONTRIBUTING.md).

## 📝 License

GPL v3 or later. See [LICENSE](LICENSE) for details.

## 👥 Authors

- **Your Team** - Initial work

## 🙏 Acknowledgments

- WordPress Community
- PHP Community
- All contributors

---

**Made with ❤️ for event organizers worldwide**
