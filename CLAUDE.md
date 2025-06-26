# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Description

Kriit is a student's homework and grades management system for the Viljandi Kutseõppekeskus (VIKK) in Estonia. It integrates with external systems (only [Tahvel](https://tahvel.edu.ee) for now): Kriit will receive POST /api/subjects/getDifferences requests from external systems, makes sure it has all the mentioned subjects, groups, teachers, and assignments (inserts them if not), and will report if Kriit had any values different compared to  sent data.

The primary benefit of Kriit vs using the external system directly is that teachers can see all grades for all students in all subjects in one page within Kriit, quickly identifying:
- Students who haven't submitted assignments
- Students with missed assignments
- Submitted assignments awaiting grading

Teachers can also grade assignments in Kriit.

## Build & Development Commands
- Install dependencies: `composer install && npm install`
- Start development environment: `bun start` (starts Docker/Podman containers)  
- Stop environment: `bun stop`
- View logs: `bun logs` or `bun logs:app`/`bun logs:db`/`bun logs:nginx`
- Access shell: `bun shell` (PHP container), `bun shell:db` (MariaDB container)
- Database operations: `bun db:import` (restore from doc/database.sql), `bun db:export` (dump to doc/database.sql)
- Database linting: `php database_linter.php` (validates schema integrity and naming conventions)
- Run tests: `bun test` (JavaScript tests using Bun test runner)

## Environment Setup
- **Container Runtime**: Docker or Podman (automatic detection via `docker/podman.override.yml`)
- **Package Manager**: Bun (Node.js alternative)
- **PHP Version**: 8.3+
- **Database**: MariaDB (via containers)
- **Services**: Nginx (8080), phpMyAdmin (8081), MailHog (8025), MariaDB (8006)
- No formal build process - files served directly through Docker/Podman setup

## Architecture

### Custom MVC Framework
- **Entry Point**: `/index.php` handles CORS, bootstraps application through `Application` class
- **Routing**: Automatic URL-based routing (`/controller/action/params`) with custom routing rules
- **Controllers**: Located in `/controllers/` directory, extend base `Controller` class
  - API controllers in `/controllers/api/` for external system integration
  - Automatic routing: `/users` → `controllers/users.php::index()`, `/users/1` → `controllers/users.php::view()`
- **Views**: In `/views/{controller}/{controller}_{action}.php` format
- **Templates**: Layout templates in `/templates/` with partials in `/templates/partials/`
  - Common templates: `auth_template.php`, `master_template.php`, `admin_template.php`
- **Service Classes**: Static methods in `classes/App/{serviceName}.php` for business logic grouping
  - Key services: `Activity`, `Assignment`, `User`, `Sync`, `Mail`, `Translation`

### Database Layer
- **Main Database Class**: `/classes/App/Db.php` - Singleton pattern with prepared statements
- **Key Methods**: `getAll()`, `getFirst()`, `getOne()`, `insert()`, `update()`, `delete()`, `upsert()`
- **Features**: SQL debugging, transaction support, query logging, performance monitoring
- **Schema**: MariaDB with UTF8MB4, camelCase field names prefixed with table names

### External Integration
- **Tahvel API**: `/api/subjects/getDifferences` endpoint for bi-directional sync
- **Data Validation**: Comprehensive validation of external data before processing
- **Activity Logging**: All sync operations logged for audit trail

## Configuration Files
- `/config.php` - Global configuration (database, email, environment settings)
- `/constants.php` - Application constants, activity types, validation rules
- `composer.json` - PHP dependencies and PSR-4 autoloading
- `package.json` - JavaScript dependencies and Bun scripts  
- `docker/podman.override.yml` - Automatic Podman configuration for rootless containers

## Code Style Guidelines

### Naming Conventions
- **Classes**: PascalCase (e.g., service classes in `classes/App/`)
- **Methods/variables**: camelCase
- **Constants**: UPPERCASE_WITH_UNDERSCORES
- **View files**: lowercase with underscores

### Database Conventions
- Table names: lowercase, plural form
- Field names: camelCase prefixed with table name (singular)
- Primary keys: tableSingularId (auto increment)
- All columns ending with `Id` must have appropriate FK/PK constraints (except those that contain the word `external`)
- Use UTF8MB4 charset for all tables
- In SQL queries, prefer USING() over ON when field names match

### Formatting
- 4-space indentation (no tabs)
- Opening brace on same line
- Single line between methods

### PHP Standards
- Use static methods for utility functions
- Include type hints on parameters/returns
- Follow PSR-4 autoloading standard

## Frontend
- **CSS Framework**: Bootstrap 5 with custom overrides
- **JavaScript**: Vanilla JS with selective Vue.js components
- **Assets**: FontAwesome icons, CodeMirror editor, jQuery
- **Build**: No build process - assets served directly
- **Responsive**: Mobile-first design approach

## Error Handling
- Development errors show detailed information
- Production errors log to Sentry
- Throw exceptions for validation errors
- Follow exception-based error handling pattern

## Documentation
- All classes should have header comments with a short description
- Complex operations should include inline comments
- All methods with parameters should document parameter types

## Key Development Patterns
- **Database Operations**: Use static methods in `classes/App/Db.php` for all database interactions
- **SQL Queries**: Use prepared statements exclusively, prefer `USING()` over `ON` when field names match
- **Activity Logging**: Log all significant actions using `Activity` service class (27+ activity types)
- **Authentication**: Session-based auth via `Auth` class with role-based access control
- **Error Handling**: Exception-based pattern with environment-specific error display
- **Multi-language**: Dynamic language switching with translation extraction system

## Testing & Quality Assurance
- **Database Linting**: `php database_linter.php` validates schema integrity and naming conventions
- **JavaScript Testing**: Bun test runner with comprehensive AVIF support test suite
- **Pre-commit Hooks**: Husky integration for branch naming validation
- **SQL Debugging**: Built-in query logging and performance monitoring for admins