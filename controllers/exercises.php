<?php namespace App;

use Exception;

class exercises extends Controller
{
    public $auth;

    function __construct($app)
    {
        // Redirect to /login if user is not logged in
        if (empty($_SESSION['userId'])) {
            $this->redirect('/');
        }

        $userTimeUpAt = Db::getOne("SELECT userTimeUpAt FROM users WHERE userId={$app->auth->userId}");
        $this->timeLeft = ($app->auth->userIsAdmin === 1 || $userTimeUpAt === null) ? null : strtotime($userTimeUpAt) - time();
    }

    function index()
    {
        $this->redirectIfTimeExpiredOrNotStarted();
        $this->exercises = Db::getAll("
            SELECT
                exercises.*,
                IF(userDoneExercises.userId IS NOT NULL, 1, 0) AS isSolved
            FROM exercises
            LEFT JOIN userDoneExercises
                ON exercises.exerciseId = userDoneExercises.exerciseId
                AND userDoneExercises.userId = ?
            ORDER BY exerciseId", [$this->auth->userId]);

        $allSolved = array_reduce($this->exercises, function ($carry, $exercise) {
            return $carry && $exercise['isSolved'] === 1;
        }, true);

        if ($allSolved) {
            // create activity that all exercises are solved
            Activity::create(ACTIVITY_ALL_SOLVED, $this->auth->userId);
            $startTimer = Db::getOne("
                SELECT activityLogTimestamp
                FROM activityLog
                WHERE userId = ?
                AND activityId = 3
                ORDER BY activityLogTimestamp DESC
                LIMIT 1", [$this->auth->userId]);
            $spentTimeFormatted = gmdate('H:i:s', time() - strtotime($startTimer));
            Db::update('users', ['userTimeTotal' => $spentTimeFormatted], 'userId = ?', [$this->auth->userId]);
            $this->redirect('exercises/congratulations');
        }
    }

    private function redirectIfTimeExpiredOrNotStarted(): void
    {
        // Redirect to /intro if user is not admin and timer is not started
        if ($this->auth->userIsAdmin !== 1 && $this->auth->userTimeUpAt === null) {
            $this->redirect('intro');
        }

        // Redirect to /exercises/timeup if time is up
        if ($this->auth->userIsAdmin !== 1 && $this->timeLeft <= 0) {
            $this->redirect('exercises/timeup');
        }
    }

    function view()
    {
        $this->redirectIfTimeExpiredOrNotStarted();
        $this->exercise = Db::getFirst("
            SELECT * FROM exercises
            WHERE exerciseId = ?", [$this->getId()]);
    }

    function timeup()
    {
        // Check if time is really up and if not, redirect back to /exercises
        if ($this->timeLeft === null || $this->timeLeft > 0) {
            $this->redirect('exercises');
        }

    }

    function congratulations()
    {
        $userId = $_SESSION['userId'];
        session_destroy();
        Activity::create(ACTIVITY_LOGOUT, $userId);
    }

    function start()
    {
        $formattedDateTime = date('Y-m-d H:i:s', strtotime('+20 minutes'));
        Db::update('users', ['userTimeUpAt' => $formattedDateTime], 'userId = ?', [$this->auth->userId]);
        Activity::create(ACTIVITY_START_TIMER, $this->auth->userId);
        stop(200);
    }

    function AJAX_validate()
    {
        $exerciseId = $this->getId();
        $answer = $_POST['answer'];
        $userId = $this->auth->userId;
        $timestamp = time();

        $nodeScriptDir = __DIR__ . "/../.node_scripts";

        if (!is_dir($nodeScriptDir)) {
            mkdir($nodeScriptDir, 0755, true);
        }

        $tempFilePath = "{$nodeScriptDir}/tempValidation_{$userId}_{$exerciseId}_{$timestamp}.js";

        $exerciseValidationFunction = Db::getOne("SELECT exerciseValidationFunction FROM exercises WHERE exerciseId = ?", [$exerciseId]);
        $escapedAnswer = json_encode($answer);

        $validationScript = "
    const jsdom = require('jsdom');
    const { JSDOM } = jsdom;

    const dom = new JSDOM({$escapedAnswer});
    const window = dom.window;
    const document = window.document;

    {$exerciseValidationFunction}

    console.log(validate());
    ";

        try {
            $this->writeValidationScript($tempFilePath, $validationScript);
            $output = $this->executeNodeScript($tempFilePath);

            if (isset($output[0]) && $output[0] === 'true') {
                Db::insert('userDoneExercises', ['userId' => $userId, 'exerciseId' => $exerciseId]);
                // Create activity
                Activity::create(ACTIVITY_SOLVED_EXERCISE, $userId, $exerciseId);
                $response = ['result' => 'success', 'message' => 'Ülesanne on lahendatud.'];
                $this->cleanupTempFile($tempFilePath);
            } else {
                $response = ['result' => 'fail', 'message' => 'Teie lahendus ei läbinud valideerimist. Palun proovige uuesti.'];
            }
        } catch (Exception $e) {
            $response = ['result' => 'error', 'message' => $e->getMessage()];
        }

        stop(200, $response);
    }

    private function writeValidationScript($filePath, $scriptContent)
    {
        if (file_put_contents($filePath, $scriptContent) === false) {
            throw new Exception("Failed to write validation script to {$filePath}");
        }
    }

    private function executeNodeScript($filePath)
    {
        exec(NODE_EXE . " {$filePath} 2>&1", $output, $return);
        if ($return !== 0) {
            throw new Exception("Node.js script execution failed: " . implode("\n", $output));
        }

        return $output;
    }

    private function cleanupTempFile($filePath)
    {
        try {
            if (file_exists($filePath) && !unlink($filePath)) {
                throw new Exception("Failed to delete temporary file {$filePath}");
            }
        } catch (Exception $e) {
            throw new Exception("An error occurred while deleting the temporary file: " . $e->getMessage());
        }
    }

    function AJAX_markAsSolved()
    {
        $exerciseId = $this->getId();
        $userId = $this->auth->userId;

        try {
            Db::insert('userDoneExercises', ['userId' => $userId, 'exerciseId' => $exerciseId]);
            // Create activity
            Activity::create(ACTIVITY_SOLVED_EXERCISE, $userId, $exerciseId);
        } catch (  Exception $e) {
            // Check if the error is due to duplicate entry
            if ($e->getCode() !== 1062) {
                throw $e;
            }

            Activity::create(ACTIVITY_SOLVED_AGAIN_THE_SAME_EXERCISE, $userId, $exerciseId);
        }


        stop(200);
    }
}
