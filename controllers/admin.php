<?php namespace App;

use HTMLPurifier;
use HTMLPurifier_Config;

class admin extends Controller
{
    public $requires_auth = true;
    public $requires_admin = true;
    public $template = 'admin';

    public function translations()
    {
        $languageCodesInUse = Translation::languageCodesInUse(true);
        $showUntranslated = empty($_GET['showUntranslated']) ? [] : explode(',', $_GET['showUntranslated']);
        $this->languagesInUse = Translation::getLanguagesByCode($languageCodesInUse);
        $this->languagesNotInUse = Translation::getLanguagesByCode($languageCodesInUse, true);
        $this->showUntranslated = array_flip($showUntranslated);
        $this->translations = Translation::getUntranslated($showUntranslated);
        $this->statistics = Translation::getStatistics();
        $this->translations_where_phrase_is_too_long = Translation::get(['LENGTH(translationPhrase) >= 765']);

    }

    public function AJAX_translationEdit()
    {

        if (empty($_POST['translationId']) || !is_numeric($_POST['translationId']) || $_POST['translationId'] <= 0) {
            stop(400, 'Invalid translationId');
        }
        if (!Translation::isValidLanguageCode($_POST['languageCode'])) {
            stop(400, "Invalid languageCode");
        }
        if ($this->htmlIsValid($_POST['translation'])) {
            stop(400, "Invalid HTML");
        }

        Db::update('translations', [
            "translationIn$_POST[languageCode]" => $_POST['translation']
        ], "translationId = $_POST[translationId]");
    }

    public function AJAX_translationAddLanguage()
    {

        if (!Translation::isValidLanguageCode($_POST['languageCode'])) {
            stop(400, "Invalid languageCode");
        }

        /** @noinspection PhpUnhandledExceptionInspection */
        Translation::addLanguage($_POST['languageCode']);

        Translation::googleTranslateMissingTranslations($_POST['languageCode']);
    }

    public function AJAX_translationDeleteLanguage()
    {

        if (!Translation::isValidLanguageCode($_POST['languageCode'])) {
            stop(400, "Invalid languageCode");
        }

        /** @noinspection PhpUnhandledExceptionInspection */
        Translation::deleteLanguage($_POST['languageCode']);

    }

    public function AJAX_translateRemainingStrings()
    {
        if (!Translation::isValidLanguageCode($_POST['languageCode'])) {
            stop(400, "Invalid languageCode");
        }
        Translation::googleTranslateMissingTranslations($_POST['languageCode']);
        $stats = Translation::getStatistics([$_POST['languageCode']]);

        // Return remaining untranslated string count
        stop(200, ['untranslatedCount' => $stats[$_POST['languageCode']]['remaining']]);

    }

    function index()
    {
        header('Location: ' . BASE_URL . 'applicants');
        exit();
    }

    function ranking()
    {
        $this->users = Db::getAll("
            SELECT
                u.*,
                COUNT(userDoneExercises.exerciseId) AS userExercisesDone,
                MIN(a.activityLogTimestamp) AS userFirstLogin,
                ROW_NUMBER() OVER (ORDER BY u.userTimeTotal DESC, COUNT(userDoneExercises.exerciseId) DESC) AS userRank
            FROM
                users u
            LEFT JOIN
                activityLog a ON u.userId = a.userId AND a.activityId = 1
            LEFT JOIN
                userDoneExercises ON u.userId = userDoneExercises.userId
            WHERE
                u.userIsAdmin = 0
            GROUP BY
                u.userId
            ORDER BY
                userRank ASC
        ");

    }

    function users()
    {
        $this->users = Db::getAll("
            SELECT
                u.*,
                MIN(a.activityLogTimestamp) AS userFirstLogin
            FROM
                users u
            LEFT JOIN
                activityLog a
                ON u.userId = a.userId
                AND a.activityId = 1
            WHERE
                u.userIsAdmin = 1
            GROUP BY
                u.userId");
    }

    function logs()
    {
        $this->log = Activity::logs();
    }

    function validatePersonalCode($personalCode)
    {
        // Регулярное выражение для проверки формата isikukood
        $pattern = '/^[1-6]\d{2}(0[1-9]|1[0-2])(0[1-9]|[12]\d|3[01])\d{4}$/';

        // Проверка соответствия регулярному выражению
        if (!preg_match($pattern, $personalCode)) {
            return false; // Не соответствует формату
        }

        // Проверка контрольного числа
        $multipliers1 = [1, 2, 3, 4, 5, 6, 7, 8, 9, 1];
        $multipliers2 = [3, 4, 5, 6, 7, 8, 9, 1, 2, 3];
        $sum = 0;

        // Первая попытка проверки
        for ($i = 0; $i < 10; $i++) {
            $sum += intval($personalCode[$i]) * $multipliers1[$i];
        }

        $mod = $sum % 11;

        // Если модуль 10, делаем вторую проверку
        if ($mod === 10) {
            $sum = 0;
            for ($i = 0; $i < 10; $i++) {
                $sum += intval($personalCode[$i]) * $multipliers2[$i];
            }

            $mod = $sum % 11;

            if ($mod === 10) {
                $mod = 0; // Если снова 10, контрольное число должно быть 0
            }
        }

        // Проверка последнего символа isikukood
        return $mod === intval($personalCode[10]);
    }


    function AJAX_addUser()
    {
        if (empty($_POST['userName'])) {
            stop(400, 'Nimi ei saa olla tühi');
        }
        if (empty($_POST['userPassword'])) {
            stop(400, 'Parool ei saa olla tühi');
        }

        if (empty($_POST['userPersonalCode'])) {
            stop(400, "Isikukood on kohustuslik");
        }

        $userPersonalCode = $_POST['userPersonalCode'];

        if (!$this->validatePersonalCode($userPersonalCode)) {
            stop(400, "Isikukood ei vasta nõuetele");
        }

        if (User::get(["userPersonalCode = '$userPersonalCode'"])) {
            stop(409, "Administraator selle isikukoodiga on juba olemas");
        }

        $userName = addslashes($_POST['userName']);
        if (User::get(["userName = '$userName'"])) {
            stop(409, __('User already exists'));
        }

        $data = [
            'userName' => $_POST['userName'],
            'userPersonalCode' => $_POST['userPersonalCode'],
            'userPassword' => password_hash($_POST['userPassword'], PASSWORD_DEFAULT),
            'userIsAdmin' => 1
        ];

        $userId = Db::insert('users', $data);
        stop(200, ['userId' => $userId]);
    }

    function AJAX_addApplicant()
    {
        if (empty($_POST['userName'])) {
            stop(400, "Nimi on kohustuslik");
        }
        if (empty($_POST['userPersonalCode'])) {
            stop(400, "Isikukood on kohustuslik");
        }

        $userPersonalCode = $_POST['userPersonalCode'];

        if (!$this->validatePersonalCode($userPersonalCode)) {
            stop(400, "Isikukood ei vasta nõuetele");
        }

        if (User::get(["userPersonalCode = '$userPersonalCode'"])) {
            stop(409, "Kandidaat selle isikukoodiga on juba olemas");
        }

        $data = [
            'userName' => $_POST['userName'],
            'userPersonalCode' => $_POST['userPersonalCode']
        ];

        $userId = Db::insert('users', $data);

        stop(200, ['userId' => $userId]);
    }

    function AJAX_editUser()
    {
        if (empty($_POST['userId']) || !is_numeric($_POST['userId'])) {
            stop(400, 'Invalid' . ' userId');
        }

        if (empty($_POST['userPersonalCode'])) {
            stop(400, "Isikukood on kohustuslik");
        }

        $userId = $_POST['userId'];
        $userPersonalCode = $_POST['userPersonalCode'];

        if (!$this->validatePersonalCode($userPersonalCode)) {
            stop(400, "Isikukood ei vasta nõuetele");
        }

        $existingUser = User::get(["userPersonalCode = '$userPersonalCode'"]);
        if ($existingUser && isset($existingUser['userId']) && $existingUser['userId'] != $userId) {
            stop(409, "Administraator selle isikukoodiga on juba olemas");
        }

        // Remove empty password from $_POST or hash it
        if (empty($_POST['userPassword'])) {
            unset($_POST['userPassword']);
        } else {
            $_POST['userPassword'] = password_hash($_POST['userPassword'], PASSWORD_DEFAULT);
        }

        User::edit($userId, $_POST);

        stop(200);
    }

    function AJAX_editApplicant()
    {
        if (empty($_POST['userId']) || !is_numeric($_POST['userId'])) {
            stop(400, 'Invalid' . ' userId');
        }

        if (empty($_POST['userPersonalCode'])) {
            stop(400, "Isikukood on kohustuslik");
        }

        $userId = $_POST['userId'];
        $userPersonalCode = $_POST['userPersonalCode'];

        if (!$this->validatePersonalCode($userPersonalCode)) {
            stop(400, "Isikukood ei vasta nõuetele");
        }

        $existingUser = User::get(["userPersonalCode = '$userPersonalCode'"]);
        if ($existingUser && isset($existingUser['userId']) && $existingUser['userId'] != $userId) {
            stop(409, "Kandidaat selle isikukoodiga on juba olemas");
        }

        User::edit($userId, $_POST);

        stop(200);
    }


    function AJAX_deleteUser()
    {
        if (empty($_POST['userId']) || !is_numeric($_POST['userId'])) {
            stop(400, 'Invalid' . ' userId');
        }

        if ($_POST['userId'] == $this->auth->userId) {
            stop(403, __('You cannot delete yourself'));
        }

        User::delete($_POST['userId']);

        stop(200);
    }

    function AJAX_getUser()
    {
        if (empty($_POST['userId']) || !is_numeric($_POST['userId'])) {
            stop(400, 'Invalid' . ' userId');
        }

        stop(200, Db::getFirst("SELECT userIsAdmin,userPersonalCode,userName FROM users WHERE userId = $_POST[userId]"));
    }

    public function htmlIsValid($html): bool
    {

        $config = HTMLPurifier_Config::createDefault();
        $config->set('Core.Encoding', 'UTF-8');
        $purifier = new HTMLPurifier($config);
        $pure_html = $purifier->purify($html);

        return !!strcmp($html, $pure_html);

    }

    function exercises()
    {
        $this->exercises = Db::getAll("SELECT * FROM exercises");
    }

    function AJAX_editExercise()
    {
        $exerciseId = $_POST['id'];

        // Update only the fields that are present in the request
        if (isset($_POST['exercise_name'])) {
            Db::update('exercises', ['exerciseName' => $_POST['exercise_name']], 'exerciseId = ?', [$exerciseId]);
        }

        if (isset($_POST['instructions'])) {
            Db::update('exercises', ['exerciseInstructions' => $_POST['instructions']], 'exerciseId = ?', [$exerciseId]);
        }

        if (isset($_POST['initial_code'])) {
            Db::update('exercises', ['exerciseInitialCode' => $_POST['initial_code']], 'exerciseId = ?', [$exerciseId]);
        }

        if (isset($_POST['validation_function'])) {
            Db::update('exercises', ['exerciseValidationFunction' => $_POST['validation_function']], 'exerciseId = ?', [$exerciseId]);
        }

        stop(200);
    }

    function AJAX_deleteExercise()
    {
        if (empty($_POST['id']) || !is_numeric($_POST['id'])) {
            stop(400, 'Invalid exercise id');
        }

        // First delete all done exercises for this exercise
        Db::delete('userDoneExercises', 'exerciseId = ?', [$_POST['id']]);

        Db::delete('exercises', 'exerciseId = ?', [$_POST['id']]);

        stop(200);
    }

    function AJAX_createExercise()
    {
        // Ensure that the exercise name is provided
        if (empty($_POST['exercise_name'])) {
            stop(400, 'Exercise name is required.');
        }

        // Collect the data for insertion
        $data = [
            'exerciseName' => $_POST['exercise_name'],
            'exerciseInstructions' => isset($_POST['instructions']) ? $_POST['instructions'] : '',
            'exerciseInitialCode' => isset($_POST['initial_code']) ? $_POST['initial_code'] : '',
            'exerciseValidationFunction' => isset($_POST['validation_function']) ? $_POST['validation_function'] : '',
        ];

        // Insert the new exercise
        $exerciseId = Db::insert('exercises', $data);

        // Return the new exercise ID to the frontend
        stop(200, ['id' => $exerciseId]);
    }

}
