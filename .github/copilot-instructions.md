# Copilot Instructions for Kriit

## Architecture Overview

- **Custom MVC Framework**: Entry point is `index.php` → `system/classes/Application.php`.
- **Controllers**: In `controllers/`, RESTful API controllers in `controllers/api/` (see `CLAUDE.md`).
- **Views**: `views/{controller}/{controller}_{action}.php`.
- **Templates**: Layouts in `templates/`, partials in `templates/partials/`.
- **Service Classes**: Business logic in `classes/App/{Service}.php` (e.g., `Db`, `Activity`, `User`).
- **Frontend**: Bootstrap 5, vanilla JS, selective Vue.js, CodeMirror, FontAwesome. No build step; assets served directly.

## Containerized Workflow

- **All development is containerized** (Docker/Podman auto-detected).
- **Start/stop**: `bun start`, `bun stop` (see `package.json` scripts).
- **Logs**: `bun logs[:service]` (e.g., `bun logs:app`).
- **Shell access**: `bun shell` (PHP), `bun shell:db` (MariaDB).
- **Database import/export**: `bun db:import`, `bun db:export` (syncs with `doc/database.sql`).
- **Composer**: `bun composer install` (runs in container).
- **Setup**: `bun setup` auto-detects platform and prepares containers.

## Database Conventions
- **MariaDB**
  1. Within Docker: Host `db`, user `root`, password `kriitkriit`, DB `kriit`.
  2. From host: mysql -h 127.0.0.1 -P 8002 -u root -pkriitkriit kriit -e "Select 1"
- **Schema changes**: Always update `doc/database.sql` using:
  1. `refreshdb.php --restore` (reset DB)
  2. Apply schema changes via `mysql` in container
  3. `php database_linter.php` (lint/fix dump)
  4. `refreshdb.php --dump` (update dump)
  5. Commit with message "Update database schema"
- **All DB access**: Use static methods in `classes/App/Db.php` (prepared statements, no raw queries).
- **Naming**: Table names plural, fields camelCase prefixed with table name, PKs as `tableSingularId`.

## Testing & Quality

- **JS tests**: `bun test` (see `tests/avif_support.test.js`).
- **Database linting**: `php database_linter.php` (schema/naming validation).
- **Syntax checks**: `docker exec kriit-app-1 php -l /var/www/html/path/to/file.php`.
- **Module testing**: See `changes/TEACHER_NOTES_MODULE.md` for browser and functional test patterns.

## Project-Specific Conventions

- **No CDN dependencies**: All assets are local (see `changes/OFFLINE_CONVERSION_DOCUMENTATION.md`).
- **Translation**: Dynamic, with extraction system (`Translation` service).
- **Error handling**: Exception-based, Sentry in production, local logs in dev.
- **Activity logging**: Use `Activity` service for all significant actions.
- **API integration**: `/controllers/api/` for external sync (e.g., Tahvel API).
- **Commit hash**: Exposed as `COMMIT_HASH` for cache-busting assets.

## Troubleshooting & Debugging

- **Check container status**: `bun logs`, `docker compose ps`
- **Rebuild images**: `bun force-rebuild` or `bun clean-build`
- **AVIF support**: See `enable_avif_commands.md` for verification and rebuild steps.
- **Setup issues**: Run `bun setup` to fix missing dependencies or container problems.

## Key Files & Docs

- `README.md`, `CLAUDE.md`: High-level project and workflow info
- `scripts/`: Bun/Node scripts for setup, build, and orchestration
- `changes/`: Module and migration docs (e.g., offline mode, teacher notes)
- `.windsurf/rules/code-style-guide.md`: DB and code style rules

---

**Example: Add a DB column**

1. Update schema in running DB (via `bun shell:db`)
2. Run `php database_linter.php`
3. Run `refreshdb.php --dump`
4. Commit `doc/database.sql` and code changes

**Example: Add a controller**

- Place in `controllers/`, follow naming and routing conventions (see `CLAUDE.md`)

---

For more, see `README.md`, `CLAUDE.md`, and module docs in `changes/`.

---