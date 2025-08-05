<?php namespace App;

use Exception;

class exercises extends Controller
{
    public $auth;
    public $template = 'applicant';

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
        // Set headers to prevent caching
        header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
        header('Pragma: no-cache');
        header('Expires: 0');

        $this->redirectIfTimeExpiredOrNotStarted();
        $this->exercises = Db::getAll(
            "SELECT
                e.*,
                ue.status,
                ue.startTime,
                ue.endTime
            FROM exercises e
            LEFT JOIN userExercises ue
                ON e.exerciseId = ue.exerciseId
                AND ue.userId = ?
            ORDER BY e.exerciseId",
            [$this->auth->userId]
        );

    // Timeout/deadline logic removed: exercises are always available
    unset($exercise); // Unset reference to last element

        $allSolved = array_reduce($this->exercises, function ($carry, $exercise) {
            return $carry && ($exercise['status'] === 'completed');
        }, true);

        if ($allSolved && count($this->exercises) > 0) {
            Activity::create(ACTIVITY_ALL_SOLVED, $this->auth->userId);
            $this->calculateAndUpdateTotalTimeSpent($this->auth->userId);
            $this->redirect('exercises/congratulations');
        }
    }

    private function redirectIfTimeExpiredOrNotStarted(): void
    {
        if ($this->auth->userIsAdmin !== 1 && $this->auth->userTimeUpAt === null) {
            $this->redirect('intro');
        }

        if ($this->auth->userIsAdmin !== 1 && $this->timeLeft <= 0) {
            $this->redirect('exercises/timeup');
        }
    }

    function view()
    {
        $exerciseId = $this->getId();
        $userId = $this->auth->userId;

        $exerciseState = Db::getFirst("SELECT * FROM userExercises WHERE userId = ? AND exerciseId = ?", [$userId, $exerciseId]);

        if ($exerciseState) {
            if ($exerciseState['status'] === 'completed') {
                // For completed, show total time spent
                if (!empty($exerciseState['startTime']) && !empty($exerciseState['endTime'])) {
                    $this->elapsedTime = strtotime($exerciseState['endTime']) - strtotime($exerciseState['startTime']);
                } else {
                    $this->elapsedTime = 0;
                }
                $this->redirect('exercises');
            } elseif ($exerciseState['status'] === 'started') {
                if (!empty($exerciseState['startTime'])) {
                    $this->elapsedTime = time() - strtotime($exerciseState['startTime']);
                } else {
                    $this->elapsedTime = 0;
                }
            } else {
                $this->elapsedTime = 0;
            }
        } else {
            Db::insert('userExercises', [
                'userId' => $userId,
                'exerciseId' => $exerciseId,
                'status' => 'started',
                'startTime' => date('Y-m-d H:i:s'),
                // 'deadline' => null // No deadline
            ]);
            $this->elapsedTime = 0;
        }

        $this->exercise = Db::getFirst("SELECT * FROM exercises WHERE exerciseId = ?", [$exerciseId]);
        Activity::create(ACTIVITY_START_EXERCISE, $userId, $exerciseId);
    }

    function timeup()
    {
        if ($this->timeLeft === null || $this->timeLeft > 0) {
            $this->redirect('exercises');
        }

        // Mark only 'started' exercises as timed_out for this user
        Db::update('userExercises',
            ['status' => 'timed_out'],
            'userId = ? AND status = ?',
            [$this->auth->userId, 'started']
        );

        Activity::create(ACTIVITY_TIME_UP, $this->auth->userId);
        $this->calculateAndUpdateTotalTimeSpent($this->auth->userId);

        $userId = $_SESSION['userId'];
        session_destroy();
    }

    function congratulations()
    {
        $userId = $_SESSION['userId'];
        $this->solvedExercisesCount = Db::getOne("SELECT COUNT(*) FROM userExercises WHERE userId = ? AND status = 'completed'", [$userId]);

        session_destroy();
        Activity::create(ACTIVITY_LOGOUT, $userId);
    }

    function start()
    {
    $formattedDateTime = date('Y-m-d H:i:s', strtotime('+1 hour'));
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
            $this->cleanupTempFile($tempFilePath);

            if (isset($output[0]) && $output[0] === 'true') {
                $this->markExerciseAsSolved($userId, $exerciseId);
                $response = ['result' => 'success', 'message' => 'Ülesanne on lahendatud.'];
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
        $this->markExerciseAsSolved($this->auth->userId, $this->getId());
        stop(200);
    }

    private function markExerciseAsSolved($userId, $exerciseId)
    {
        $exerciseState = Db::getFirst("SELECT * FROM userExercises WHERE userId = ? AND exerciseId = ?", [$userId, $exerciseId]);

        if ($exerciseState) {
            if ($exerciseState['status'] === 'completed') {
                Activity::create(ACTIVITY_SOLVED_AGAIN_THE_SAME_EXERCISE, $userId, $exerciseId);
                return;
            }
            Db::update('userExercises',
                ['status' => 'completed', 'endTime' => date('Y-m-d H:i:s')],
                "userId = ? AND exerciseId = ?",
                [$userId, $exerciseId]
            );
            Activity::create(ACTIVITY_SOLVED_EXERCISE, $userId, $exerciseId);
        } else {
            // This case is unlikely if view() logic is correct, but as a fallback:
            Db::insert('userExercises', [
                'userId' => $userId,
                'exerciseId' => $exerciseId,
                'status' => 'completed',
                'startTime' => date('Y-m-d H:i:s'),
                'endTime' => date('Y-m-d H:i:s')
            ]);
            Activity::create(ACTIVITY_SOLVED_EXERCISE, $userId, $exerciseId);
        }
    }

    private function calculateAndUpdateTotalTimeSpent($userId): void
    {
        // Calculate total time spent
        $startTimer = Db::getOne("
        SELECT activityLogTimestamp
        FROM activityLog
        WHERE userId = ?
        AND activityId = ?
        ORDER BY activityLogTimestamp DESC
        LIMIT 1", [$userId, ACTIVITY_START_TIMER]);

        $spentTimeFormatted = gmdate('H:i:s', time() - strtotime($startTimer));

        // Update user's total time spent
        Db::update('users', ['userTimeTotal' => $spentTimeFormatted], 'userId = ?', [$userId]);
    }
}
