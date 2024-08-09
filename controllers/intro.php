<?php namespace App;

class intro extends Controller
{

    function index()
    {
        $this->skipIntroIfTimerStartedOrAdmin();
        $this->users = Db::getAll("SELECT * FROM users");
    }

    public function skipIntroIfTimerStartedOrAdmin(): void
    {
        $userTimeUpAt = Db::getOne("SELECT userTimeUpAt FROM users WHERE userId={$this->auth->userId}");
        $this->timeLeft = ($this->auth->userIsAdmin === 1 || $userTimeUpAt === null) ? null : strtotime($userTimeUpAt) - time();

        if ($this->timeLeft > 0 || $this->auth->userIsAdmin === 1) {
            $this->redirect('exercises');
        }
    }
}
