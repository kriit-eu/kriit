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
        validate($_POST['userPersonalCode'], 'Isikukood peab koosnema 11st numberist.', '/^\d{11}$/');
        $userPersonalCode = $_POST['userPersonalCode'];
        $user = Db::getFirst("SELECT userIsAdmin, userIsTeacher, groupId, userPassword
                          FROM users
                          WHERE users.userPersonalCode = ?
                          AND userDeleted = 0", [$userPersonalCode]);
        stop(200, empty($user) ? ['User not found'] : [
            'user' => [
                'groupId' => $user['groupId'],
                'userIsAdmin' => $user['userIsAdmin'],
                'userIsTeacher' => $user['userIsTeacher'],
                'isPasswordSet' => !empty($user['userPassword'])
            ]
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
