<?php namespace App;

/**
 * Created by PhpStorm.
 * User: hennotaht
 * Date: 7/29/13
 * Time: 21:48
 */
class users extends Controller
{
    public $requires_auth = false;

    function check()
    {
        validate($_POST['userPersonalCode']);
        $userPersonalCode = $_POST['userPersonalCode'];
        stop(200, [
            'user' => Db::getFirst("SELECT userIsAdmin, userIsTeacher
                           FROM users
                           WHERE users.userPersonalCode = ?
                           AND userDeleted = 0", [$userPersonalCode])
        ]);
    }

    function checkPassword()
    {
        $user = Db::getFirst("SELECT *
                           FROM users
                           WHERE userPersonalCode = ?
                           AND userDeleted = 0", [$_POST['userPersonalCode']]);

        stop(200, [
            'isCorrectPassword' => password_verify($_POST['userPassword'], $user['userPassword'])
        ]);
    }
}
