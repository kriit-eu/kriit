# CLAUDE.md

## Quick Start
```bash
composer install && npm install   # Install dependencies
bun start                        # Start containers (ports: 8000-8003)
bun db:import                    # Load database from doc/database.sql
```

## What This Is
Grades management system for Estonian vocational school (VIKK). Syncs with Tahvel via POST `/api/subjects/getDifferences`. PHP 8.3 custom MVC, MariaDB, Bootstrap 5, Bun/Docker.

## Architecture
```
/
├── index.php                    # Entry point, routes to controllers
├── controllers/                 # URL → controller/action/params
│   ├── subjects.php            # Main teacher view
│   ├── grading.php             # Grade assignments
│   └── api/                    # External integration endpoints
│       └── subjects.php        # getDifferences() sync endpoint
├── classes/App/                 # Static service classes
│   ├── Db.php                  # Database singleton (getAll, insert, etc)
│   ├── Sync.php                # External system sync logic
│   └── Activity.php            # Audit logging (30 activity types)
├── views/{controller}/          # {controller}_{action}.php format
├── templates/                   # Layout wrappers
└── doc/database.sql            # Schema dump
```

## Key Concepts

### Database Access
```php
// All queries through Db class
$users = Db::getAll("SELECT * FROM users WHERE userRole = ?", ['teacher']);
$userId = Db::insert('users', ['userName' => 'John', 'userEmail' => 'j@e.com']);
Db::update('assignments', ['assignmentName' => 'New'], "assignmentId = ?", [123]);
```

### Routing & Controllers
```php
// URL /subjects/view/123 → controllers/subjects.php::view(123)
class subjects extends Controller {
    function index() { /* List view */ }
    function view($id) { /* Single item */ }
}
```

### External Sync
```php
// Tahvel sends POST /api/subjects/getDifferences with JSON:
{
  "subjects": [{
    "subjectExternalId": "123",
    "subjectName": "Math",
    "assignments": [...]
  }]
}
// Kriit responds with differences after syncing
```

## Common Tasks

### Add New Controller
```php
// controllers/mynew.php
class mynew extends Controller {
    function index() {
        $this->data = Db::getAll("SELECT * FROM mytable");
    }
}
// Create views/mynew/mynew_index.php
```

### Database Operations
```php
// Insert with auto-increment ID
$id = Db::insert('assignments', [
    'assignmentSubjectId' => 123,
    'assignmentName' => 'Homework 1',
    'assignmentDueDate' => date('Y-m-d')
]);

// Upsert (insert or update)
Db::upsert('grades', [
    'gradeAssignmentId' => $assignmentId,
    'gradeUserId' => $userId,
    'gradeValue' => 5
]);

// Transaction
Db::beginTransaction();
try {
    Db::insert('subjects', [...]);
    Db::insert('assignments', [...]);
    Db::commit();
} catch (Exception $e) {
    Db::rollback();
}
```

### Activity Logging
```php
Activity::create(ACTIVITY_TEACHER_GRADE_ASSIGNMENT, $assignmentId, $userId, [
    'oldGrade' => 3,
    'newGrade' => 5
]);
```

### Authentication Check
```php
if (!Auth::isTeacher()) stop(403);
if (Auth::getUserId() != $ownerId) stop(403);
```

## Important Details

### Database Naming
- Tables: lowercase plural (`users`, `assignments`)
- Fields: camelCase prefixed (`userId`, `assignmentName`)
- PKs: {table}Id (auto_increment)
- FKs: must have constraints (except *externalId fields)

### Key Constants
```php
// Activity types (constants.php)
ACTIVITY_SYNC_START = 18
ACTIVITY_TEACHER_GRADE_ASSIGNMENT = 26

// Validation types
IS_ID = 1, IS_INT = 2, IS_STRING = 5
```

### API Endpoints
- POST `/api/subjects/getDifferences?systemId=1` - Tahvel sync
- GET `/api/users` - List users
- POST `/api/assignments` - Create assignment

### Quick Commands
```bash
bun logs:app                    # PHP error logs
bun shell                       # Enter PHP container
bun db:export                   # Backup database
php database_linter.php         # Validate schema
```

## Current Context
Branch: main
Recent work: Learning outcomes system (new `outcomes` table), Markdown editor integration, Estonian char support in branches