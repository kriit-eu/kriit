#!/usr/bin/env php
<?php

//-----------------------------
// Configuration
//-----------------------------
$config = [
    'databaseUsername' => 'root',
    'databasePassword' => 'kriitkriit',
    'databaseHostname' => 'db',
    'databasePort' => '8002',
    'databaseName' => '',
    'configFilePaths' => ['./config.php', './wp-config.php'],
    'dumpFilePath' => 'doc/database.sql',
    'tempFilePath' => 'doc/database.sql.tmp',
    'mysqlExecutablePath' => 'mariadb',
    'mysqldumpExecutablePath' => 'mariadb-dump',
    'maxLineLength' => 120,
];

//-----------------------------
// Time & Timezone Setup
//-----------------------------
$start = microtime(true);
date_default_timezone_set('UTC');
log_message("Started at: " . date('Y-m-d H:i:s'));

try {
    $timezone = json_decode(file_get_contents('http://ip-api.com/json'))->timezone;
    date_default_timezone_set($timezone);
} catch (Exception $e) {
    // Keep UTC if fetching timezone fails
}

//-----------------------------
// Determine Mode
//-----------------------------
$options = getopt('', ['dump', 'restore', 'repair', 'dump-schema']);
$mode = isset($options['dump'])
    ? 'dump'
    : (isset($options['restore'])
        ? 'restore'
        : (isset($options['repair'])
            ? 'repair'
            : (isset($options['dump-schema'])
                ? 'dump-schema'
                : 'help')));

if ($mode === 'help') {
    showHelp();
    exit(0);
}

//-----------------------------
// Validate Dump File for Restore/Repair
//-----------------------------
if ($mode === 'restore' || $mode === 'repair') {
    if (!file_exists($config['dumpFilePath'])) {
        log_message("No dump file found at {$config['dumpFilePath']}. "
            . "Please create a dump file before restoring or repairing.");
        exit(1);
    }
    if (filesize($config['dumpFilePath']) === 0) {
        log_message("The dump file is empty. "
            . "Please create a non-empty dump file before restoring or repairing.");
        exit(1);
    }
}

//-----------------------------
// Read Database Credentials
//-----------------------------
readDatabaseCredentials($config);
log_message("Database name: {$config['databaseName']}");

//-----------------------------
// Execute Mode
//-----------------------------
if ($mode === 'dump') {
    dumpDatabase($config);
} elseif ($mode === 'restore') {
    restoreDatabase($config);
} elseif ($mode === 'repair') {
    repairDumpFile($config);
} elseif ($mode === 'dump-schema') {
    dumpSchema($config);
}

//-----------------------------
// Print Time Taken
//-----------------------------
$elapsed = microtime(true) - $start;
log_message("Time taken: "
    . ($elapsed < 1 ? round($elapsed * 1000) . " ms" : round($elapsed, 2) . " s"));


//====================================================
// FUNCTIONS
//====================================================

/**
 * Dump the Database (structure + data), then normalize it.
 */
function dumpDatabase(array $config): void
{
    $passwordOption = empty($config['databasePassword']) ? "" : "-p" . escapeshellarg($config['databasePassword']);

    if (executeCommand(sprintf(
        '%s --default-character-set=utf8mb4 -u %s %s -h %s -P %s %s > %s',
        escapeshellcmd($config['mysqldumpExecutablePath']),
        escapeshellarg($config['databaseUsername']),
        $passwordOption,
        escapeshellarg($config['databaseHostname']),
        escapeshellarg($config['databasePort']),
        escapeshellarg($config['databaseName']),
        escapeshellarg($config['dumpFilePath'])
    ))) {
        // Run our normal line-by-line transformations
        normalizeDumpFile($config);
        logFileStats($config['dumpFilePath']);
    } else {
        log_message("Failed to create dump file.");
    }
}

/**
 * Dump only the schema (no data) in a stripped-down form, printed to stdout.
 * We reuse existing normalization but skip all data logic.
 */
function dumpSchema(array $config): void
{
    log_message("Dumping minimal schema to stdout ...");

    $passwordOption = empty($config['databasePassword']) ? "" : "-p" . escapeshellarg($config['databasePassword']);
    $command = sprintf(
        '%s --no-data --skip-comments -u %s %s -h %s -P %s %s > %s',
        escapeshellcmd($config['mysqldumpExecutablePath']),
        escapeshellarg($config['databaseUsername']),
        $passwordOption,
        escapeshellarg($config['databaseHostname']),
        escapeshellarg($config['databasePort']),
        escapeshellarg($config['databaseName']),
        escapeshellarg($config['dumpFilePath'])
    );

    // Generate a no-data dump
    if (!executeCommand($command)) {
        log_message("Failed to create schema dump (no-data).");
        exit(1);
    }

    // Now reuse our normalizing logic, but in "schemaOnly" mode
    normalizeDumpFile($config, /* schemaOnly = */ true);

    // Finally, read the resulting file and echo it to stdout
    if (file_exists($config['dumpFilePath'])) {
        echo file_get_contents($config['dumpFilePath']);
    }
    // We're done. No need to keep that file if you don't want to.
    exit(0);
}

/**
 * Repair an existing dump file (e.g., convert from UTF16 to UTF8) and then normalize.
 */
function repairDumpFile(array $config): void
{
    log_message("Repairing dump file: {$config['dumpFilePath']}");

    if (isUtf16($config['dumpFilePath'])) {
        log_message("Detected UTF16LE encoding, will convert to UTF8");

        // Try using system iconv command for converting
        if (executeCommand("iconv -f UTF-16LE -t UTF-8 \"{$config['dumpFilePath']}\" > \"{$config['tempFilePath']}\"")) {
            rename($config['tempFilePath'], $config['dumpFilePath']);
            log_message("Successfully converted file using iconv");
        } else {
            log_message("Failed to convert with iconv, use some tool to convert the file from UTF16LE to UTF8");
            exit(1);
        }
    }
    normalizeDumpFile($config);
    logFileStats($config['dumpFilePath']);
}

/**
 * Restore the Database (drops & recreates it, then loads from dump)
 */
function restoreDatabase(array $config): void
{
    $passwordOption = empty($config['databasePassword']) ? "" : "-p" . escapeshellarg($config['databasePassword']);

    // Drop & create
    if (!executeCommand(sprintf(
        "%s -u %s %s -h %s -P %s -e \"DROP DATABASE IF EXISTS \`%s\`; CREATE DATABASE \`%s\`; SET @@SESSION.sql_mode='NO_AUTO_VALUE_ON_ZERO';\"",
        escapeshellcmd($config['mysqlExecutablePath']),
        escapeshellarg($config['databaseUsername']),
        $passwordOption,
        escapeshellarg($config['databaseHostname']),
        escapeshellarg($config['databasePort']),
        $config['databaseName'],
        $config['databaseName']
    ))) {
        log_message("Failed to drop/create the database.");
        return;
    }

    // Check if the dump file might be in UTF16LE format
    if (isUtf16($config['dumpFilePath'])) {
        log_message("Detected UTF16LE encoding in the dump file, converting to UTF8 for import...");
        log_message("Please use the --repair option to convert the dump file to UTF8 first.");
        exit(1);
    }

    if (executeCommand(sprintf(
        '%s --binary-mode -u %s %s -h %s -P %s %s < %s',
        escapeshellcmd($config['mysqlExecutablePath']),
        escapeshellarg($config['databaseUsername']),
        $passwordOption,
        escapeshellarg($config['databaseHostname']),
        escapeshellarg($config['databasePort']),
        escapeshellarg($config['databaseName']),
        escapeshellarg($config['dumpFilePath'])
    ))) {
        log_message("Database restored successfully.");
    } else {
        log_message("Failed to restore the database.");
    }
}

/**
 * Read Database Credentials from config files
 */
function readDatabaseCredentials(array &$config): void
{
    // First, include constants.php if it exists to define required constants
    $constantsPath = './constants.php';
    if (file_exists($constantsPath)) {
        try {
            ob_start();
            require_once $constantsPath;
            ob_end_clean();
        } catch (Throwable $e) {
            log_message("Error reading constants file: " . $e->getMessage());
        }
    }

    foreach ($config['configFilePaths'] as $path) {
        if (!file_exists($path)) {
            continue;
        }

        try {
            ob_start();
            require $path;
            ob_end_clean();

            if (defined('DATABASE_HOSTNAME')) {
                $config['databaseHostname'] = DATABASE_HOSTNAME;
            }
            if (defined('DATABASE_PORT')) {
                $config['databasePort'] = DATABASE_PORT;
            }
            if (defined('DATABASE_USERNAME')) {
                $config['databaseUsername'] = DATABASE_USERNAME;
            }
            if (defined('DATABASE_PASSWORD')) {
                $config['databasePassword'] = DATABASE_PASSWORD;
            }
            if (defined('DATABASE_DATABASE')) {
                $config['databaseName'] = DATABASE_DATABASE;
            }



            if (!empty($config['databaseName']) && !empty($config['databaseHostname']) && !empty($config['databaseUsername'])) {
                break;
            }
        } catch (Throwable $e) {
            log_message("Error reading config file $path: " . $e->getMessage());
        }
    }

    // fallback
    if (empty($config['databaseName'])) {
        $config['databaseName'] = basename(getcwd());
    }
}

/**
 * Print Help / Usage
 */
function showHelp(): void
{
    echo <<<HELP
This script facilitates dumping and restoring MySQL/MariaDB databases. It also
processes dump files to minimize differences when switching between MySQL and
MariaDB, ensuring consistent output and minimal noise in version control.

Usage:
  php refreshdb.php [--dump | --restore | --repair | --dump-schema]

Options:
  --dump         Create a database dump file with transformations applied.
  --restore      Restore the database from an existing dump file.
  --repair       Apply normalization rules to an existing dump file without accessing the database.
  --dump-schema  Output a very compact schema (no data) to stdout (for LLM context).

Configuration:
  The script will read database credentials from config.php or wp-config.php.
  You can also customize settings by editing the \$config array at the top.

HELP;
}

/**
 * Execute Shell Command
 */
function executeCommand(string $command): bool
{
    log_message("Executing: $command");
    system($command, $result);
    if ($result !== 0) {
        log_message("Command failed with exit code $result");
        return false;
    }
    return true;
}

/**
 * Determine if a file is UTF16 (by BOM).
 */
function isUtf16(string $sourcePath): bool
{
    $firstBytes = file_get_contents($sourcePath, false, null, 0, 2);
    return ($firstBytes === "\xFF\xFE");
}

/**
 * Process Dump File for Consistency (line by line). Also supports a "schemaOnly"
 * mode that omits headers, inserts, etc.
 */
function normalizeDumpFile(array $config, bool $schemaOnly = false): bool
{
    log_message("Processing dump file for more consistent output (schemaOnly=" . ($schemaOnly ? 'true' : 'false') . ")...");

    if (!file_exists($config['dumpFilePath'])) {
        log_message("Source file does not exist: $config[dumpFilePath]");
        return false;
    }

    $filesizeInMB = round(filesize($config['dumpFilePath']) / 1024 / 1024, 0);
    if ($filesizeInMB > 50) {
        log_message($config['dumpFilePath'] . " is large: " . $filesizeInMB . " MB");
    }

    // Open source file
    if (!($sourceHandle = fopen($config['dumpFilePath'], 'r'))) {
        log_message("Failed to open source file: $config[dumpFilePath]");
        return false;
    }

    // Open target file
    if (!($targetHandle = fopen($config['tempFilePath'], 'w'))) {
        fclose($sourceHandle);
        log_message("Failed to open target file: $config[tempFilePath]");
        return false;
    }

    // In schemaOnly mode, skip adding custom headers
    if (!$schemaOnly) {
        addHeader($targetHandle);
    }

    $lineCount = 0;
    $startTime = microtime(true);

    // Only used if $schemaOnly == false
    $inInsertStatement = false;
    $valueBlocks = [];
    $insertStmt = '';

    while (($line = fgets($sourceHandle)) !== false) {

        $lineCount++;
        logProgress($lineCount, $startTime);

        // If we're not in schemaOnly mode, handle consolidated INSERT logic
        if (!$schemaOnly && $inInsertStatement) {
            $trimmedLine = trim($line);
            if (preg_match('/^\s*\(/', $trimmedLine) || preg_match('/\)\s*,\s*\(/', $trimmedLine)) {
                $parsedBlocks = parseValueBlocks($trimmedLine);
                if (!empty($parsedBlocks)) {
                    $valueBlocks = array_merge($valueBlocks, $parsedBlocks);
                }
                if (preg_match('/\);\s*$/', $trimmedLine)) {
                    writeConsolidatedInsert($targetHandle, $insertStmt, $valueBlocks);
                    $inInsertStatement = false;
                }
                continue;
            }
            if (preg_match('/;\s*$/', $trimmedLine) || !preg_match('/^\s*\(/i', $trimmedLine)) {
                if (!empty($valueBlocks)) {
                    writeConsolidatedInsert($targetHandle, $insertStmt, $valueBlocks);
                }
                $inInsertStatement = false;
                if (!preg_match('/^\s*;\s*$/', $trimmedLine)) {
                    fwrite($targetHandle, $line);
                }
                continue;
            }
            continue;
        }

        // Skip lines we don't want
        if (shouldSkipLine($line)) {
            continue;
        }

        // If schemaOnly, skip any INSERT lines altogether (there shouldn't be any, but just in case)
        if ($schemaOnly && preg_match('/^INSERT /i', ltrim($line))) {
            continue;
        }

        // Transform line in various ways
        $line = transformLine($line);

        // If not schemaOnly, detect start of an INSERT
        if (!$schemaOnly && preg_match('/^INSERT INTO `([^`]+)`(\s+VALUES|\s+\([^)]+\)\s+VALUES)\s*\(?/', $line, $matches)) {
            $inInsertStatement = true;
            $insertStmt = "INSERT INTO `{$matches[1]}` VALUES\n";
            $valueBlocks = [];

            if (preg_match('/VALUES\s*(\(.*)/i', $line, $values)) {
                $valueStr = rtrim($values[1], " \t\n\r\0\x0B;");
                $valueBlocks = parseValueBlocks($valueStr);
                if (preg_match('/\);\s*$/', $line)) {
                    writeConsolidatedInsert($targetHandle, $insertStmt, $valueBlocks);
                    $inInsertStatement = false;
                }
            }
            continue;
        }

        // Write lines
        if (!$schemaOnly && str_starts_with($line, '-- Table structure for table')) {
            fwrite($targetHandle, "\n");
        }
        fwrite($targetHandle, $line);
    }

    // Handle any trailing INSERT data if we ended in the middle
    if (!$schemaOnly && $inInsertStatement && !empty($valueBlocks)) {
        writeConsolidatedInsert($targetHandle, $insertStmt, $valueBlocks);
    }

    fclose($sourceHandle);
    fclose($targetHandle);

    rename($config['tempFilePath'], $config['dumpFilePath']);
    log_message("Processed $lineCount lines total");

    return true;
}

/**
 * Optional: add a simple header line to the top (skipped when schemaOnly=true).
 */
function addHeader($targetHandle): void
{
    fwrite($targetHandle, "-- Dump created on " . date('Y-m-d H:i:s') . " by " . gethostname() . "\n");
    fwrite($targetHandle, "SET FOREIGN_KEY_CHECKS=0;\n");
    fwrite($targetHandle, "SET @@SESSION.sql_mode='NO_AUTO_VALUE_ON_ZERO';\n\n");
}

/**
 * Check if we should skip an entire line altogether
 */
function shouldSkipLine(string $line): bool
{
    $patterns = [
        '/^-- (MySQL dump|Dump|Server version|Dump completed on|MariaDB dump|Host:|Current Database:|Dump created on)/',
        '/^-- -{10,}/',                // lines like "-- ---------"
        '/^--\s*$/',                   // just "--"
        '/^\s*$/',                     // blank line
        '/^\s*\/\*![0-9]+\s+SET/',     // e.g., "/*!40101 SET..."
        '/\/\*M?!999999\\\- enable the sandbox mode \*\//',
        '/\/\*!999999\\\- enable the sandbox mode \*\//',
        '/DROP TABLE IF EXISTS|DROP DATABASE|CREATE DATABASE|USE\s+`/',
        '/^SET FOREIGN_KEY_CHECKS=0;?$/',
        '/^SET @@SESSION\.sql_mode=\'NO_AUTO_VALUE_ON_ZERO\';?$/'
    ];

    foreach ($patterns as $pattern) {
        if (preg_match($pattern, $line)) {
            return true;
        }
    }
    return false;
}

/**
 * Transform a single line by removing or replacing certain things
 */
function transformLine(string $line): string
{
    $replacements = [
        '/DEFINER=`[^`]+`@`[^`]+`\s*/' => '',            // Remove DEFINER clauses
        '/\s+AUTO_INCREMENT=\d+/' => '',                // Remove AUTO_INCREMENT
        '/\butf8\b/' => 'utf8mb4',                      // Replace utf8 with utf8mb4
        '/\b(tiny|small|medium|big)?int\(\d+\)/' => '$1int', // int(11)->int, etc.
        '/\s+COLLATE\s+[\'"]?[a-zA-Z0-9_]+[\'"]?/' => '',  // remove collate
        '/\s+COLLATE\s*=\s*[\'"]?[a-zA-Z0-9_]+[\'"]?/' => '',
        '/(=\s*)(\'(\d+(\.\d+)?)\')/i' => '$1$3',       // remove numeric quotes
    ];

    // Remove engine info, charset, etc. from CREATE TABLE, if present
    $line = preg_replace('/ENGINE\s*=\s*\S+/i', '', $line);
    $line = preg_replace('/\bDEFAULT\s+CHARSET\s*=\s*\S+/i', '', $line);
    $line = preg_replace('/\bCHARSET\s*=\s*\S+/i', '', $line);
    $line = preg_replace('/ROW_FORMAT\s*=\s*\S+/i', '', $line);

    foreach ($replacements as $pattern => $replacement) {
        $line = preg_replace($pattern, $replacement, $line);
    }

    // Clean up extra spaces left behind
    $line = trim(preg_replace('/\s+/', ' ', $line));

    if ($line !== '') {
        $line .= "\n"; // restore a newline
    }
    return $line;
}

/**
 * Parse multiple value blocks from lines like: (1,'abc'),(2,'xyz'),(3,'foo')
 */
function parseValueBlocks(string $lineFragment): array
{
    $blocks = [];
    $lineFragment = rtrim(trim($lineFragment), ',;');

    // If multiple row blocks in one string => split them
    if (str_contains($lineFragment, '),(')) {
        $parts = preg_split('/\),\s*\(/', $lineFragment);
        foreach ($parts as $i => $part) {
            $part = rtrim(trim($part), ',');
            // Add missing parentheses
            if (!str_starts_with($part, '(')) {
                $part = '(' . $part;
            }
            if (!str_ends_with($part, ')')) {
                $part .= ')';
            }
            $blocks[] = $part;
        }
    } else {
        // Single row block
        if (!str_starts_with($lineFragment, '(')) {
            $lineFragment = '(' . $lineFragment;
        }
        if (!str_ends_with($lineFragment, ')')) {
            $lineFragment .= ')';
        }
        $blocks[] = $lineFragment;
    }
    return $blocks;
}

/**
 * Consolidate multi-line INSERT blocks respecting maxLineLength
 */
function writeConsolidatedInsert($targetHandle, string $insertHeader, array $valuesBuffer): void
{
    global $config;

    fwrite($targetHandle, $insertHeader);

    $consolidatedValues = [];
    $currentValueLine = "";
    $maxLineLength = $config['maxLineLength'];

    foreach ($valuesBuffer as $valueBlock) {
        $valueBlock = rtrim($valueBlock, ","); // remove trailing comma

        if (strlen($currentValueLine . $valueBlock) > $maxLineLength) {
            if (!empty($currentValueLine)) {
                $consolidatedValues[] = $currentValueLine;
            }
            $currentValueLine = $valueBlock;
        } else {
            if (!empty($currentValueLine)) {
                $currentValueLine .= ",";
            }
            $currentValueLine .= $valueBlock;
        }
    }
    if (!empty($currentValueLine)) {
        $consolidatedValues[] = $currentValueLine;
    }

    // Ensure trailing commas are trimmed
    for ($i = 0; $i < count($consolidatedValues) - 1; $i++) {
        if (!str_ends_with($consolidatedValues[$i], ",")) {
            $consolidatedValues[$i] .= ",";
        }
    }
    if (!empty($consolidatedValues)) {
        $consolidatedValues[count($consolidatedValues) - 1] = rtrim($consolidatedValues[count($consolidatedValues) - 1], ",");
    }

    fwrite($targetHandle, implode("\n", $consolidatedValues));
    $lastValue = end($consolidatedValues);
    if (str_ends_with($lastValue, ';')) {
        fwrite($targetHandle, "\n");
    } else {
        fwrite($targetHandle, ";\n");
    }
}

/**
 * Log a message with elapsed time
 */
function log_message($message): void
{
    global $start;
    $elapsed = microtime(true) - $start;
    echo "\e[1;36m[" . number_format($elapsed, 1) . "]\e[0m $message\n";
}

/**
 * Log file stats: path & size
 */
function logFileStats(string $filePath): void
{
    if (!file_exists($filePath)) {
        log_message("File not found: $filePath");
        return;
    }
    $size = filesize($filePath);
    log_message("Database file statistics:");
    log_message("- Path: $filePath");
    log_message("- Size: " . formatBytes($size));
}

/**
 * Byte size to human-readable form
 */
function formatBytes(int $bytes, int $precision = 2): string
{
    $units = ['B', 'KB', 'MB', 'GB', 'TB'];
    $bytes = max($bytes, 0);
    $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
    $pow = min($pow, count($units) - 1);
    $bytes /= (1 << (10 * $pow));
    return round($bytes, $precision) . ' ' . $units[$pow];
}

/**
 * Show line processing progress occasionally
 */
function logProgress(int $lineCount, float $startTime): void
{
    if ($lineCount % 20000 === 0) {
        $elapsed = microtime(true) - $startTime;
        $rate = $lineCount / $elapsed;
        log_message(sprintf("Processed %d lines... (%.1f lines/sec)", $lineCount, $rate));
    }
}