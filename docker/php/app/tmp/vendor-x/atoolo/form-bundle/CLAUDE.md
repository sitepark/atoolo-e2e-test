# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

**Atoolo Form Bundle** is a Symfony bundle providing an HTTP interface for displaying and processing forms using [JSON Forms](https://jsonforms.io/). It integrates with the Atoolo Resource Bundle to load form definitions from resources and processes submissions through a configurable processor pipeline.

- **Language**: PHP 8.1+ (tested on 8.2, 8.3, 8.4)
- **Framework**: Symfony 6.3+ / 7.0+
- **License**: MIT
- **Documentation**: https://sitepark.github.io/atoolo-docs/develop/bundles/form/

## Development Commands

### Setup
```bash
# Install dependencies
composer install

# Install PHAR tools via Phive (runs automatically post-install)
phive install --force-accept-unsigned --trust-gpg-keys C00543248C87FB13,4AA394086372C20A,CF1A108D0E7AE720,51C67305FFC2E5C0,E82B2FB314E9906E
```

### Testing
```bash
# Run all tests with coverage
composer test
./tools/phpunit.phar -c phpunit.xml --coverage-text

# Run specific test file
./tools/phpunit.phar test/Service/FormDefinitionLoaderTest.php

# Run specific test method
./tools/phpunit.phar --filter testMethodName test/Path/To/TestFile.php

# Mutation testing (8 threads, covered code only)
composer test:infection
vendor/bin/infection --threads=8 --no-progress --only-covered -s
```

### Code Quality
```bash
# Run all analysis checks
composer analyse

# Individual checks
composer analyse:phplint        # PHP syntax linting
composer analyse:phpstan        # Static analysis (Level 9)
composer analyse:phpcsfixer     # Code style checking (PER-CS)
composer analyse:compatibilitycheck  # PHP compatibility check

# Fix code style issues
composer cs-fix
./vendor/bin/phpcbf
./tools/php-cs-fixer fix

# Generate PHPStan report (XML)
composer report:phpstan
```

### Build Outputs
- PHPUnit coverage: `var/log/clover/` (HTML + XML)
- PHPUnit results: `var/log/surefire-reports/surefire-report.xml`
- PHPStan report: `var/log/phpstan-report.xml`
- Cache directories: `var/cache/`

## Architecture

### Layered Architecture

```
┌─────────────────────────────────────────────────────────┐
│                 HTTP/Controller Layer                    │
│            FormController (REST API)                    │
└──────────────────────────┬──────────────────────────────┘
                           │
┌──────────────────────────┴──────────────────────────────┐
│               Service/Application Layer                  │
│  FormDefinitionLoader  SubmitHandler  FormReader        │
│  Email Services        JsonSchemaValidator              │
└──────────────────┬──────────────────────────────────────┘
                   │
┌──────────────────┴──────────────────────────────────────┐
│              Processor Pipeline Layer                    │
│  IpAllower → IpBlocker → IpLimiter → SubmitLimiter     │
│  → SpamDetector → JsonSchemaValidator → EmailSender    │
└──────────────────┬──────────────────────────────────────┘
                   │
┌──────────────────┴──────────────────────────────────────┐
│               Data Transfer Objects                      │
│  FormDefinition  FormSubmission  UISchema Elements      │
└──────────────────────────────────────────────────────────┘
```

### Key Components

#### Controllers (`src/Controller/`)
- **FormController** - REST API with two endpoints:
  - `GET /api/form/{locale}/{location}/{component}` - Load form definition
  - `POST /api/form/{locale}/{location}/{component}` - Submit form data
  - Handles multilocale support via Atoolo Resource Bundle

#### DTOs (`src/Dto/`)
- **Core DTOs**: FormDefinition, FormSubmission, UploadFile
- **UISchema DTOs** (`Dto/UISchema/`): Element, Layout, Control, Annotation, Type, Role
  - Polymorphic deserialization using Symfony Serializer discriminator mapping
- **Email DTOs** (`Dto/Email/`): EmailHtmlMessageRendererResult

#### Services (`src/Service/`)
- **FormDefinitionLoader** - Loads and transforms form configurations from Atoolo resources (FormEditor model → JSON Forms format)
- **SubmitHandler** - Orchestrates processor pipeline execution
- **FormDataModelFactory** - Converts submissions to email message models
- **FormReader** - Traverses form structure to extract field data
- **JsonSchemaValidator** - Extended JSON Schema Draft 2020-12 validation with custom format constraints
- **LabelTranslator** - Handles i18n for form labels

**Email Services** (`src/Service/Email/`):
- **EmailMessageModelFactory** - Builds email data model from form submissions
- **EmailHtmlMessageRenderer** (abstract) - Base email rendering interface
- **EmailHtmlMessageTwigRenderer** - Twig-based email rendering
- **EmailHtmlMessageTwigMjmlRenderer** - MJML-based responsive email rendering
- **CsvGenerator** - Creates CSV attachments from form data
- **MjmlRenderer** - MJML compilation wrapper

**Validation Services** (`src/Service/JsonSchemaValidator/`):
- **FormatConstraint** interface - Custom JSON Schema format validators
- **PhoneConstraint**, **HtmlConstraint**, **DataUrlConstraint** - Format validators
- **Draft202012Extended** - Extended JSON Schema validator

#### Processor Pipeline (`src/Processor/`)

All processors implement `SubmitProcessor` interface:
```php
interface SubmitProcessor {
    public function process(FormSubmission $submission, array $options): FormSubmission;
}
```

**Available Processors** (execution order by priority):
1. **IpLimiter** (80) - Rate limits by IP address (Symfony RateLimiter)
2. **SubmitLimiter** (70) - Global submission rate limiting
3. **SpamDetector** - Spam detection logic
4. **IpAllower** - IP whitelist validation
5. **IpBlocker** - IP blacklist validation
6. **JsonSchemaValidator** - Schema validation
7. **EmailSender** - Email delivery via Symfony Mailer

Processors can set `$submission->approved = true` to skip subsequent processors.

### Data Flow Patterns

**Form Load Flow:**
1. Request → FormController::definition()
2. FormDefinitionLoader loads from Atoolo Resource
3. Deserializes UISchema to typed objects (discriminator mapping)
4. LabelTranslator applies i18n
5. Returns FormDefinition JSON response

**Form Submit Flow:**
1. Request → FormController::submit()
2. Creates FormSubmission with client IP
3. SubmitHandler chains processors (IP checks → rate limits → validation → email)
4. Each processor can approve or reject submission
5. Returns 200 on success, API Problem on failure

**Email Generation:**
1. FormDataModelFactory extracts data from submission
2. EmailMessageModelFactory creates structured email model
3. Renderer (Twig/MJML) generates HTML body
4. CsvGenerator creates optional attachments
5. Symfony Mailer sends email

## Code Quality Standards

### PHPStan Configuration (Level 9)
- Configuration: `phpstan.neon.dist`
- Custom type aliases for complex email model structures
- Cache: `var/cache/phpstan`
- Analyzes: `src/` directory only

### PHP-CS-Fixer (PER-CS Standard)
- Configuration: `.php-cs-fixer.dist.php`
- Rules: `@PER-CS` (PSR-12 compatible modern standard)
- Scans: `src/` and `test/` directories
- Cache: `var/cache/php-cs-fixer`

### PHPUnit Configuration
- Configuration: `phpunit.xml`
- Framework: PHPUnit 10.4+
- Execution: Random order for test independence
- Coverage: Clover XML + HTML reports
- Logging: JUnit XML format
- Memory limit: 512M

### Testing Standards
- **One assertion per test** - Each test method must contain exactly one assertion
- **MANDATORY assertion messages** - Every assertion must include a descriptive message explaining what is being tested
- **Test complete objects** - Use object comparison, not field-by-field assertions
- **Static imports** - All assertion methods must use static imports
- **Test structure**: arrange-act-assert pattern

Example test structure:
```php
use PHPUnit\Framework\TestCase;
use function PHPUnit\Framework\assertEquals;

class ExampleTest extends TestCase {
    #[Test]
    public function loadsFormDefinitionWithAllProperties(): void {
        // arrange
        $loader = new FormDefinitionLoader(/* deps */);
        $location = ResourceLocation::of(/* ... */);

        // act
        $definition = $loader->load($location);

        // assert
        assertEquals(
            $expectedDefinition,
            $definition,
            "Form definition should be loaded with all properties intact including schema, UISchema, and processors"
        );
    }
}
```

## Project-Specific Patterns

### UISchema Polymorphic Deserialization
UISchema elements use Symfony Serializer discriminator mapping:
```php
#[DiscriminatorMap(typeProperty: 'type', mapping: [
    'HorizontalLayout' => HorizontalLayout::class,
    'VerticalLayout' => VerticalLayout::class,
    // ...
])]
abstract class Element { /* ... */ }
```

### Processor Configuration
Default processors are configured in `config/services.yaml`:
```yaml
Atoolo\Form\Service\SubmitHandler:
    arguments:
        $defaultProcessors:
            - '@Atoolo\Form\Processor\IpLimiter'
            - '@Atoolo\Form\Processor\SubmitLimiter'
            - '@Atoolo\Form\Processor\JsonSchemaValidator'
```

Individual forms can override via `FormDefinition::$processors`.

### Immutable DTOs
All DTOs use `readonly` properties with constructor injection:
```php
readonly class FormDefinition {
    public function __construct(
        public JsonSchema $schema,
        public Element $uiSchema,
        public FormMessages $messages,
        // ...
    ) {}
}
```

### Exception to API Problem Transformation
Custom exceptions are transformed to RFC 9457 API Problems via `ExceptionTransformer`:
- `FormNotFoundException` → 404 Not Found
- `AccessDeniedException` → 403 Forbidden
- `LimitExceededException` → 429 Too Many Requests
- `SpamDetectedException` → 422 Unprocessable Entity

### Custom JSON Schema Format Constraints
Extend `FormatConstraint` interface for custom format validators:
```php
class PhoneConstraint implements FormatConstraint {
    public function validate(mixed $value): ?ValidationError {
        // Phone number validation logic
    }
}
```

Register in service configuration with tag:
```yaml
Atoolo\Form\Service\JsonSchemaValidator\FormatConstraint\PhoneConstraint:
    tags:
        - { name: 'atoolo_form.format_constraint', format: 'phone' }
```

## Important File Locations

| Path | Purpose |
|------|---------|
| `src/AtooloFormBundle.php` | Bundle entry point |
| `src/Controller/FormController.php` | REST API endpoints |
| `src/Service/FormDefinitionLoader.php` | Form configuration loading |
| `src/Service/SubmitHandler.php` | Processor pipeline orchestration |
| `src/Processor/SubmitProcessor.php` | Processor interface |
| `config/services.yaml` | Service registration & default processors |
| `config/routes.yaml` | API route definitions |
| `templates/email.text.twig` | Email body template |
| `templates/email.text.summary.twig` | Email summary template |

## Dependencies

**Core Symfony Bundles:**
- symfony/framework-bundle ^7.1
- symfony/mailer ^7.1
- symfony/rate-limiter ^7.1
- symfony/serializer ^7.1
- symfony/validator ^7.1

**Domain-Specific:**
- atoolo/resource-bundle ^1.3 - Atoolo resource loading
- opis/json-schema ^2.3 - JSON Schema validation
- phpro/api-problem-bundle ^1.7 - RFC 9457 API Problems

**Email/Document:**
- twig/markdown-extra ^3.21 - Markdown support
- league/csv ^9.16 - CSV generation
- league/html-to-markdown ^5.1 - HTML to Markdown conversion

## Configuration Files

- `composer.json` - Dependencies, scripts, autoloading
- `phpunit.xml` - PHPUnit configuration
- `phpstan.neon.dist` - PHPStan Level 9 configuration with custom type aliases
- `.php-cs-fixer.dist.php` - PER-CS code style rules
- `phpcs.compatibilitycheck.xml` - PHP compatibility checking (8.1-8.4)
- `config/services.yaml` - Symfony service container configuration
- `config/routes.yaml` - API route definitions
- `config/rate_limiter.yaml` - Rate limiter configuration

## Tools (Managed via Phive)

PHAR tools are symlinked in `tools/` directory:
- `tools/phpunit.phar` - PHPUnit test runner (included directly)
- `tools/phpstan` → `~/.phive/phars/phpstan-*.phar`
- `tools/php-cs-fixer` → `~/.phive/phars/php-cs-fixer-*.phar`
- `tools/phplint` → `~/.phive/phars/overtrue/phplint-*.phar`
- `tools/composer-normalize` → `~/.phive/phars/composer-normalize-*.phar`

All tools are version-locked and installed via Phive (PHP Version Manager) for reproducible builds.
