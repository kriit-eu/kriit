# CLAUDE.md: Guidelines for Code Agents

## Project Description

Kriit is a student's homework and grades management system for the Viljandi Kutseõppekeskus (VIKK) in Estonia. It integrates with external systems (only [Tahvel](https://tahvel.edu.ee) for now): Kriit will receive POST /api/subjects/getDifferences requests from external systems, makes sure it has all the mentioned subjects, groups, teachers, and assignments (inserts them if not), and will report if Kriit had any values different compared to  sent data.

The primary benefit of Kriit vs using the external system directly is that teachers can see all grades for all students in all subjects in one page within Kriit, quickly identifying:
- Students who haven't submitted assignments
- Students with missed assignments
- Submitted assignments awaiting grading

Teachers can also grade assignments in Kriit.

## Build & Development Commands
- Install dependencies: `composer install && npm install`
- Database linting: `php database_linter.php`

## Environment Setup
- Local development requires PHP 8.3+, MariaDB
- No formal build process, files are served directly

## Architecture
- Custom MVC framework:
    - Controllers handle requests (`controllers/{controllerName}.php`)
    - Templates set the general layout (`templates/{layoutName}_template.php`)
      - Main templates: `templates/{templateName}_template.php`
      - Common templates include `auth_template.php`, `master_template.php`, and `admin_template.php`
      - Partial templates in `templates/partials/`
  - Views render template content (`views/{controllerName}/{controllerName}_{actionName}.php`)
  - Service classes have static methods in `classes/App/{serviceName}.php` to group related functions
  - Automatic routing for controllers (e.g., `/users` maps to `controllers/users.php` to function `index()` and `/users/1` maps to function `view()`
  - Default controller set in constants.php

## Configuration Files
- `/config.php` for global configuration (e.g., database and email connection details)
- `/constants.php` for global constants and enumerations for more semantic code

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
- **CSS**: Bootstrap framework
- **JavaScript**:
    - Prefer Vue.js for interactive components

## Error Handling
- Development errors show detailed information
- Production errors log to Sentry
- Throw exceptions for validation errors
- Follow exception-based error handling pattern

## Documentation
- All classes should have header comments with a short description
- Complex operations should include inline comments
- All methods with parameters should document parameter types

## Best Practices
- Use the static methods in classes/App/Db.php to interact with the database
- Use prepared statements for all database queries
- Implement type hints and return types
- Use the database linter to verify schema integrity