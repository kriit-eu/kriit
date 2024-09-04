<?php namespace App;

class intro extends Controller
{

    function index()
    {
        $this->redirectAdminsToAdminPage();
        $this->redirectTeachersToSubjectsPage();
        $this->redirectStudentsToSubjectsPage();
        $this->skipIntroIfTimerStartedOrAdmin();
    }

    public function skipIntroIfTimerStartedOrAdmin(): void
    {
        $userTimeUpAt = Db::getOne("SELECT userTimeUpAt FROM users WHERE userId={$this->auth->userId}");
        $this->timeLeft = ($this->auth->userIsAdmin === 1 || $userTimeUpAt === null) ? null : strtotime($userTimeUpAt) - time();

        if ($this->timeLeft > 0 || $this->auth->userIsAdmin === 1) {
            $this->redirect('exercises');
        }

        // Redirect to /timeup if time is up
        if ($this->timeLeft !== null && $this->timeLeft <= 0) {
            $this->redirect('exercises/timeup');
        }
    }

    function htmlCourse()
    {

    }

    function cssCourse()
    {

    }

    private function redirectAdminsToAdminPage()
    {
        if ($this->auth->userIsAdmin === 1) {
            $this->redirect('admin/index');
        }
    }

    private function redirectTeachersToSubjectsPage()
    {
        if ($this->auth->userIsTeacher === 1) {
            $this->redirect('subjects');
        }
    }

    private function redirectStudentsToSubjectsPage()
    {
        if ($this->auth->groupId && $this->auth->userIsAdmin !== 1) {
            $this->redirect('subjects');
        }
    }
}
