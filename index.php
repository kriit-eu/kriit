<?php namespace App;

use function header;

try {
    header("Access-Control-Allow-Origin: *");
    header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
    header("Access-Control-Allow-Headers: Content-Type, Authorization");
    if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
        exit(0);
    }

    require 'system/functions.php';
    require 'constants.php';

    convertWarningsToExceptions();

    // Init composer auto-loading
    if (!@include_once("vendor/autoload.php")) {
        exit('Run composer install');
    }

    date_default_timezone_set(DEFAULT_TIMEZONE);

    // Load config
    if (!include('config.php')) {
        $errors[] = 'No config.php. Please make a copy of config.sample.php and name it config.php and configure it.';
        require 'templates/error_template.php';
        exit();
    }

    // Default env is development
    if (!defined('ENV')) define('ENV', ENV_DEVELOPMENT);

    // Load sentry
    require 'templates/partials/sentry.php';

    new Application;

} catch (\mysqli_sql_exception $e) { // Catch DatabaseException specifically
    // Set http status code to 500
    http_response_code(500);

    if (ENV == ENV_PRODUCTION) {
        handleProductionError($e);
    } else {
        Db::displayError($e);
    }

    exit();

} catch (\Exception $e) {
    http_response_code(500);

    if (ENV == ENV_PRODUCTION) {
        handleProductionError($e);
        exit();
    }

    // Extract code context around the error location
    $snippet = [];
    if ($fileLines = file_exists($e->getFile()) ? file($e->getFile()) : []) {
        $start = max(0, $e->getLine() - 5);
        $end = min(count($fileLines), $e->getLine() + 5);
        for ($i = $start; $i < $end; $i++) {
            $snippet[$i + 1] = $fileLines[$i];
        }
    }

    // Process stack trace
    $stackTrace = array_map(function($trace) {
        $processedTrace = [
            'callString' => '',
            'file' => $trace['file'] ?? null,
            'line' => $trace['line'] ?? null
        ];

        // Build the function call string
        if (isset($trace['class'])) {
            $processedTrace['callString'] .= $trace['class'] . $trace['type'];
        }
        $processedTrace['callString'] .= $trace['function'];

        // Add function arguments if available
        if (!empty($trace['args'])) {
            $args = array_map(function ($arg) {
                if (is_object($arg)) {
                    return get_class($arg);
                } elseif (is_array($arg)) {
                    return 'array(' . count($arg) . ')';
                } elseif (is_string($arg)) {
                    return '"' . (strlen($arg) > 50 ? substr($arg, 0, 47) . '...' : $arg) . '"';
                } elseif (is_bool($arg)) {
                    return $arg ? 'true' : 'false';
                } elseif (is_null($arg)) {
                    return 'null';
                }
                return (string)$arg;
            }, $trace['args']);
            $processedTrace['callString'] .= '(' . implode(', ', $args) . ')';
        } else {
            $processedTrace['callString'] .= '()';
        }

        return $processedTrace;
    }, $e->getTrace());

    // Rest of the variables preparation
    $localVariables = $GLOBALS['vars'] ?? [];
    ksort($localVariables);
    unset($localVariables['_SERVER']['HTTP_COOKIE']);
    unset($localVariables['_ENV']);

    $relativePath = str_replace(dirname(__DIR__) . '/', '', dirname($e->getFile()) . '/');
    $relativeFullPath = str_replace(dirname(__DIR__) . '/', '', $e->getFile()) . ':' . $e->getLine();
    $errorLine = $e->getLine();
    $errorMessage = $e->getMessage();
    $pathInfo = pathinfo($e->getFile());

    require 'templates/error_debug_template.php';
    exit();
}
