<?php namespace App;


/**
 * Class auth authenticates user and permits to check if the user has been logged in
 * Automatically loaded when the controller has $requires_auth property.
 */
#[\AllowDynamicProperties]
class Auth
{

    public int $userId;
    public bool $logged_in = FALSE;
    public bool $userIsAdmin;
    public string $userName;

    function __construct()
    {
        // Check if this is an API request with Authorization header
        if (isset($_SERVER['HTTP_AUTHORIZATION'])) {
            if (!str_starts_with($_SERVER['HTTP_AUTHORIZATION'], 'Bearer ')) {
                stop(400, 'Invalid Authorization header format. Must start with "Bearer "');
            }

            $api_key = substr($_SERVER['HTTP_AUTHORIZATION'], 7);
            if (empty($api_key)) {
                stop(400, 'API key is missing');
            }

            $user = Db::getFirst("SELECT * FROM users WHERE userApiKey = ?", [$api_key]);

            if (empty($user) || $user['userApiKey'] !== $api_key) {
                stop(403, 'API key is invalid');
            }

            $this->load_user_data($user);
            return;
        }

        // Regular session-based authentication
        if (isset($_SESSION['userId'])) {
            $user = Db::getFirst("SELECT *
                               FROM users
                               WHERE userId = '{$_SESSION['userId']}'");

            if (!empty($user)) {
                $this->load_user_data($user);
            }
        }
    }

    /**
     * Dynamically add all user table fields as object properties to auth object
     * @param $user
     */
    public
    function load_user_data($user)
    {

        foreach ($user as $user_attr => $value) {
            $this->$user_attr = $value;
        }
        $this->logged_in = TRUE;
        
        // Define USER_ID constant after successful login
        if (!defined('USER_ID')) {
            define('USER_ID', $user['userId']);
        }
    }

    /**
     * Verifies if the user is logged in and authenticates if not and POST contains username, else displays the login form
     * @return bool Returns true when the user has been logged in
     */
    function require_auth()
    {

        // If user has already logged in...
        if ($this->logged_in) {
            return TRUE;
        }

        // Not all credentials were provided
        if (!(isset($_POST['userPersonalCode']))) {
            $this->show_login();
        }

        // Attempt to retrieve user data from database
        $user = Db::getFirst("SELECT *
                           FROM users
                           WHERE userPersonalCode = ?
                           AND userDeleted = 0", [$_POST['userPersonalCode']]);

        // Show login again if user is not found in the database
        if (empty($user['userId'])) {
            $this->show_login(['Tundmatu isik']);
        }

        // Show login again if user has already completed the exercises
        if ($user['userTimeUpAt'] !== null && strtotime($user['userTimeUpAt']) < time()) {
            $this->show_login(['Katsed on juba sooritatud!']);
        }

        // Show login again if user is admin and password is wrong
        if (($user['userIsAdmin'] || $user['userIsTeacher'] || $user['groupId']) && !empty($user['userPassword']) && !password_verify($_POST['userPassword'], $user['userPassword'])) {
            $this->show_login(['Vale parool'], $_POST['userPersonalCode']);
        }

        if (!$user['userIsAdmin'] && defined('ALLOWED_IP_ADDRESSES') && !in_array($_SERVER['REMOTE_ADDR'], constant('ALLOWED_IP_ADDRESSES'))) {
            $this->show_login(["Sellelt IP aadressilt ($_SERVER[REMOTE_ADDR]) pole teil õigust logida sisse"]);
        }

        if (($user['groupId']|| $user['userIsTeacher']) && empty($user['userPassword'])) {
            $user['userPassword'] = password_hash($_POST['userPassword'], PASSWORD_DEFAULT);
            Db::update('users', $user, 'userId = ?', [$user['userId']]);
        }


        // User has provided correct login data if we are here
        User::login($user['userId']);

        // Load $this->auth with users table's field values
        $this->load_user_data($user);

        // Implement Post-Redirect-Get pattern to prevent form resubmission issues
    // Always redirect to /exercises after login
    header("Location: " . BASE_URL . 'exercises');
    exit();
    }

    /**
     * @param $errors
     */
    protected function show_login($errors = null)
    {
        // Display the login form
        require 'templates/auth_template.php';

        // Prevent loading the requested controller (not authenticated)
        exit();
    }


}
