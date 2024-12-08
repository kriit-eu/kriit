# Database Linting Script

This PHP script performs a linting process on a MySQL/MariaDB database to ensure proper indexing and relationships for fields following the `Id` naming convention. It validates the database schema and generates SQL fixes for any issues it identifies.

## Features

- **Id Column Validation**: Checks columns ending with `Id` for appropriate primary key (PK), foreign key (FK), or unique index constraints.
- **Irregular Plural Support**: Handles special cases where the table name does not follow standard pluralization rules.
- **Whitelist Handling**: Skips checks for specified fields that are intentionally excluded or handled dynamically.
- **SQL Fix Generation**: Outputs SQL commands to address missing constraints or indexes.

## Prerequisites

- PHP installed on the server.
- A MySQL/MariaDB database with the schema to be validated.
- A `config.php` file with the following constants:
  - `DATABASE_HOSTNAME`: Hostname of the database server.
  - `DATABASE_DATABASE`: Name of the database.
  - `DATABASE_USERNAME`: Database username.
  - `DATABASE_PASSWORD`: Database password.

## Usage

Run the script:
```bash
php database_linter.php
```

## Output

- **Validation Messages**: The script prints messages indicating which columns are missing constraints or indexes.
- **SQL Fix Commands**: Outputs SQL commands to resolve the identified issues.

## Configuration

- **Irregular Plurals**: Add any irregular plural mappings in the `$irregular_plurals` array.
- **Whitelisted Fields**: Add fields that should be excluded from FK checks to the `$whitelisted_fields` array.

## Example

Given the following database schema:
```sql
CREATE TABLE users (
    userId INT PRIMARY KEY,
    name VARCHAR(100)
);

CREATE TABLE orders (
    orderId INT PRIMARY KEY,
    userId INT
);
```

If `orders.userId` is missing a foreign key constraint, the script will output:
```
orders.userId is missing FK
SQL commands to fix the issues:

ALTER TABLE `orders` ADD CONSTRAINT `fk_orders_userId` FOREIGN KEY (`userId`) REFERENCES `users`(`userId`);
```