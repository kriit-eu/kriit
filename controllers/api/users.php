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

        $usersWithoutEmail = [];
        foreach ($_POST['students'] as $student) {
            $userExternalId = $student['id'];
            $fullName = $student['fullname'];
            $personalCode = $student['idcode'];

            $existingUser = Db::getFirst("SELECT userId, userName, userEmail FROM users WHERE userPersonalCode = ?", [$personalCode]);

            if ($existingUser) {
                try {
                    if (empty($existingUser['userEmail'])) {
                        $usersWithoutEmail[] = $personalCode;
                    }

                    Db::update('users', ['userName' => $fullName, 'groupId' => $groupId], 'userId = ?', [$existingUser['userId']]);
                    if ($existingUser['userName'] != $fullName) {
                        Activity::create(ACTIVITY_UPDATE_USER, $this->auth->userId, $existingUser['userId'], "Name changed from {$existingUser['userName']} to $fullName");
                    }

                    if (!empty($existingUser['groupId']) && $existingUser['groupId'] != $groupId) {
                        Activity::create(ACTIVITY_UPDATE_USER, $this->auth->userId, $existingUser['userId'], "Group changed from {$existingUser['groupId']} to $groupId");
                    }
                } catch (\Exception $e) {
                    stop(400, 'Something went wrong with user updating: ' . $e->getMessage());
                }

            } else {
                try {
                    $createdUserId = Db::insert('users', [
                        'userExternalId' => $userExternalId,
                        'userName' => $fullName,
                        'userPersonalCode' => $personalCode,
                        'groupId' => $groupId,
                    ]);
                    $usersWithoutEmail[] = $personalCode;
                    Activity::create(ACTIVITY_ADD_USER, $this->auth->userId, $createdUserId);
                } catch (\Exception $e) {
                    stop(400, 'Something went wrong with user adding: ' . $e->getMessage());
                }
            }
        }

        stop(200, $usersWithoutEmail);
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
