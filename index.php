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

    // To see the error message in dev
    if (ENV == ENV_PRODUCTION) {
        handleProductionError($e);
        exit();
    }

    // Throw the exception to the screen for the developer to see
    http_response_code(500);
    throw $e;
}