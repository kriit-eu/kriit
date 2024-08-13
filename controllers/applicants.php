<?php namespace App;

class applicants extends Controller
{
    public $template = 'admin';

    function index()
    {
        $this->users = Db::getAll("
            SELECT
                u.*,
                COUNT(userDoneExercises.exerciseId) AS userExercisesDone,
                MIN(a.activityLogTimestamp) AS userFirstLogin
            FROM
                users u
            LEFT JOIN
                activityLog a
                ON u.userId = a.userId
                AND a.activityId = 1
            LEFT JOIN userDoneExercises ON u.userId = userDoneExercises.userId
            WHERE
                u.userIsAdmin = 0
            GROUP BY
                u.userId");
    }

    function view()
    {
        $userId = $this->getId();
        $this->user = Db::getFirst("SELECT * FROM users WHERE userId = '{$userId}'");
    }

}
