<?php namespace App;

class settings extends Controller
{
    public $template = 'master';

    public function view()
    {
        if (!$this->auth->userId) {
            $this->redirect('/');
        }
        $this->template->content = new \View('settings');
    }

    public function updateEmail() {
        if (!$this->auth->userId) {
            error_out('Not logged in', 401);
        }

        $email = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
        $password = filter_input(INPUT_POST, 'password');

        if (!$email) {
            error_out('Invalid email format', 400);
        }

        if (!password_verify($password, $this->auth->userPassword)) {
            error_out('Incorrect password', 401);
        }

        // Update the user's email
        Db::update('users', ['userEmail' => $email], 'userId = ?', [$this->auth->userId]);

        stop(200, 'Email updated successfully');
    }

    public function updatePassword() {
        if (!$this->auth->userId) {
            error_out('Not logged in', 401);
        }

        $currentPassword = filter_input(INPUT_POST, 'current_password');
        $newPassword = filter_input(INPUT_POST, 'new_password');
        $confirmPassword = filter_input(INPUT_POST, 'confirm_password');

        if ($newPassword !== $confirmPassword) {
            error_out('Passwords do not match', 400);
        }

        if (strlen($newPassword) < 8) {
            error_out('Password must be at least 8 characters', 400);
        }

        if (!password_verify($currentPassword, $this->auth->userPassword)) {
            error_out('Current password is incorrect', 401);
        }

        // Update the user's password
        Db::update('users', ['userPassword' => password_hash($newPassword, PASSWORD_DEFAULT)], 'userId = ?', [$this->auth->userId]);

        stop(200, 'Password updated successfully');
    }
} 