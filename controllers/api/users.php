<?php namespace App\api;

use App\Controller;
use App\Db;


class users extends Controller
{

    function addGroupStudents()
    {
        if (empty($_POST['groupName'])) {
            stop(400, 'Invalid groupName');
        }

        if (empty($_POST['students'])) {
            stop(400, 'Empty students');
        }


        $groupId = Db::getOne("SELECT groupId FROM groups WHERE groupName = ?", [$_POST['groupName']]);

        if (!$groupId) {
            stop(400, 'Group not found');
        }

        $newUsers = [];
        foreach ($_POST['students'] as $student) {
            $fullName = $student['fullname'];
            $personalCode = $student['idcode'];

            $existingUserId = Db::getOne("SELECT userId FROM users WHERE userPersonalCode = ?", [$personalCode]);

            if ($existingUserId) {
                Db::update('users', ['userName' => $fullName, 'groupId' => $groupId],'userId = ?', [$existingUserId]);
            } else {

                Db::insert('users', [
                    'userName' => $fullName,
                    'userPersonalCode' => $personalCode,
                    'groupId' => $groupId,
                ]);
                $newUsers[] = $personalCode;
            }
        }

        stop(200, $newUsers);
    }


    function addStudentsEmails()
    {
        if (empty($_POST['usersEmailsData'])) {
            stop(400, 'Invalid usersEmailsData');
        }

        foreach ($_POST['usersEmailsData'] as $emailData) {
            $existingUserId = Db::getOne("SELECT userId FROM users WHERE userPersonalCode = ?", [$emailData['userPersonalCode']]);

            if ($existingUserId) {
                Db::update('users', ['userEmail' => $emailData['userEmail']], 'userId = ?', [$existingUserId]);
            } else {
                stop(400, 'User not found');
            }
        }

        stop(200, 'Emails added');
    }

}
