<?php namespace App;

class intro extends Controller
{

    function index()
    {
        // Set headers to prevent caching and form resubmission issues
        header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
        header('Pragma: no-cache');
        header('Expires: 0');
        
        $this->redirectAdminsToAdminPage();
        $this->redirectTeachersToGradingPage();
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
        // Set headers to prevent caching and form resubmission issues
        header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
        header('Pragma: no-cache');
        header('Expires: 0');
    }

    function cssCourse()
    {
        // Set headers to prevent caching and form resubmission issues
        header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
        header('Pragma: no-cache');
        header('Expires: 0');
    }

    private function redirectAdminsToAdminPage()
    {
        if ($this->auth->userIsAdmin === 1) {
            $this->redirect('admin/index');
        }
    }

    private function redirectTeachersToGradingPage()
    {
        if ($this->auth->userIsTeacher === 1) {
            $this->redirect('grading');
        }
    }

    private function redirectStudentsToSubjectsPage()
    {
        if ($this->auth->groupId && $this->auth->userIsAdmin !== 1) {
            $this->redirect('subjects');
        }
    }

    // Confirmation page logic
    function confirm()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = json_decode(file_get_contents('php://input'), true);
            $errors = [];
            // Expected answers (can be improved to fetch from config or translation)
            $expected = [
                'rule1' => '60 minutit',
                'rule2' => 'koostöö teiste isikutega',
                'rule3' => 'internetti',
            ];
            // Simple contains check for answers
            if (stripos($data['rule1'] ?? '', '60') === false) {
                $errors[] = 'Aja vastus on vale.';
            }
            if (stripos($data['rule2'] ?? '', 'koostöö') === false) {
                $errors[] = 'Keelatud tegevuse vastus on vale.';
            }
            if (stripos($data['rule3'] ?? '', 'internet') === false) {
                $errors[] = 'Lubatud vahendi vastus on vale.';
            }
            if (count($errors) === 0) {
                echo json_encode(['success' => true]);
            } else {
                echo json_encode(['success' => false, 'error' => implode(' ', $errors)]);
            }
            exit;
        }
        // GET: show confirmation page via template system
        $this->action = 'confirm';
        $this->template = 'master';
    }
}
