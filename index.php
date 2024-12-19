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

} catch (\Exception $e) { // General exception handling
    http_response_code(500);

    if (ENV == ENV_PRODUCTION) {
        handleProductionError($e);
        exit();
    }

    // Custom error page for development environment
    $errorFile = $e->getFile();
    $errorLine = $e->getLine();
    $errorMessage = $e->getMessage();
    
    // Get the code snippet around the error
    $fileContent = file_exists($errorFile) ? file($errorFile) : [];
    $snippet = [];
    if ($fileContent) {
        $start = max(0, $errorLine - 5);
        $end = min(count($fileContent), $errorLine + 5);
        for ($i = $start; $i < $end; $i++) {
            $snippet[$i + 1] = $fileContent[$i];
        }
    }

    // Display custom error page
    require 'templates/error_debug_template.php';
    exit();
}
