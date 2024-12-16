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
        $userId = isset($_SESSION['userId']) ? $_SESSION['userId'] : null;
        
        session_destroy();
        
        if ($userId) {
            Activity::create(ACTIVITY_LOGOUT, $userId);
        }
        
        header('Location: ' . BASE_URL);
        exit();
    }
}