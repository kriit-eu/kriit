<?php namespace App;

class applicants extends Controller
{
    public $template = 'admin';

    function index()
    {
        $this->users = Db::getAll("
            SELECT
                u.*,
                (SELECT COUNT(ud.exerciseId) FROM userDoneExercises ud WHERE ud.userId = u.userId) AS userExercisesDone,
                MIN(a1.activityLogTimestamp) AS userFirstLogin,
                MAX(a2.activityLogTimestamp) AS userStartTimer
            FROM
                users u
            LEFT JOIN activityLog a1 ON u.userId = a1.userId AND a1.activityId = 1
            LEFT JOIN activityLog a2 ON u.userId = a2.userId AND a2.activityId = 3
            WHERE
                u.userIsAdmin = 0
            GROUP BY
                u.userId
        ");
    }

    function view()
    {
        $userId = $this->getId();
        $this->user = Db::getFirst("SELECT * FROM users WHERE userId = '{$userId}'");
    }

}
