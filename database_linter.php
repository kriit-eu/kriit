<?php
require_once 'config.php';

// Define irregular plurals
$irregular_plurals = [
    'criteria' => 'criterion'
];

// Define whitelisted fields that don't need FK checks
$whitelisted_fields = [
    // External system references
    'subjects.subjectExternalId',
    'users.userExternalId',
    'assignments.assignmentExternalId',
    // Special cases with dynamic references
    'activityLog.id'
];

// Connect to database
try {
    $pdo = new PDO(
        "mysql:host=" . DATABASE_HOSTNAME . ";dbname=" . DATABASE_DATABASE,
        DATABASE_USERNAME,
        DATABASE_PASSWORD,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

// Get all tables and their columns ending with Id
$tables_query = "
    SELECT 
        TABLE_NAME,
        COLUMN_NAME
    FROM INFORMATION_SCHEMA.COLUMNS
    WHERE 
        TABLE_SCHEMA = :database
        AND COLUMN_NAME LIKE '%Id'
";

$tables_stmt = $pdo->prepare($tables_query);
$tables_stmt->execute(['database' => DATABASE_DATABASE]);
$id_columns = $tables_stmt->fetchAll(PDO::FETCH_ASSOC);

// Get all constraints and indexes
$constraints_query = "
    SELECT 
        TABLE_NAME,
        COLUMN_NAME,
        CONSTRAINT_TYPE,
        CONSTRAINT_NAME
    FROM (
        SELECT 
            k.TABLE_NAME,
            k.COLUMN_NAME,
            t.CONSTRAINT_TYPE,
            k.CONSTRAINT_NAME
        FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE k
        JOIN INFORMATION_SCHEMA.TABLE_CONSTRAINTS t
            ON k.CONSTRAINT_NAME = t.CONSTRAINT_NAME
            AND k.TABLE_SCHEMA = t.TABLE_SCHEMA
        WHERE 
            k.TABLE_SCHEMA = :database
            AND t.CONSTRAINT_TYPE IN ('PRIMARY KEY', 'FOREIGN KEY', 'UNIQUE')
            AND k.COLUMN_NAME LIKE '%Id'
        UNION ALL
        SELECT 
            TABLE_NAME,
            COLUMN_NAME,
            IF(NON_UNIQUE = 0, 'UNIQUE INDEX', 'INDEX') as CONSTRAINT_TYPE,
            INDEX_NAME as CONSTRAINT_NAME
        FROM INFORMATION_SCHEMA.STATISTICS
        WHERE 
            TABLE_SCHEMA = :database
            AND COLUMN_NAME LIKE '%Id'
    ) all_constraints
    GROUP BY TABLE_NAME, COLUMN_NAME, CONSTRAINT_TYPE, CONSTRAINT_NAME
";

$constraints_stmt = $pdo->prepare($constraints_query);
$constraints_stmt->execute(['database' => DATABASE_DATABASE]);
$constraints = $constraints_stmt->fetchAll(PDO::FETCH_ASSOC);

// Build a lookup array for constraints and indexes
$constraint_lookup = [];
foreach ($constraints as $constraint) {
    $key = $constraint['TABLE_NAME'] . '.' . $constraint['COLUMN_NAME'];
    if (!isset($constraint_lookup[$key])) {
        $constraint_lookup[$key] = [];
    }
    $constraint_lookup[$key][] = $constraint['CONSTRAINT_TYPE'];
}

// Array to store SQL fixes
$sql_fixes = [];

// Check each Id column against the naming convention and constraints
foreach ($id_columns as $column) {
    $table_name = $column['TABLE_NAME'];
    $column_name = $column['COLUMN_NAME'];
    $full_column_name = $table_name . '.' . $column_name;

    // Get expected PK name using irregular plural if exists, otherwise remove trailing 's'
    if (isset($irregular_plurals[$table_name])) {
        $expected_pk = $irregular_plurals[$table_name] . 'Id';
    } else {
        $expected_pk = preg_replace('/s$/', 'Id', $table_name);
    }

    // Handle whitelisted fields differently
    if (in_array($full_column_name, $whitelisted_fields)) {
        if (!isset($constraint_lookup[$full_column_name])) {
            echo "$full_column_name is missing an index (needs at least an INDEX, UNIQUE INDEX, or FK)\n";
            $sql_fixes[] = "ALTER TABLE `$table_name` ADD INDEX `idx_{$table_name}_{$column_name}` (`$column_name`);";
        }
        continue;
    }

    // Check if this column should be a PK or FK
    $should_be_pk = ($column_name === $expected_pk);
    $has_pk = isset($constraint_lookup[$full_column_name]) &&
        in_array('PRIMARY KEY', $constraint_lookup[$full_column_name]);

    if ($should_be_pk && !$has_pk) {
        echo "$full_column_name is missing PK\n";
        $sql_fixes[] = "ALTER TABLE `$table_name` ADD PRIMARY KEY (`$column_name`);";
    } elseif (!$has_pk) { // Only check for FK if it's not a PK
        $has_fk = isset($constraint_lookup[$full_column_name]) &&
            (in_array('FOREIGN KEY', $constraint_lookup[$full_column_name]) ||
                in_array('UNIQUE INDEX', $constraint_lookup[$full_column_name]));
        if (!$has_fk) {
            echo "$full_column_name is missing FK\n";

            // Try to determine the referenced table
            $referenced_table = '';
            if (strpos($column_name, 'userId') !== false) {
                $referenced_table = 'users.userId';
            } elseif (strpos($column_name, 'assignmentId') !== false) {
                $referenced_table = 'assignments.assignmentId';
            } elseif (strpos($column_name, 'activityId') !== false) {
                $referenced_table = 'activities.activityId';
            }

            if ($referenced_table) {
                list($ref_table, $ref_column) = explode('.', $referenced_table);
                $sql_fixes[] = "ALTER TABLE `$table_name` ADD CONSTRAINT `fk_{$table_name}_{$column_name}` " .
                    "FOREIGN KEY (`$column_name`) REFERENCES `$ref_table`(`$ref_column`);";
            }
        }
    }
}

if (!empty($sql_fixes)) {
    echo "\nSQL commands to fix the issues:\n\n";
    foreach ($sql_fixes as $sql) {
        echo $sql . "\n";
    }
}