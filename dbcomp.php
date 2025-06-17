<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require 'vendor/autoload.php';
require 'config.php'; // Local DB config

const PRODUCTION_HOSTNAME = 'kriit.vikk.ee';  // Production
const COMPARE_MODE_DEVELOPMENT = 'development';
const COMPARE_MODE_DEPLOYMENT = 'deployment';

// Determine mode
$compareMode = $_GET['mode']
    ?? ($_SERVER['HTTP_HOST'] === PRODUCTION_HOSTNAME
        ? COMPARE_MODE_DEPLOYMENT
        : COMPARE_MODE_DEVELOPMENT);

$publicIp = @file_get_contents('https://api.ipify.org/');

// --- Database Helpers ---
function getRows(string $sql, mysqli $db, bool $fetchFirst = false)
{
    $rows = [];
    if ($res = $db->query($sql)) {
        while ($row = $res->fetch_array(MYSQLI_ASSOC)) {
            $rows[] = $row;
            if ($fetchFirst) {
                break;
            }
        }
        $res->close();
    }
    return $fetchFirst ? ($rows[0] ?? null) : $rows;
}

// --- Parsing / Normalizing ---
function getDdlsByTable(mysqli $db, string $database): array
{
    $ddls = [];
    $tables = getRows("SHOW TABLES FROM `$database`", $db);
    foreach ($tables as $t) {
        $tableName = reset($t);
        $create = getRows("SHOW CREATE TABLE `$database`.`$tableName`", $db, true);
        if (!empty($create['Create Table'])) {
            $ddls[$tableName] = $create['Create Table'];
        }
    }
    return $ddls;
}

function parseCreateTable(string $createStmt): array
{
    $res = ['tableName' => '', 'columns' => [], 'indexes' => [], 'constraints' => []];
    $lines = preg_split('/\r\n|\r|\n/', $createStmt);
    if (preg_match('/CREATE TABLE\s+`([^`]+)`/i', $lines[0], $m)) {
        $res['tableName'] = $m[1];
    }
    array_shift($lines);
    array_pop($lines);

    foreach ($lines as $line) {
        $line = trim(trim($line), ',');
        if (preg_match('/^`([^`]+)`\s+(.*)$/', $line, $matches)) {
            $res['columns'][$matches[1]] = ['rawDefinition' => normalizeSpaces($matches[2])];
        } elseif (preg_match('/^(PRIMARY KEY|UNIQUE KEY|KEY)\s+(.*)$/i', $line, $m2)) {
            $type = strtoupper($m2[1]);
            $rest = $m2[2];
            $idxName = ($type === 'PRIMARY KEY') ? 'PRIMARY'
                : (preg_match('/^`([^`]+)`\s*\((.*)\)/', $rest, $m3) ? $m3[1] : $type);
            $res['indexes'][$idxName] = normalizeSpaces($m2[1] . ' ' . $rest);
        } elseif (preg_match('/^CONSTRAINT\s+`([^`]+)`\s+FOREIGN KEY\s+(.*)$/i', $line, $m2)) {
            $res['constraints'][$m2[1]] = "CONSTRAINT `{$m2[1]}` FOREIGN KEY " . $m2[2];
        }
    }
    return $res;
}

function normalizeSpaces(string $str): string
{
    return preg_replace('/\s+/', ' ', trim($str));
}

// Combine normalizers + field sorting
function normalizeCreateTable(string $stmt): string
{
    $stmt = preg_replace('/AUTO_INCREMENT=\d+/', 'AUTO_INCREMENT=?', $stmt);
    $stmt = preg_replace('/COLLATE=utf8mb[34]_general_ci/', 'COLLATE=utf8mb?_general_ci', $stmt);
    $stmt = preg_replace('/CHARSET=utf8mb[34]/', 'CHARSET=utf8mb?', $stmt);

    // Sort fields
    if (!preg_match('/^(CREATE TABLE .+?\()(.+?)(\)\s*ENGINE.+)$/s', $stmt, $m)) {
        return $stmt;
    }
    [$all, $header, $fields, $footer] = $m;
    $lines = array_map('trim', explode(",\n", $fields));
    $cols = $idxs = $consts = [];

    foreach ($lines as $ln) {
        if (preg_match('/^CONSTRAINT/i', $ln)) {
            $consts[] = $ln;
        } elseif (preg_match('/^(PRIMARY KEY|KEY|UNIQUE KEY|FOREIGN KEY)/i', $ln)) {
            $idxs[] = $ln;
        } else {
            $cols[] = $ln;
        }
    }
    sort($cols);
    sort($idxs);
    sort($consts);
    $merged = implode(",\n  ", array_merge($cols, $idxs, $consts));
    return $header . "\n  " . $merged . "\n)" . ltrim($footer, ")");
}

// --- Compare & Build Sync ---
function buildSyncCommandsNonDestructive(array $localParsed, array $stagingParsed): array
{
    $allTables = array_unique(array_merge(array_keys($localParsed), array_keys($stagingParsed)));
    sort($allTables);
    $sqlCommands = [];

    foreach ($allTables as $t) {
        $local = $localParsed[$t] ?? null;
        $stag = $stagingParsed[$t] ?? null;

        if ($local && !$stag) {
            $sqlCommands[] = generateCreateTableSQL($local);
        } elseif (!$local && $stag) {
            // Optionally: $sqlCommands[] = "DROP TABLE `$t`;";
        } elseif ($local && $stag) {
            foreach (generateAlterTableSQL($local, $stag) as $c) {
                $sqlCommands[] = $c;
            }
        }
    }
    return $sqlCommands;
}

function generateCreateTableSQL(array $def): string
{
    $table = $def['tableName'];
    $cols = [];
    foreach ($def['columns'] as $colName => $colData) {
        $cols[] = "`$colName` {$colData['rawDefinition']}";
    }
    foreach ($def['indexes'] as $idxDef) {
        $cols[] = $idxDef;
    }
    // constraints not shown; add if needed
    return "CREATE TABLE `{$table}` (\n  " . implode(",\n  ", $cols) . "\n);";
}

function generateAlterTableSQL(array $local, array $staging): array
{
    $t = $local['tableName'];
    $changes = [];
    foreach (compareColumns($t, $local['columns'], $staging['columns']) as $c) {
        $changes[] = "ALTER TABLE `$t` $c;";
    }
    foreach (compareIndexes($t, $local['indexes'], $staging['indexes']) as $c) {
        $changes[] = "ALTER TABLE `$t` $c;";
    }
    foreach (compareConstraints($t, $local['constraints'], $staging['constraints']) as $c) {
        $changes[] = "ALTER TABLE `$t` $c;";
    }
    return $changes;
}

function compareConstraints($table, $local, $staging): array
{
    $res = [];
    $all = array_unique(array_merge(array_keys($local), array_keys($staging)));
    foreach ($all as $name) {
        if (isset($local[$name]) && !isset($staging[$name])) {
            $res[] = "ADD {$local[$name]}";
        } elseif (!isset($local[$name]) && isset($staging[$name])) {
            $res[] = "DROP FOREIGN KEY `$name`";
        } elseif (isset($local[$name], $staging[$name]) && $local[$name] !== $staging[$name]) {
            $res[] = "DROP FOREIGN KEY `$name`";
            $res[] = "ADD {$local[$name]}";
        }
    }
    return $res;
}

function compareColumns($table, $localCols, $stagCols): array
{
    $res = [];
    $all = array_unique(array_merge(array_keys($localCols), array_keys($stagCols)));
    foreach ($all as $c) {
        $l = $localCols[$c]['rawDefinition'] ?? null;
        $s = $stagCols[$c]['rawDefinition'] ?? null;
        if ($l && !$s) {
            $res[] = "ADD COLUMN `$c` $l";
        } elseif (!$l && $s) {
            // Possibly: $res[] = "DROP COLUMN `$c`";
        } elseif ($l && $s && $l !== $s) {
            $res[] = "MODIFY COLUMN `$c` $l";
        }
    }
    return $res;
}

function compareIndexes($table, $localIdx, $stagIdx): array
{
    $res = [];
    $all = array_unique(array_merge(array_keys($localIdx), array_keys($stagIdx)));
    foreach ($all as $i) {
        $l = $localIdx[$i] ?? null;
        $s = $stagIdx[$i] ?? null;
        if ($l && !$s) {
            $res[] = "ADD $l";
        } elseif (!$l && $s) {
            $res[] = ($i === 'PRIMARY') ? "DROP PRIMARY KEY" : "DROP INDEX `$i`";
        } elseif ($l && $s && $l !== $s) {
            $res[] = ($i === 'PRIMARY') ? "DROP PRIMARY KEY" : "DROP INDEX `$i`";
            $res[] = "ADD $l";
        }
    }
    return $res;
}

// --- Rendering Diffs ---
function renderTableDiffEnhanced($old, $new, $oldLabel, $newLabel)
{
    $differ = new \Jfcherng\Diff\Differ(explode("\n", $old), explode("\n", $new));
    $renderer = \Jfcherng\Diff\Factory\RendererFactory::make('SideBySide', [
        'detailLevel' => 'line',
        'lineNumbers' => true,
        'separateBlock' => true,
        'showHeader' => true,
        'spacesToNbsp' => false,
        'tabSize' => 4,
        'language' => [
            'eng',
            ['old_version' => $oldLabel, 'new_version' => $newLabel],
        ],
    ]);
    return $renderer->render($differ);
}

function renderDiff($table, $oldDDL, $newDDL, $oldLabel, $newLabel, $alterStatements)
{
    if ($oldDDL && !$newDDL) {
        return "<h3><span class='badge bg-danger'>{$table}</span></h3>
            <div class='text-center'><small class='text-muted'>exists only in {$oldLabel}</small></div>
            <pre>" . htmlentities($oldDDL) . "</pre>";
    }
    if (!$oldDDL && $newDDL) {
        return "<h3><span class='badge bg-danger'>{$table}</span></h3>
            <div class='text-center'><small class='text-muted'>exists only in {$newLabel}</small></div>
            <pre>" . htmlentities($newDDL) . "</pre>";
    }
    // Both exist, might differ
    if ($oldDDL !== $newDDL) {
        $diffHtml = renderTableDiffEnhanced($oldDDL, $newDDL, $oldLabel, $newLabel);
        $altHtml = $alterStatements ? "<pre>" . htmlentities(implode("\n", $alterStatements)) . "</pre>" : "";
        return "<h3><span class='badge bg-danger'>{$table}</span></h3>{$diffHtml}{$altHtml}";
    }
    return '';
}

function buildDifferences(array $localDDLs, array $stagingDDLs, string $mode): array
{
    $diffs = [];
    $allTables = array_unique(array_merge(array_keys($localDDLs), array_keys($stagingDDLs)));
    sort($allTables);

    // Decide old vs new
    if ($mode === COMPARE_MODE_DEPLOYMENT) {
        $oldDDLs = $localDDLs;  // Local => old
        $newDDLs = $stagingDDLs;
        $oldLabel = 'Local';
        $newLabel = 'Staging';
    } else {
        $oldDDLs = $stagingDDLs;
        $newDDLs = $localDDLs;
        $oldLabel = 'Staging';
        $newLabel = 'Local';
    }

    // Pre-parse for alter statements
    $oldParsed = [];
    foreach ($oldDDLs as $tbl => $stmt) {
        $oldParsed[$tbl] = parseCreateTable($stmt);
    }
    $newParsed = [];
    foreach ($newDDLs as $tbl => $stmt) {
        $newParsed[$tbl] = parseCreateTable($stmt);
    }

    foreach ($allTables as $tbl) {
        $o = normalizeCreateTable($oldDDLs[$tbl] ?? '');
        $n = normalizeCreateTable($newDDLs[$tbl] ?? '');
        $alterStmts = [];
        if (!empty($oldParsed[$tbl]) && !empty($newParsed[$tbl]) && $o !== $n) {
            $alterStmts = generateAlterTableSQL($newParsed[$tbl], $oldParsed[$tbl]);
        }
        $html = renderDiff($tbl, $o, $n, $oldLabel, $newLabel, $alterStmts);
        if ($html) {
            $diffs[] = $html;
        }
    }
    return $diffs;
}

// --- Main ---
$diffs = [];
$syncCommands = [];
$connectionError = null;
$failedConnection = null;
$isConnectionFailure = false;

try {
    // Try local connection first
    try {
        $localDb = mysqli_init();
        $localDb->options(MYSQLI_OPT_CONNECT_TIMEOUT, 3);
        $localPort = defined('DATABASE_PORT') ? (int)DATABASE_PORT : 3306;
        if (!$localDb->real_connect(DATABASE_HOSTNAME, DATABASE_USERNAME, DATABASE_PASSWORD, DATABASE_DATABASE, $localPort)) {
            // Check if it's a connection error (error numbers 2002, 2003, or 2005 indicate connection issues)
            $isConnectionFailure = in_array($localDb->connect_errno, [2002, 2003, 2005]);
            throw new Exception($localDb->connect_error);
        }
    } catch (Exception $e) {
        throw new Exception("Local database connection failed: " . $e->getMessage());
    }

    // Try staging connection second
    try {
        $stagingDb = mysqli_init();
        $stagingDb->options(MYSQLI_OPT_CONNECT_TIMEOUT, 3);
        $stagingPort = defined('STAGING_DATABASE_PORT') ? (int)STAGING_DATABASE_PORT : 3306;
        if (!$stagingDb->real_connect(STAGING_DATABASE_HOSTNAME, STAGING_DATABASE_USERNAME, STAGING_DATABASE_PASSWORD, STAGING_DATABASE_DATABASE, $stagingPort)) {
            throw new Exception($stagingDb->connect_error);
        }
    } catch (Exception $e) {
        $localDb->close();
        throw new Exception("Staging database connection failed: " . $e->getMessage());
    }

    $localDDLs = getDdlsByTable($localDb, DATABASE_DATABASE);
    $stagingDDLs = getDdlsByTable($stagingDb, STAGING_DATABASE_DATABASE);

    $localParsed = [];
    foreach ($localDDLs as $tbl => $stmt) {
        $localParsed[$tbl] = parseCreateTable($stmt);
    }
    $stagingParsed = [];
    foreach ($stagingDDLs as $tbl => $stmt) {
        $stagingParsed[$tbl] = parseCreateTable($stmt);
    }

    $diffs = buildDifferences($localDDLs, $stagingDDLs, $compareMode);
    $syncCommands = buildSyncCommandsNonDestructive($localParsed, $stagingParsed);

    $localDb->close();
    $stagingDb->close();
} catch (Exception $e) {
    $connectionError = $e->getMessage();
    if (strpos($connectionError, "Local database connection failed") === 0) {
        $failedConnection = [
            'type' => 'Local',
            'host' => DATABASE_HOSTNAME,
            'user' => DATABASE_USERNAME,
            'database' => DATABASE_DATABASE
        ];
    } else {
        $failedConnection = [
            'type' => 'Staging',
            'host' => STAGING_DATABASE_HOSTNAME,
            'user' => STAGING_DATABASE_USERNAME,
            'database' => STAGING_DATABASE_DATABASE
        ];
    }
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Compare Database Schemas</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        <?= \Jfcherng\Diff\DiffHelper::getStyleSheet() ?>
        body {
            font-family: sans-serif;
            margin: 20px;
            background-color: #e9e9e9;
        }

        pre {
            background: #f9f9f9;
            padding: 10px;
            border: 1px solid black;
            background-color: aquamarine;
        }

        h3 {
            margin-top: 1.2em;
            margin-bottom: 3px;
            text-align: center;
        }

        .warning {
            color: darkred;
            font-weight: bold;
        }

        .diff-wrapper .diff-header {
            text-align: center !important;
        }
    </style>
</head>
<body>
<div class="container-fluid">
    <h1>Database Schema Comparison Tool</h1>

    <?php if ($connectionError): ?>
        <div class="alert alert-danger mt-3">
            <h4 class="alert-heading"><?= $failedConnection['type'] ?> Database Connection Error</h4>
            <p>Failed to connect to: <code><?= htmlspecialchars($failedConnection['user']) ?>@<?= htmlspecialchars($failedConnection['host']) ?>/<?= htmlspecialchars($failedConnection['database']) ?></code></p>
            <p>Error: <?= htmlspecialchars($connectionError) ?></p>
            <hr>
            <?php if ($failedConnection['type'] === 'Local'): ?>
                <?php if ($isConnectionFailure): ?>
                    <p class="mb-0">Please check if your local database server is running on <?= htmlspecialchars(DATABASE_HOSTNAME) ?></p>
                <?php else: ?>
                    <p class="mb-0">Please check your database settings and permissions.</p>
                <?php endif; ?>
            <?php else: ?>
                <p class="mb-0">Please check your database connection settings and ensure your IP is allowed in the firewall rules.</p>
                <details class="mt-3">
                    <summary class="h5" style="color: #842029; cursor: pointer;">How to add your IP to staging server's firewall</summary>
                    <div class="card mt-2">
                        <div class="card-body">
                            <h6>Adding Your IP to Staging Server's Firewall</h6>
                            <ol>
                                <li>SSH into the staging server:
                                    <div class="d-flex align-items-center">
                                        <pre><code>ssh root@<?= STAGING_DATABASE_HOSTNAME ?></code></pre>
                                        <button class="btn btn-sm btn-outline-secondary ms-2"
                                                onclick="copyToClipboard('ssh root@<?= STAGING_DATABASE_HOSTNAME ?>', event)">Copy</button>
                                    </div>
                                </li>
                                <li>Open the firewall configuration file:
                                    <div class="d-flex align-items-center">
                                        <pre><code>sudo nano /etc/awall/mariadb.json</code></pre>
                                        <button class="btn btn-sm btn-outline-secondary ms-2"
                                                onclick="copyToClipboard('sudo nano /etc/awall/mariadb.json', event)">Copy</button>
                                    </div>
                                </li>
                                <li>Add your public IP address to the "src" array:
                                    <div class="d-flex align-items-center">
                                    <pre><code>{
  "description": "Allow MariaDB connections",
  "service": {
    "mariadb": {
      "proto": "tcp",
      "port": <?= defined('STAGING_DATABASE_PORT') ? STAGING_DATABASE_PORT : 3306 ?>
    }
  },
  "filter": [
    {
      "in": "internet",
      "out": "_fw",
      "service": "mariadb",
      "action": "accept",
      "src": [
        <span style="background-color: #ffeb3b">"<?= $publicIp ?: 'YOUR_PUBLIC_IP' ?>"</span>
      ]
    }
  ]
}</code></pre>
                                    </div>
                                </li>
                                <li>Save the file (Ctrl+X, Y, Enter).</li>
                                <li>Activate the changes:
                                    <div class="d-flex align-items-center">
                                        <pre><code>sudo awall activate</code></pre>
                                        <button class="btn btn-sm btn-outline-secondary ms-2"
                                                onclick="copyToClipboard('sudo awall activate', event)">Copy</button>
                                    </div>
                                </li>
                            </ol>
                            <div class="alert alert-info">
                                <strong>Tip:</strong> To view your public IP, use this command:<br>
                                <div class="d-flex align-items-center">
                                    <code>curl https://api.ipify.org/</code>
                                    <button class="btn btn-sm btn-outline-secondary ms-2"
                                            onclick="copyToClipboard('curl https://api.ipify.org/', event)">Copy</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </details>
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <div class="alert alert-info mt-3">
        <p>
            <strong>Dev Mode (Staging → Local):</strong> to see what changes have been made compared to staging<br>
            <strong>Deployment Mode (Local → Staging):</strong> to see what changes have to be made to the production
            database to bring it in line with the staging database
        </p>
    </div>

    <div>
        <p>
            <a href="?mode=<?= COMPARE_MODE_DEVELOPMENT ?>"
               class="btn btn-outline-primary <?= $compareMode === COMPARE_MODE_DEVELOPMENT ? 'active' : '' ?>">
                Dev Mode
            </a>
            <a href="?mode=<?= COMPARE_MODE_DEPLOYMENT ?>"
               class="btn btn-outline-primary <?= $compareMode === COMPARE_MODE_DEPLOYMENT ? 'active' : '' ?>">
                Deploy Mode
            </a>
        </p>
        <p>
            Local: <code><?= DATABASE_USERNAME ?>@<?= DATABASE_HOSTNAME ?>/<?= DATABASE_DATABASE ?></code><br>
            Staging: <code><?= STAGING_DATABASE_USERNAME ?>@<?= STAGING_DATABASE_HOSTNAME ?>/<?= STAGING_DATABASE_DATABASE ?></code>
        </p>
    </div>

    <section>
        <?php if (empty($diffs)): ?>
            <p>No differences found.</p>
        <?php else: ?>
            <?php foreach ($diffs as $diffHtml): ?>
                <?= $diffHtml ?>
            <?php endforeach; ?>
        <?php endif; ?>
    </section>

    <hr>

    <section>
        <h2>SQL to transform <?= $compareMode === COMPARE_MODE_DEPLOYMENT ? 'local schema to staging' : 'staging schema to local' ?> (non-destructive)</h2>
        <p class="warning">Review carefully before applying!</p>
        <?php if (empty($syncCommands)): ?>
            <p>No changes needed.</p>
        <?php else: ?>
            <pre><?= implode("\n", $syncCommands) ?></pre>
        <?php endif; ?>
    </section>
</div>

<script>
    function copyToClipboard(text, event) {
        navigator.clipboard.writeText(text)
            .then(() => {
                const btn = event.target;
                const originalText = btn.textContent;
                btn.textContent = 'Copied!';
                btn.classList.remove('btn-outline-secondary');
                btn.classList.add('btn-success');

                setTimeout(() => {
                    btn.textContent = originalText;
                    btn.classList.remove('btn-success');
                    btn.classList.add('btn-outline-secondary');
                }, 2000);
            })
            .catch(err => {
                console.error('Failed to copy text: ', err);
                alert('Failed to copy to clipboard');
            });
    }
</script>
</body>
</html>
