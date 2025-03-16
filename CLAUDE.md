# CLAUDE.md: Guidelines for Code Agents

## Project Description

Kriit is a student's homework and grades management system for the Viljandi Kutseõppekeskus (VIKK) in Estonia. It integrates with the [Tahvel](https://tahvel.edu.ee) system via a Chrome extension called 'Õpetaja Assistent' which runs on the tahvel.edu.ee website. The extension communicates with Kriit via ReST API to synchronize subjects, students, assignments, and grades.

The primary benefit is that teachers can see all grades for all students in all subjects in one page within Kriit, quickly identifying:
- Students who haven't submitted assignments
- Students with missed assignments
- Submitted assignments awaiting grading

Teachers can grade assignments in Kriit, and Õpetaja Assistent will synchronize these grades with Tahvel when the teacher visits the tahvel.edu.ee website.

## Build & Development Commands
- Install dependencies: `composer install && npm install`
- Database linting: `php database_linter.php`

## Environment Setup
- Local development requires PHP 8.3+, MariaDB
- No formal build process, files are served directly

## Architecture
- Custom MVC framework:
    - Controllers handle requests (`controllers/{controllerName}.php`)
    - Views render templates (`views/{controllerName}/{controllerName}_{actionName}.php`)
    - Models use classes with static methods in `classes/App/` to group related functions
    - Automatic routing for controllers (e.g., `/users` maps to `controllers/users.php`)
    - Default controller set in constants.php
    - Default action is index

## Configuration Files
- `/config.php` for global configuration (e.g., database connection details)
- `/constants.php` for global constants and enumerations for more semantic code

## Templates
- Main templates: `views/_templates/{templateName}_template.php`
- Common templates include `auth_template.php`, `master_template.php`, and `admin_template.php`
- Partial templates in `templates/partials/`

## Code Style Guidelines

### Naming Conventions
- **Classes**: PascalCase (e.g., model classes in `models/`)
- **Methods/variables**: camelCase
- **Constants**: UPPERCASE_WITH_UNDERSCORES
- **View files**: lowercase with underscores

### Database Conventions
- Table names: lowercase, plural form
- Field names: camelCase prefixed with table name (singular)
- Primary keys: tableSingularId (auto increment)
- All columns ending with `Id` must have appropriate FK/PK constraints
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
    - Prefer VueJS for interactive components
    - jQuery for DOM manipulation when not using Vue

## Error Handling
- Development errors show detailed information
- Production errors log to Sentry
- Throw exceptions for validation errors
- Use try/catch blocks for database operations
- Follow exception-based error handling pattern

## Documentation
- All classes should have header comments with author and date
- Complex operations should include inline comments
- All methods with parameters should document parameter types

## Best Practices
- Use prepared statements for all database queries
- Implement type hints and return types
- Use the database linter to verify schema integrity