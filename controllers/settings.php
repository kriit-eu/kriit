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
            error_out('Pole sisse logitud', 401);
        }

        $email = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
        $password = filter_input(INPUT_POST, 'password');

        if (!$email) {
            error_out('Vigane e-posti formaat', 400);
        }

        if (!password_verify($password, $this->auth->userPassword)) {
            error_out('Vale parool', 401);
        }

        // Get current email for logging
        $oldEmail = $this->auth->userEmail;

        // Update the user's email
        Db::update('users', ['userEmail' => $email], 'userId = ?', [$this->auth->userId]);

        // Log the email change
        Activity::create(
            \ACTIVITY_CHANGE_EMAIL,
            $this->auth->userId,
            $this->auth->userId,
            "Muutis e-posti aadressi '$oldEmail' -> '$email'"
        );

        stop(200, 'E-posti aadress edukalt uuendatud');
    }

    public function updatePassword() {
        if (!$this->auth->userId) {
            error_out('Pole sisse logitud', 401);
        }

        $currentPassword = filter_input(INPUT_POST, 'current_password');
        $newPassword = filter_input(INPUT_POST, 'new_password');
        $confirmPassword = filter_input(INPUT_POST, 'confirm_password');

        if ($newPassword !== $confirmPassword) {
            error_out('Paroolid ei kattu', 400);
        }

        if (strlen($newPassword) < 8) {
            error_out('Parool peab olema vähemalt 8 tähemärki pikk', 400);
        }

        if (!password_verify($currentPassword, $this->auth->userPassword)) {
            error_out('Vale parool', 401);
        }

        // Update the user's password
        Db::update('users', ['userPassword' => password_hash($newPassword, PASSWORD_DEFAULT)], 'userId = ?', [$this->auth->userId]);

        // Log the password change
        Activity::create(
            ACTIVITY_CHANGE_PASSWORD,
            $this->auth->userId,
            $this->auth->userId,
            'Muutis parooli'
        );

        stop(200, 'Parool edukalt uuendatud');
    }
} 