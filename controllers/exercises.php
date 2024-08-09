<?php namespace App;

class exercises extends Controller
{

    public $auth;

    function __construct($app)
    {
        $userTimeUpAt = Db::getOne("SELECT userTimeUpAt FROM users WHERE userId={$app->auth->userId}");
        $this->timeLeft = ($app->auth->userIsAdmin === 1 || $userTimeUpAt === null) ? null : strtotime($userTimeUpAt) - time();
    }

    function index()
    {
        $this->redirectIfTimeExpiredOrNotStarted();
        $this->exercises = Db::getAll("SELECT * FROM exercises JOIN exerciseStatuses USING(exerciseStatusId)");
    }

    function view()
    {
        $this->redirectIfTimeExpiredOrNotStarted();
        $this->exercise = Db::getFirst("
            SELECT * FROM exercises
            JOIN exerciseStatuses USING(exerciseStatusId)
            WHERE exerciseId = ?", [$this->getId()]);
    }

    function timeup()
    {
        // Check if time is really up and if not, redirect back to /exercises
        if ($this->timeLeft === null || $this->timeLeft > 0) {
            $this->redirect('exercises');
        }
    }

    function start()
    {
        $formattedDateTime = date('Y-m-d H:i:s', strtotime('+60 minutes'));
        Db::update('users', ['userTimeUpAt' => $formattedDateTime], 'userId = ?', [$this->auth->userId]);
        Activity::create(ACTIVITY_START_TIMER, $this->auth->userId);
        stop(200);
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
}
