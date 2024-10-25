<?php namespace App;

/**
 * Created by PhpStorm.
 * User: hennotaht
 * Date: 7/29/13
 * Time: 21:48
 */
class logout extends Controller
{
    public $requires_auth = false;

    function index()
    {
        // Attempt to log out the user
        if (isset($_SESSION['userId'])) {
            $userId = $_SESSION['userId'];
            session_destroy();
            Activity::create(ACTIVITY_LOGOUT, $userId);
        }
        // Redirect to the login page
        header('Location: ' . BASE_URL);
        exit();
    }
}