<?php

use App\Db;
use App\Request;
use App\Translation;
use Sentry\State\Scope;
use function Sentry\captureException;
use function Sentry\captureLastError;
use function Sentry\configureScope;

/**
 * Display a fancy error page and quit.
 * @param $error_msg string Error message to show
 * @param int $code HTTP RESPONSE CODE. Default is 500 (Internal server error)
 */
function error_out($error_msg, $code = 500)
{

    // Return HTTP RESPONSE CODE to browser
    header($_SERVER["SERVER_PROTOCOL"] . " $code Something went wrong", true, $code);


    // Set error message
    $errors[] = $error_msg;

    if (Request::isAjax()) {
        stop(400, $error_msg);
    }

    // Show pretty error, too, to humans
    require __DIR__ . '/../templates/error_template.php';


    // Stop execution
    exit();
}

function get_translation_strings($lang)
{
    global $translations;

    // Handle case when current language has been just deleted from the DB
    $translationColumn = !in_array($_SESSION['language'], Translation::languageCodesInUse(false))
        ? "NULL AS translationIn$lang" : "translationIn$lang";

    $translations_raw = Db::getAll("
        SELECT translationPhrase, $translationColumn
        FROM translations");

    foreach ($translations_raw as $item) {
        $translations[$item['translationPhrase']] = $item["translationIn$lang"] === NULL ? $item['translationPhrase']
            : $item["translationIn$lang"];
    }
}

/**
 * Translates the text into currently selected language
 * @param $translationPhrase string The text to be translated
 * @param null $dynamic_source string The source of the translation
 * @return string|null Translated text
 */
function __(string $translationPhrase, $dynamic_source = null): ?string
{
    global $translations;

    $translationPhrase = trim($translationPhrase);

    // We don't want such things ending up in db
    if ($translationPhrase === '') {
        return '';
    }

    // Convert the first letter of the language code to upper case
    $lang = ucfirst($_SESSION['language']);

    // return the original string if there was no language
    if (!$lang) {
        return $translationPhrase;
    }

    // Load translations (only the first time)
    if (empty($translations)) {

        // Return original string if the language does not exist (any more)
        if (!in_array($lang, Translation::languageCodesInUse(true))) {
            return $translationPhrase;
        }
        get_translation_strings($lang);
    }

    // Db does not store more than 765 bytes
    $translationPhrase = substr($translationPhrase, 0, 765);

    // Return the translation if it's there
    if (isset($translations[$translationPhrase])) {

        // Return original string if untranslated
        if ($translations[$translationPhrase] === NULL)
            return $translationPhrase;

        // Else return translated string
        return $translations[$translationPhrase];
    }

    // Right, so we don't have this in our db yet

    // Insert new stub
    Translation::add($translationPhrase, $dynamic_source);

    // And return the original string
    return nl2br($translationPhrase);

}

function stop($code, $data = false)
{
    $response['status'] = $code;

    if ($data) {
        $response['data'] = $data;
    }

    // Change HTTP status code
    http_response_code($code);

    // Add Content-Type header
    header('Content-Type: application/json');

    exit(json_encode($response));
}

function send_error_report($exception): ?\Sentry\EventId
{

    // Get user data from session
    $auth = empty($_SESSION['userId']) ? null : Db::getFirst("select * from users where userId = ?", [$_SESSION['userId']]);

    // Add user data to Sentry
    configureScope(function (Scope $scope) use ($auth): void {
        if (!empty($_SESSION['userId'])) {
            $scope->setUser([
                'auth' => $auth ?? null,
                'session' => $_SESSION ?? null,
                'email' => $auth['email'] ?? null,
            ]);
        }
    });
    // Send error data to Sentry
    $eventCode = captureException($exception);

    captureLastError();

    return $eventCode;
}

function convertWarningsToExceptions(): void
{
    // Convert warnings to exceptions
    set_error_handler(function ($severity, $message, $file, $line) {

        if (!(error_reporting() & $severity)) {

            // This error code is not included in error_reporting
            return;
        }

        throw new ErrorException($message, 0, $severity, $file, $line);

    });
}

function handleProductionError(\Exception $exception){
    $eventCode = send_error_report($exception);
    echo "Juhtus viga.";
    echo " Et saaksime selle viga kiiresti parandada, palun saata meile kirjeldus, mis juhtus.";
    if($eventCode){
        echo " ja see event id: <b>" .  $eventCode . "</b>";
    }
}

function validate($param, $message = 'Invalid parameter value.', $rule = IS_ID, $required = true)
{
    if (!$required && empty($param)) return; // Skip validation if not required and empty

    // If rule is a string, treat it as regex pattern
    if (is_string($rule)) {
        if (!preg_match($rule, $param)) {
            stop(400, $message);
        }
        return $param;
    }

    if (($rule === IS_ID && (!is_numeric($param) || intval($param) <= 0)) ||
        ($rule === IS_INT && !is_numeric($param)) ||
        ($rule === IS_0OR1 && ($param !== '0' && $param !== '1' && $param !== 0 && $param !== 1)) ||
        ($rule === IS_ARRAY && !is_array($param)) ||
        ($rule === IS_STRING && !is_string($param)) ||
        ($rule === IS_DATE && !strtotime($param))) {
        stop(400, $message);
    }
    return $param;
}

function isValidID($id): bool
{
    return !!filter_var($id, FILTER_VALIDATE_INT) && $id > 0;
}

function slugify(mixed $studentName)
{
    return mb_strtolower(preg_replace('/[^a-zA-Z0-9]+/', '-', $studentName));
}