---
trigger: always_on
---

- Any time a database interaction is required, use the following command: mysql -uroot -proot -h 127.0.0.1 -e "your sql command"
- Any time database needs to be changed, database dump at doc/database.sql must be updated with the following procedure:
    - Run `git checkout doc/database.sql` to revert any temporary changes to the dump (for example if user has overwritten it with live database copy to test live database on localhost)
    - Run `refreshdb.php --restore` to revert any temporary data in MariaDB
    - Run `mysql -u root -proot -h 127.0.0.1 -e "your sql command"` to update the database schema (or add new rows to enumeration tables)
    - Run `php database_linter.php` to verify the dump is correct and fix any issues
    - Run `refreshdb.php --dump` to update the database dump with the current schema
    - Commit the changes to git with "Update database schema" commit message
- Code formatting standards (4-space indentation, single line between methods)