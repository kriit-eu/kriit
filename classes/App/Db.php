<?php namespace App;

use Doctrine\SqlFormatter\SqlFormatter;
use JetBrains\PhpStorm\NoReturn;

class Db
{
    private static ?Db $instance = null;
    private \mysqli $conn;
    public array $debugLog = [];

    const GET_RESULT = 1;
    const AFFECTED_ROWS = 2;
    // If you want a specialized mode for getOne() or getFirst(), you could define:
    // const GET_ONE = 3; // etc.

    private function __construct(string $host, string $user, string $password, string $dbname)
    {
        $this->conn = new \mysqli($host, $user, $password, $dbname);

        // Set error reporting level
        mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

        if ($this->conn->connect_error) {
            throw new \Exception("Connection failed: " . $this->conn->connect_error);
        }
    }

    public static function getInstance(): Db
    {
        if (self::$instance === null) {
            try {
                self::$instance = new self(
                    DATABASE_HOSTNAME,
                    DATABASE_USERNAME,
                    DATABASE_PASSWORD,
                    DATABASE_DATABASE
                );
            } catch (\Exception $e) {
                die("Database connection error: " . $e->getMessage());
            }
        }
        return self::$instance;
    }

    /**
     * Debug function to produce a human-readable version of the query
     * with placeholders substituted by parameter values (for logs).
     *
     * Supports both question marks and named placeholders.
     */
    private function debugQuery(string $query, array $params): string
    {
        // If no params, nothing to replace
        if (empty($params)) {
            // If it's a new query, add it once to the debug log
            if (!isset($this->debugLog[$query])) {
                $this->debugLog[$query] = [
                    'query' => $query,
                    'count' => 1,
                    'cumulative_time' => 0.0
                ];
            } else {
                // If it already exists, just increment the count
                $this->debugLog[$query]['count'] += 1;

                // Move the entry to the end of $debugLog
                $existing = $this->debugLog[$query];
                unset($this->debugLog[$query]);
                $this->debugLog[$query] = $existing;
            }
            return $query;
        }

        $hasQuestionMarks = str_contains($query, '?');
        preg_match_all('/:[a-zA-Z0-9_]+/', $query, $namedMatches);
        $hasNamed = !empty($namedMatches[0]);

        if ($hasQuestionMarks && $hasNamed) {
            throw new \Exception("Cannot mix named placeholders and question marks in the same query.");
        }

        $debugQuery = $query;

        // If we are using named placeholders
        if ($hasNamed) {
            foreach ($namedMatches[0] as $placeholder) {
                $key = ltrim($placeholder, ':');
                $value = $params[$key] ?? null;
                $escapedValue = ($value === null)
                    ? 'NULL'
                    : "'" . $this->conn->real_escape_string($value) . "'";
                $debugQuery = str_replace($placeholder, $escapedValue, $debugQuery);
            }
        } else {
            // question-mark placeholders
            foreach ($params as $param) {
                $escapedValue = ($param === null)
                    ? 'NULL'
                    : "'" . $this->conn->real_escape_string($param) . "'";
                // Replace only the *first* occurrence:
                $debugQuery = preg_replace('/\?/', $escapedValue, $debugQuery, 1);
            }
        }

        if (isset($this->debugLog[$debugQuery])) {
            $this->debugLog[$debugQuery]['count'] += 1;
            $existing = $this->debugLog[$debugQuery];
            unset($this->debugLog[$debugQuery]);
            $this->debugLog[$debugQuery] = $existing;
        } else {
            $this->debugLog[$debugQuery] = [
                'query' => $debugQuery,
                'count' => 1,
                'cumulative_time' => 0.0
            ];
        }

        return $debugQuery;
    }

    #[NoReturn]
    public static function displayError($e): void
    {
        // Remove previous output
        ob_clean();

        // Get the last query from the debug log
        $lastQuery = end(self::getInstance()->debugLog)['query'] ?? '(none)';

        // Get debug log
        $highlightedQuery = (new SqlFormatter())->format($lastQuery);
        echo("Error: {$e->getMessage()}<br><br><strong>Query:</strong><br><code>$highlightedQuery</code>");

        // Show full stack trace (HTML formatted)
        $trace = $e->getTrace();
        $rootDir = dirname(__DIR__, 2);

        $trace = array_map(function ($item) use ($rootDir) {
            if (!empty($item['file'])) {
                $item['file'] = str_replace($rootDir, '', $item['file']);
            }
            return $item;
        }, $trace);

        echo '<br><br><strong>Stack trace:</strong><br>';
        echo '<pre>';
        foreach ($trace as $item) {
            $file = $item['file'] ?? '';
            $line = $item['line'] ?? '';
            $function = $item['function'] ?? '';
            $class = $item['class'] ?? '';
            $type = $item['type'] ?? '';
            echo "$file:$line <b>$class$type$function</b>\n";
        }
        echo '</pre>';

        echo '<br><br><strong>Debug log:</strong><br>';
        foreach (self::getDebugLog() as $logItem) {
            echo $logItem . '<br>';
        }

        echo '<br><strong>Aggregate Query Execution Time:</strong> ' . self::getTotalQueryTime() . ' seconds<br>';
    }

    /**
     * Given a set of parameter values, build a corresponding type string
     * for mysqli bind_param (i, d, s)
     */
    private static function getTypeString(array $params): string
    {
        return implode('', array_map(function ($param) {
            $type = gettype($param);
            return match ($type) {
                'boolean', 'integer' => 'i',
                'double' => 'd',
                'NULL', 'string' => 's',
                default => throw new \Exception("Unsupported data type: {$type}"),
            };
        }, $params));
    }

    /**
     * Main function that executes prepared statements.
     */
    private function executePrepared(string $query, array $params = [], int $returnType = self::GET_RESULT): bool|\mysqli_result
    {
        if (empty($params)) {
            $debugQuery = $this->debugQuery($query, []);
            $startTime = microtime(true);
            $res = $this->conn->query($query);
            $timeTaken = microtime(true) - $startTime;
            $this->debugLog[$debugQuery]['cumulative_time'] += $timeTaken;
            return ($returnType === self::AFFECTED_ROWS) ? $this->conn->affected_rows : $res;
        }

        $hasQuestionMarks = str_contains($query, '?');
        preg_match_all('/:[a-zA-Z0-9_]+/', $query, $namedMatches);
        $namedPlaceholders = $namedMatches[0] ?? [];

        if ($hasQuestionMarks && !empty($namedPlaceholders)) {
            throw new \Exception("Cannot mix named placeholders and question marks in the same query.");
        }

        $debugQuery = $this->debugQuery($query, $params);

        // Named placeholders:
        if (!empty($namedPlaceholders)) {
            $orderedParams = [];
            foreach ($namedPlaceholders as $ph) {
                $key = ltrim($ph, ':');
                if (!array_key_exists($key, $params)) {
                    throw new \Exception("Missing named parameter :{$key}");
                }
                $orderedParams[] = $params[$key];
            }
            $queryForMysqli = preg_replace('/:[a-zA-Z0-9_]+/', '?', $query);
            $types = self::getTypeString($orderedParams);
            $stmt = $this->conn->prepare($queryForMysqli);
            if (!empty($orderedParams)) {
                $stmt->bind_param($types, ...$orderedParams);
            }
            $startTime = microtime(true);
            $stmt->execute();
            $timeTaken = microtime(true) - $startTime;
            $this->debugLog[$debugQuery]['cumulative_time'] += $timeTaken;

            return ($returnType === self::AFFECTED_ROWS)
                ? $stmt->affected_rows
                : $stmt->get_result();
        } // Question marks:
        else {
            $types = self::getTypeString($params);
            $stmt = $this->conn->prepare($query);
            if (!empty($params)) {
                $stmt->bind_param($types, ...$params);
            }
            $startTime = microtime(true);
            $stmt->execute();
            $timeTaken = microtime(true) - $startTime;
            $this->debugLog[$debugQuery]['cumulative_time'] += $timeTaken;

            return ($returnType === self::AFFECTED_ROWS)
                ? $stmt->affected_rows
                : $stmt->get_result();
        }
    }

    /**
     * Returns the first column of the first row, or null.
     * Changed 3rd param to self::GET_RESULT instead of $callingFunction
     */
    public static function getOne($query, array $params = [])
    {
        $backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2);
        $callingFunction = $backtrace[1]['function'] ?? 'Global Scope';

        // Important fix: pass an integer constant (e.g. self::GET_RESULT)
        // rather than the string $callingFunction.
        $res = self::getInstance()->executePrepared($query, $params, self::GET_RESULT);
        $row = $res->fetch_array(MYSQLI_NUM);
        return $row[0] ?? null;
    }

    /**
     * Returns an array of the first column of all rows.
     */
    public static function getCol($query, $params = [])
    {
        $result = self::getInstance()->executePrepared($query, $params, self::GET_RESULT);
        $output = [];
        while ($row = $result->fetch_array(MYSQLI_NUM)) {
            $output[] = $row[0];
        }
        return $output;
    }

    /**
     * Returns the first row as an associative array, or null if none.
     */
    public static function getFirst($query, $params = [])
    {
        $result = self::getInstance()->executePrepared($query, $params, self::GET_RESULT);
        return $result->fetch_assoc() ?: null;
    }

    /**
     * Returns all rows (assoc arrays).
     */
    public static function getAll($query, $params = [])
    {
        $result = self::getInstance()->executePrepared($query, $params, self::GET_RESULT);
        $output = [];
        while ($row = $result->fetch_assoc()) {
            $output[] = $row;
        }
        return $output;
    }

    /**
     * Executes a query that modifies data; returns affected rows.
     */
    public static function q($query, $params = [])
    {
        return self::getInstance()->executePrepared($query, $params, self::AFFECTED_ROWS);
    }

    public static function insert($table, $data)
    {
        $fields = implode(", ", array_keys($data));
        $placeholders = implode(", ", array_fill(0, count($data), "?"));
        $query = "INSERT INTO {$table} ({$fields}) VALUES ({$placeholders})";

        self::getInstance()->executePrepared($query, array_values($data), self::GET_RESULT);
        return self::getInstance()->conn->insert_id;  // Return last insert ID
    }

    public static function delete($table, $whereClause, $whereParams = [])
    {
        $query = "DELETE FROM {$table} WHERE {$whereClause}";
        self::getInstance()->executePrepared($query, $whereParams, self::AFFECTED_ROWS);
        return self::getInstance()->conn->affected_rows;
    }

    public static function update($table, $data, $whereClause, $whereParams = [])
    {
        $fields = array_keys($data);
        $fieldPlaceholders = implode(" = ?, ", $fields) . " = ?";
        $query = "UPDATE {$table} SET {$fieldPlaceholders} WHERE {$whereClause}";
        $values = array_merge(array_values($data), $whereParams);
        self::getInstance()->executePrepared($query, $values, self::AFFECTED_ROWS);
        return self::getInstance()->conn->affected_rows;
    }

    public static function getDebugLog(): array
    {
        $result = [];
        $debugLog = self::getInstance()->debugLog;
        foreach ($debugLog as $item) {
            $time = number_format($item['cumulative_time'], 4);
            $oneLineQuery = preg_replace('/\s+/', ' ', $item['query']);
            $result[] = "{$item['count']} x  $time  $oneLineQuery";
        }
        // Reverse sort so the last query is on top
        krsort($result);
        return $result;
    }

    public static function upsert($table, $data)
    {
        // Query the schema to determine the unique or primary key fields
        $describeQuery = "SHOW INDEX FROM {$table} WHERE Key_name = 'PRIMARY' OR Non_unique = 0";
        $uniqueFields = [];
        $columns = self::getAll($describeQuery);
        foreach ($columns as $column) {
            $uniqueFields[] = $column['Column_name'];
        }

        // Prepare the WHERE clause and parameters based on unique fields
        $whereClauseParts = [];
        $whereParams = [];
        foreach ($uniqueFields as $field) {
            if (isset($data[$field])) {
                $whereClauseParts[] = "{$field} = ?";
                $whereParams[] = $data[$field];
            }
        }
        $whereClause = implode(' OR ', $whereClauseParts);
        $selectQuery = "SELECT COUNT(*) FROM {$table} WHERE {$whereClause}";
        $existingRowCount = self::getOne($selectQuery, $whereParams);

        if ($existingRowCount === 0) {
            return self::insert($table, $data);
        } else {
            return self::update($table, $data, "{$uniqueFields[0]} = ?", [$data[$uniqueFields[0]]]);
        }
    }

    public static function getTotalQueryTime(): float
    {
        $totalTime = 0.0;
        $debugLog = self::getInstance()->debugLog;
        foreach ($debugLog as $item) {
            $totalTime += $item['cumulative_time'];
        }
        return $totalTime;
    }
}
