# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

Atoolo Web Account Bundle is a Symfony bundle providing user registration and authentication for Sitepark CMS-based websites. It enables features like user registration, password recovery, and JWT-based authentication via GraphQL mutations.

**Key Technologies:**
- PHP 8.1-8.4
- Symfony 6.3+ / 7.3+
- GraphQL (via overblog/graphql-bundle)
- JWT Authentication (via lexik/jwt-authentication-bundle)
- PSR-4 autoloading: `Atoolo\WebAccount\` → `src/`

## Common Development Commands

### Code Quality & Analysis
```bash
# Run all analysis checks
composer analyse

# Individual checks
composer analyse:phpstan        # PHPStan level 9 analysis
composer analyse:phpcsfixer     # PER-CS code style check
composer analyse:phplint        # PHP syntax validation
composer analyse:compatibilitycheck  # PHP 8.1-8.4 compatibility check

# Fix code style
composer cs-fix
```

### Testing
```bash
# Run all tests with coverage
composer test

# Run tests directly with PHPUnit
./tools/phpunit.phar -c phpunit.xml --coverage-text

# Run mutation testing
composer test:infection

# Run a single test
./tools/phpunit.phar -c phpunit.xml --filter testMethodName
./tools/phpunit.phar -c phpunit.xml path/to/SpecificTest.php
```

### Code Style
- Uses **PER-CS** (PHP Evolving Recommendation Coding Style) via PHP-CS-Fixer
- Code style configuration: `.php-cs-fixer.dist.php`
- Fix violations: `composer cs-fix` or `./tools/php-cs-fixer fix`

## Architecture Overview

### Bundle Structure

The bundle follows Symfony best practices with clear separation of concerns:

```
src/
├── Dto/                    # Data Transfer Objects for domain entities
├── Exception/              # Custom exceptions
├── GraphQL/                # GraphQL mutation resolvers and input types
├── Security/               # Symfony Security components (Authenticator, UserProvider)
└── Service/                # Business logic services
```

### Key Architectural Patterns

**GraphQL-First API:**
- All user-facing operations exposed as GraphQL mutations
- GraphQL schema definitions: `config/graphql/`
- Resolvers in `src/GraphQL/` using `Overblog\GraphQLBundle\Annotation`

**External IES Integration:**
- Bundle communicates with external IES (Identity & Email Service) via GraphQL
- `GraphQLClient` service handles IES communication
- `IesUrlResolver` determines IES endpoint from resource channel

**JWT Token Management:**
- Authentication results in JWT cookie (`WEB_ACCOUNT_TOKEN`)
- `WebAccountAuthenticator` validates JWT from cookies
- Tokens contain user data + roles with `ROLE_` prefix
- TTL configurable via bundle config (default: 30 days)

**Configuration Loading:**
- `ConfigurationLoader` loads web-account configs from `{resource_channel}/config/web-account/*.php`
- Configs define email settings, registration flows, etc.
- Uses PHP-based config files returning arrays

### Core Services

**Authentication Flow:**
1. `GraphQL/Authentication::authenticationWithPassword()` - GraphQL mutation endpoint
2. `Service/Authentication/UsernamePasswordAuthentication` - Authenticates via IES
3. JWT token created and stored in `CookieJar`
4. `ApplyCookieJarListener` attaches cookies to response

**Registration Flow:**
1. `GraphQL/Registration::startRegistration()` - Initiates registration, sends email
2. `Service/Registration/StartRegistration` - Calls IES to create challenge
3. User receives email with code
4. `GraphQL/Registration::finishRegistration()` - Completes registration with code
5. `Service/Registration/FinishRegistration` - Validates and creates user in IES

**Password Recovery Flow:**
- Similar two-step process via `GraphQL/Password` mutations
- `StartPasswordRecovery` and `FinishPasswordRecovery` services

### Symfony Security Integration

- `WebAccountAuthenticator` - Validates JWT from cookies
- `WebAccountUserProvider` - Provides user instances
- `UnauthorizedEntryPoint` - Redirects unauthorized users (configurable)
- Roles prefixed with `ROLE_` (e.g., `ROLE_WEB_ACCOUNT`)

## Important Conventions

### DTOs and Data Flow
- DTOs in `src/Dto/` represent domain entities and requests/responses
- Request DTOs (e.g., `StartRegistrationRequest`) passed to services
- Result DTOs (e.g., `StartRegistrationResult`) returned from services
- GraphQL Input types in `src/GraphQL/Input/` wrap DTOs for GraphQL layer

### Error Handling
- Service exceptions extend `ServiceException`
- GraphQL union types handle multiple return types (e.g., success | error)
- Example: `FinishUserRegistrationResultType` = `FinishRegistrationResult | EmailAlreadyExistsError`
- Type resolvers map response objects to GraphQL types

### Cookie Management
- `CookieJar` service collects cookies during request processing
- `ApplyCookieJarListener` applies cookies to response
- Cookie name constant: `CookieJar::WEB_ACCOUNT_TOKEN_NAME`

## Configuration

Bundle configuration in `config/packages/atoolo_web_account.yaml`:

```yaml
atoolo_web_account:
  token_ttl: 2592000                 # JWT token TTL (30 days)
  registration_token_ttl: 7200       # Registration token TTL (2 hours)
  password_reset_token_ttl: 7200     # Password reset token TTL (2 hours)
  unauthorized_entry_point: '/account'  # Redirect URL for unauthorized users
```

## Development Notes

- PHPStan level 9 enforced
- All code must pass PHP 8.1-8.4 compatibility checks
- Test coverage reports: `var/log/clover/`
- GraphQL schema changes require updating YAML files in `config/graphql/types/`
- Bundle auto-loads services via `config/services.yaml` (autowire + autoconfigure)
