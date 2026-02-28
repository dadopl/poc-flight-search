# poc-flight-search

Flight Search Engine — proof-of-concept using Symfony 7.4, Domain-Driven Design, Hexagonal Architecture, and CQRS.

## Requirements

- PHP 8.3+
- Composer
- SQLite (development) or PostgreSQL 16+ (production)

## Quick Start

```bash
# Install dependencies
composer install

# Copy and configure environment
cp .env .env.local
# Edit .env.local and set APP_SECRET

# Run database migrations
make migrate

# Verify setup
php bin/console about
```

## Development

```bash
# Run tests
make test

# Static analysis (PHPStan level 8)
make stan

# Code style check (PSR-12)
make cs

# Run all migrations
make migrate
```

## Database Configuration

By default, SQLite is used for development (no server needed):

```
DATABASE_URL="sqlite:///%kernel.project_dir%/var/data.db"
```

For production, set the `DATABASE_URL` environment variable to a PostgreSQL connection:

```
DATABASE_URL="postgresql://user:password@127.0.0.1:5432/flight_search?serverVersion=16&charset=utf8"
```

For tests, a separate SQLite database is used automatically (`var/data_test.db`).

## CI/CD

GitHub Actions runs on every push:
- PHPStan level 8 (static analysis)
- PHP_CodeSniffer PSR-12 (code style)
- PHPUnit (unit/integration tests)

See `.github/workflows/ci.yml`.
