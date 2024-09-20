<?php namespace App\api;

use App\Activity;
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

            $existingUser = Db::getFirst("SELECT userId, userName FROM users WHERE userPersonalCode = ?", [$personalCode]);

            if ($existingUser) {
                Db::update('users', ['userName' => $fullName, 'groupId' => $groupId],'userId = ?', [$existingUser['userId']]);
                if ($existingUser['userName'] != $fullName) {
                    Activity::create(ACTIVITY_UPDATE_USER, $this->auth->userId, $existingUser['userId'], "Name changed from {$existingUser['userName']} to $fullName");
                }
                if ($existingUser['groupId'] != $groupId) {
                    Activity::create(ACTIVITY_UPDATE_USER, $this->auth->userId, $existingUser['userId'], "Group changed from {$existingUser['groupId']} to $groupId");
                }

            } else {
                $createdUserId = Db::insert('users', [
                    'userName' => $fullName,
                    'userPersonalCode' => $personalCode,
                    'groupId' => $groupId,
                ]);
                $newUsers[] = $personalCode;
                Activity::create(ACTIVITY_ADD_USER, $this->auth->userId, $createdUserId);
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
                Activity::create(ACTIVITY_UPDATE_USER, $this->auth->userId, $existingUserId, "Email changed to {$emailData['userEmail']}");
            } else {
                stop(400, 'User not found');
            }
        }

        stop(200, 'Emails added');
    }

}
