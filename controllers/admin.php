<?php

namespace App;

use HTMLPurifier;
use HTMLPurifier_Config;

class admin extends Controller
{
    public $requires_auth = true;
    public $requires_admin = true;
    public $template = 'admin';


    function index()
    {
        header('Location: ' . BASE_URL . 'applicants');
        exit();
    }

    function ranking(): void
    {
        // Fetch all users
        $allUsers = Db::getAll("
        SELECT
            u.*,
            COUNT(DISTINCT ue.exerciseId) AS userExercisesDone,
            MIN(a.activityLogTimestamp) AS userFirstLogin,
            ROW_NUMBER() OVER (
                ORDER BY
                    COUNT(DISTINCT ue.exerciseId) DESC,
                    CASE
                        WHEN u.userTimeTotal IS NOT NULL THEN 0
                        ELSE 1
                    END ASC,
                    u.userTimeTotal ASC,
                    u.userId ASC
            ) AS userRank
        FROM
            users u
        LEFT JOIN
            activityLog a ON u.userId = a.userId AND a.activityId = 1
        LEFT JOIN
            userExercisesWithComputedStatus ue ON u.userId = ue.userId AND ue.status = 'completed'
        WHERE
            u.userIsAdmin = 0
            AND u.userIsTeacher = 0
            AND u.groupId IS NULL
        GROUP BY
            u.userId
        ORDER BY
            userRank ASC;
    ");

        // Filter users who have completed at least one task and are not admins
        $this->filteredUsers = array_filter($allUsers, function ($user) {
            return $user['userExercisesDone'] > 0;
        });

        $this->users = $allUsers;  // Keep all users for ranking display

        // Calculate the average number of solved tasks among filtered users
        $totalSolvedTasks = array_sum(array_column($this->filteredUsers, 'userExercisesDone'));
        $userCount = count($this->filteredUsers);
        $this->averageExercisesDone = $userCount > 0 ? $totalSolvedTasks / $userCount : 0;
    }

    function groups(): void
    {
        $this->groups = Db::getAll("
            SELECT
                groups.groupId,
                groups.groupName
            FROM groups
            ORDER BY groups.groupName");
    }


    function users(): void
    {
        $this->users = Db::getAll("
        SELECT
            u.*,
            g.groupName
        FROM
            users u
        LEFT JOIN
            groups g ON u.groupId = g.groupId
        GROUP BY
            u.userName, g.groupName
        ORDER BY  g.groupName, u.userName");

        $this->groups = Db::getAll("SELECT * FROM groups");
    }

    function subjects(): void
    {
        $this->subjects = Db::getAll("
        SELECT
            subjects.subjectId,
            subjects.subjectName,
            subjects.teacherId,
            subjects.subjectExternalId,
            subjects.groupId,
            groups.groupName AS subjectGroup,
            users.userName AS teacherName
        FROM
            subjects
        LEFT JOIN
            groups ON subjects.groupId = groups.groupId
        LEFT JOIN
            users ON subjects.teacherId = users.userId
        ORDER BY
            subjects.subjectName ASC, groups.groupName ASC
        ");

        $this->teachers = Db::getAll("SELECT * FROM users WHERE userIsTeacher = 1");
        $this->groups = Db::getAll("SELECT * FROM groups");
    }


    function logs(): void
    {
        $this->log = Activity::logs();
    }

    function subjects_view(): void
    {
        //Ger all assignments for the subject
        $this->assignments = Db::getAll("SELECT * FROM assignments WHERE subjectId = ?", [$this->getId()]);
    }

    function groups_view(): void
    {
        $data = Db::getAll("
                SELECT
                    u.userId, u.userName, u.groupId, u.userPersonalCode, u.userExternalId, u.userEmail, g.groupName
                FROM users u
                LEFT JOIN groups g ON u.groupId = g.groupId
                WHERE g.groupId = ?
                ORDER BY g.groupName
            ", [$this->getId()]);

        $group = [
            'groupId' => $this->getId(),
            'groupName' => null,
            'students' => []
        ];

        foreach ($data as $student) {
            $group['groupName'] = $student['groupName'];
            $group['students'][] = $student;
        }

        $this->group = $group;
    }

    function validatePersonalCode($personalCode): bool
    {
        $pattern = '/^[1-9]\d{2}(0[1-9]|1[0-2])(0[1-9]|[12]\d|3[01])\d{4}$/';

        if (!preg_match($pattern, $personalCode)) {
            return false;
        }

        $multipliers1 = [1, 2, 3, 4, 5, 6, 7, 8, 9, 1];
        $multipliers2 = [3, 4, 5, 6, 7, 8, 9, 1, 2, 3];
        $sum = 0;

        for ($i = 0; $i < 10; $i++) {
            $sum += intval($personalCode[$i]) * $multipliers1[$i];
        }

        $mod = $sum % 11;

        if ($mod === 10) {
            $sum = 0;
            for ($i = 0; $i < 10; $i++) {
                $sum += intval($personalCode[$i]) * $multipliers2[$i];
            }

            $mod = $sum % 11;

            if ($mod === 10) {
                $mod = 0;
            }
        }

        return $mod === intval($personalCode[10]);
    }

    function AJAX_deleteAssignment()
    {
        if (empty($_POST['assignmentId']) || (int)$_POST['assignmentId'] <= 0) {
            stop(400, 'Invalid assignmentId');
        }

        $this->deleteAllAssignmentDependentData($_POST['assignmentId']);

        try {
            Db::delete('assignments', 'assignmentId = ?', [$_POST['assignmentId']]);
            Activity::create(ACTIVITY_DELETE_ASSIGNMENT, $this->auth->userId, null, "Deleted assignment with assignmentId: $_POST[assignmentId]");
        } catch (\Exception $e) {
            stop(400, $e->getMessage());
        }

        stop(200, 'Assignment deleted');
    }

    private function deleteAllAssignmentDependentData($assignmentId): void
    {
        try {
            $criteria = Db::getAll("SELECT criterionId FROM criteria WHERE assignmentId = ?", [$assignmentId]);
            foreach ($criteria as $criterion) {
                Db::delete('userDoneCriteria', 'criterionId = ?', [$criterion['criterionId']]);
            }

            Db::delete('criteria', 'assignmentId =  ?', [$assignmentId]);
            Db::delete('messages', 'assignmentId = ?', [$assignmentId]);
            Db::delete('userAssignments', 'assignmentId = ?', [$assignmentId]);
        } catch (\Exception $e) {
            stop(400, $e->getMessage());
        }
    }

    function AJAX_deleteStudent()
    {

        if (empty($_POST['userId']) || (int)$_POST['userId'] <= 0) {
            stop(400, 'Invalid userId');
        }

        try {
            Db::delete('userAssignments', 'userId = ?', [$_POST['userId']]);
            Db::delete('userExercises', 'userId = ?', [$_POST['userId']]);
            Db::delete('userDoneCriteria', 'userId = ?', [$_POST['userId']]);
            Db::delete('messages', 'userId = ?', [$_POST['userId']]);


            Db::delete('users', 'userId = ?', [$_POST['userId']]);
            Activity::create(ACTIVITY_DELETE_USER, $this->auth->userId, $_POST['userId']);
        } catch (\Exception $e) {
            stop(400, $e->getMessage());
        }

        stop(200);
    }

    function AJAX_addAssignment()
    {
        if (empty($_POST['subjectId']) || !is_numeric($_POST['subjectId'])) {
            stop(400, 'Invalid subjectId');
        }

        if (empty($_POST['assignmentName'])) {
            stop(400, 'Assignment name is required');
        }

        if (empty($_POST['assignmentInstructions'])) {
            stop(400, 'Instructions are required');
        }

        if (empty($_POST['assignmentDueAt'])) {
            stop(400, 'Due date is required');
        }

        // Ensure assignmentEntryDate is present; default to today if missing
        $assignmentEntryDate = empty($_POST['assignmentEntryDate']) ? date('Y-m-d') : $_POST['assignmentEntryDate'];

        // Optional: assignmentHours (Tundide arv) - should be a non-negative integer or null
        $assignmentHours = null;
        if (isset($_POST['assignmentHours']) && $_POST['assignmentHours'] !== '') {
            if (!is_numeric($_POST['assignmentHours']) || (int)$_POST['assignmentHours'] < 0) {
                stop(400, 'Invalid assignmentHours');
            }
            $assignmentHours = (int)$_POST['assignmentHours'];
        }

        $data = [
            'subjectId' => $_POST['subjectId'],
            'assignmentName' => $_POST['assignmentName'],
            'assignmentInstructions' => $_POST['assignmentInstructions'],
            'assignmentDueAt' => $_POST['assignmentDueAt'],
            'assignmentEntryDate' => $assignmentEntryDate,
            'assignmentInitialCode' => $_POST['assignmentInitialCode'] ?? null,
            'assignmentValidationFunction' => $_POST['assignmentValidationFunction'] ?? null,
            'assignmentHours' => $assignmentHours
        ];

        try {

            $assignmentId = Db::insert('assignments', $data);
            Activity::create(ACTIVITY_CREATE_ASSIGNMENT, $this->auth->userId, $assignmentId);
        } catch (\Exception $e) {
            stop(400, $e->getMessage());
        }

        // Defensive: some DB helpers may ignore NULLs in insert; ensure assignmentHours is explicitly set when provided
        if ($assignmentHours !== null) {
            try {
                Db::update('assignments', ['assignmentHours' => $assignmentHours], 'assignmentId = ?', [$assignmentId]);
            } catch (\Exception $e) {
                // Non-fatal: log activity and continue
                Activity::create(ACTIVITY_UPDATE_ASSIGNMENT, $this->auth->userId, $assignmentId, "Failed to set assignmentHours: " . $e->getMessage());
            }
        }


        stop(200, ['assignmentId' => $assignmentId]);
    }

    function AJAX_addSubject()
    {
        if (empty($_POST['subjectName'])) {
            stop(400, 'Aine nimi on kohustuslik');
        }

        if (empty($_POST['subjectExternalId'])) {
            stop(400, 'Remote aine ID on kohustuslik');
        }

        if (empty($_POST['teacherId'])) {
            stop(400, 'Õpetaja on kohustuslik');
        }

        if (empty($_POST['groupId'])) {
            stop(400, 'Grupp on kohustuslik');
        }

        $subject = Db::getFirst("SELECT * FROM subjects WHERE subjectExternalId = ?", [$_POST['subjectExternalId']]);
        if ($subject) {
            stop(409, 'Aine selle remote ID-ga on juba olemas');
        }

        $subject = Db::getFirst("SELECT * FROM subjects WHERE subjectName = ?", [$_POST['subjectName']]);
        if ($subject) {
            stop(409, 'Aine selle nimega on juba olemas');
        }

        $data = [
            'subjectName' => $_POST['subjectName'],
            'subjectExternalId' => $_POST['subjectExternalId'],
            'teacherId' => $_POST['teacherId'],
            'groupId' => $_POST['groupId']
        ];

        try {
            $subjectId = Db::insert('subjects', $data);
            Activity::create(ACTIVITY_CREATE_SUBJECT, $this->auth->userId, $subjectId);
        } catch (\Exception $e) {
            stop(400, $e->getMessage());
        }

        stop(200, ['subjectId' => $subjectId]);
    }


    function AJAX_addUser(): void
    {
        if (empty($_POST['userName'])) {
            stop(400, 'Nimi ei saa olla tühi');
        }

        if (empty($_POST['userPersonalCode'])) {
            stop(400, "Isikukood on kohustuslik");
        }

        $userPersonalCode = $_POST['userPersonalCode'];

        if (!$this->validatePersonalCode($userPersonalCode)) {
            stop(400, "Isikukood $userPersonalCode ei vasta nõuetele.");
        }

        if (User::get(["userPersonalCode = '$userPersonalCode'"])) {
            stop(409, "Kasutaja selle isikukoodiga on juba olemas" . $userPersonalCode);
        }

        $userName = addslashes($_POST['userName']);
        if (User::get(["userName = '$userName'"])) {
            stop(409, __('User already exists'));
        }

        $data = $this->getUserDataForAddingOrUpdating();

        try {

            $userId = Db::insert('users', $data);
            Activity::create(ACTIVITY_ADD_USER, $this->auth->userId, $userId);
        } catch (\Exception $e) {
            stop(400, $e->getMessage());
        }

        stop(200, ['userId' => $userId]);
    }

    function AJAX_addStudent()
    {
        if (empty($_POST['groupId'])) {
            stop(400, 'Grupp on kohustuslik');
        }

        if (empty($_POST['userName'])) {
            stop(400, "Nimi on kohustuslik");
        }
        if (empty($_POST['userPersonalCode'])) {
            stop(400, "Isikukood on kohustuslik");
        }

        $userPersonalCode = $_POST['userPersonalCode'];

        if (!$this->validatePersonalCode($userPersonalCode)) {
            stop(400, "Isikukood ei vasta nõuetele");
        }

        if (User::get(["userPersonalCode = '$userPersonalCode'"])) {
            stop(409, "Õpilane selle isikukoodiga on juba olemas");
        }

        if (!empty($_POST['userExternalId']) && User::get(["userExternalId = '$_POST[userExternalId]'"])) {
            stop(409, "Õpilane selle remote ID-ga on juba olemas");
        }


        $data = [
            'groupId' => $_POST['groupId'],
            'userExternalId' => !empty($_POST['userExternalId']) ? $_POST['userExternalId'] : null,
            'userName' => $_POST['userName'],
            'userPersonalCode' => $_POST['userPersonalCode'],
            'userEmail' => $_POST['userEmail'] ?? null
        ];

        try {
            $userId = Db::insert('users', $data);
            Activity::create(ACTIVITY_ADD_USER, $this->auth->userId, $userId);
        } catch (\Exception $e) {
            stop(400, $e->getMessage());
        }


        stop(200, ['userId' => $userId]);
    }

    function AJAX_addApplicant()
    {
        if (empty($_POST['userName'])) {
            stop(400, "Nimi on kohustuslik");
        }
        if (empty($_POST['userPersonalCode'])) {
            stop(400, "Isikukood on kohustuslik");
        }

        $userPersonalCode = $_POST['userPersonalCode'];

        if (!$this->validatePersonalCode($userPersonalCode)) {
            stop(400, "Isikukood ei vasta nõuetele");
        }

        if (User::get(["userPersonalCode = '$userPersonalCode'"])) {
            stop(409, "Kandidaat selle isikukoodiga on juba olemas");
        }

        $data = [
            'userName' => $_POST['userName'],
            'userPersonalCode' => $_POST['userPersonalCode']
        ];

        $userId = Db::insert('users', $data);

        stop(200, ['userId' => $userId]);
    }

    function AJAX_editUser()
    {
        if (empty($_POST['userId']) || !is_numeric($_POST['userId'])) {
            stop(400, 'Invalid' . ' userId');
        }

        if (empty($_POST['userPersonalCode'])) {
            stop(400, "Isikukood on kohustuslik");
        }

        $userId = $_POST['userId'];
        $userPersonalCode = $_POST['userPersonalCode'];

        if (!$this->validatePersonalCode($userPersonalCode)) {
            stop(400, "Isikukood ei vasta nõuetele");
        }

        $existingUser = Db::getFirst("SELECT * FROM users WHERE userId = $userId");
        if (!$existingUser) {
            stop(409, "Kasutajat ei leitud");
        }

        if ($existingUser && isset($existingUser['userId']) && $existingUser['userId'] != $userId) {
            stop(409, "Kasutaja selle isikukoodiga on juba olemas");
        }

        if (empty($_POST['userPassword'])) {
            unset($_POST['userPassword']);
        }

        $updatedUserData = $this->getUserDataForAddingOrUpdating();

        User::edit($userId, $updatedUserData);

        if (!empty($updatedUserData['userPassword'])) {
            Activity::create(ACTIVITY_UPDATE_USER, $this->auth->userId, $userId, "Password changed");
        }

        if ($existingUser['userName'] != $updatedUserData['userName']) {
            Activity::create(ACTIVITY_UPDATE_USER, $this->auth->userId, $userId, "Name changed from {$existingUser['userName']} to {$updatedUserData['userName']}");
        }

        if (
            (empty($existingUser['groupId']) && !empty($updatedUserData['groupId'])) ||
            (!empty($existingUser['groupId']) && empty($updatedUserData['groupId'])) ||
            (!empty($existingUser['groupId']) && !empty($updatedUserData['groupId']) && $existingUser['groupId'] != $updatedUserData['groupId'])
        ) {
            $oldGroupName = $newGroupName = null;
            if (!empty($existingUser['groupId'])) {
                $oldGroupName = Db::getFirst("SELECT groupName FROM groups WHERE groupId = {$existingUser['groupId']}")['groupName'] ?? 'No Group';
            } else {
                $oldGroupName = 'No Group';
            }

            if (!empty($updatedUserData['groupId'])) {
                $newGroupName = Db::getFirst("SELECT groupName FROM groups WHERE groupId = {$updatedUserData['groupId']}")['groupName'] ?? 'No Group';
            } else {
                $newGroupName = 'No Group';
            }

            $changeDescription = ($oldGroupName === 'No Group' && $newGroupName !== 'No Group') ? "Added to group $newGroupName" : (($oldGroupName !== 'No Group' && $newGroupName === 'No Group') ? "Removed from group $oldGroupName" : (($oldGroupName !== 'No Group' && $newGroupName !== 'No Group' && $oldGroupName !== $newGroupName) ? "Moved from group $oldGroupName to group $newGroupName" : null));


            Activity::create(ACTIVITY_UPDATE_USER, $this->auth->userId, $userId, $changeDescription);
        }

        if ($existingUser['userIsAdmin'] && !empty($updatedUserData['userIsAdmin']) || !empty($existingUser['userIsAdmin']) && $existingUser['userIsAdmin'] != $updatedUserData['userIsAdmin']) {
            Activity::create(ACTIVITY_UPDATE_USER, $this->auth->userId, $userId, "Admin status changed from {$existingUser['userIsAdmin']} to {$updatedUserData['userIsAdmin']}");
        }

        if ($existingUser['userPersonalCode'] != $updatedUserData['userPersonalCode']) {
            Activity::create(ACTIVITY_UPDATE_USER, $this->auth->userId, $userId, "Personal code changed from {$existingUser['userPersonalCode']} to {$updatedUserData['userPersonalCode']}");
        }

        stop(200, ['userId' => $userId]);
    }

    function AJAX_editApplicant()
    {
        if (empty($_POST['userId']) || !is_numeric($_POST['userId'])) {
            stop(400, 'Invalid' . ' userId');
        }

        if (empty($_POST['userPersonalCode'])) {
            stop(400, "Isikukood on kohustuslik");
        }

        $userId = $_POST['userId'];
        $userPersonalCode = $_POST['userPersonalCode'];

        if (!$this->validatePersonalCode($userPersonalCode)) {
            stop(400, "Isikukood ei vasta nõuetele");
        }

        $existingUser = User::get(["userPersonalCode = '$userPersonalCode'"]);
        if ($existingUser && isset($existingUser['userId']) && $existingUser['userId'] != $userId) {
            stop(409, "Kandidaat selle isikukoodiga on juba olemas");
        }

        User::edit($userId, $_POST);


        stop(200);
    }


    function AJAX_deleteUser()
    {
        if (empty($_POST['userId']) || !is_numeric($_POST['userId'])) {
            stop(400, 'Invalid' . ' userId');
        }

        if ($_POST['userId'] == $this->auth->userId) {
            stop(403, __('You cannot delete yourself'));
        }

        User::delete($_POST['userId']);
        Activity::create(ACTIVITY_DELETE_USER, $this->auth->userId, $_POST['userId']);

        stop(200);
    }

    function AJAX_getUser()
    {
        if (empty($_POST['userId']) || !is_numeric($_POST['userId'])) {
            stop(400, 'Invalid' . ' userId');
        }

        stop(200, Db::getFirst("SELECT userIsAdmin,userPersonalCode,userName FROM users WHERE userId = $_POST[userId]"));
    }

    public function htmlIsValid($html): bool
    {

        $config = HTMLPurifier_Config::createDefault();
        $config->set('Core.Encoding', 'UTF-8');
        $purifier = new HTMLPurifier($config);
        $pure_html = $purifier->purify($html);

        return !!strcmp($html, $pure_html);
    }

    private function getUserDataForAddingOrUpdating(): array
    {
        $data = [
            'userName' => $_POST['userName'],
            'userPersonalCode' => $_POST['userPersonalCode'],
            'groupId' => empty($_POST['groupId']) ? null : $_POST['groupId'],
            'userIsAdmin' => empty($_POST['userIsAdmin']) ? 0 : 1,
            'userEmail' => $_POST['userEmail'],
            'userDeleted' => empty($_POST['userDeleted']) ? 0 : 1
        ];

        if (!empty($_POST['userPassword'])) {
            $data['userPassword'] = password_hash($_POST['userPassword'], PASSWORD_DEFAULT);
        }

        return $data;
    }

    function exercises()
    {
        $this->exercises = Db::getAll("SELECT * FROM exercises");
    }

    function AJAX_editExercise()
    {
        $exerciseId = $_POST['id'];

        // Update only the fields that are present in the request
        if (isset($_POST['exercise_name'])) {
            Db::update('exercises', ['exerciseName' => $_POST['exercise_name']], 'exerciseId = ?', [$exerciseId]);
        }

        if (isset($_POST['instructions'])) {
            Db::update('exercises', ['exerciseInstructions' => $_POST['instructions']], 'exerciseId = ?', [$exerciseId]);
        }

        if (isset($_POST['initial_code'])) {
            Db::update('exercises', ['exerciseInitialCode' => $_POST['initial_code']], 'exerciseId = ?', [$exerciseId]);
        }

        if (isset($_POST['validation_function'])) {
            Db::update('exercises', ['exerciseValidationFunction' => $_POST['validation_function']], 'exerciseId = ?', [$exerciseId]);
        }

        stop(200);
    }

    function AJAX_deleteExercise()
    {
        if (empty($_POST['id']) || !is_numeric($_POST['id'])) {
            stop(400, 'Invalid exercise id');
        }

        // First delete all done exercises for this exercise
        Db::delete('userExercises', 'exerciseId = ?', [$_POST['id']]);

        Db::delete('exercises', 'exerciseId = ?', [$_POST['id']]);

        stop(200);
    }

    function AJAX_createExercise()
    {
        // Ensure that the exercise name is provided
        if (empty($_POST['exercise_name'])) {
            stop(400, 'Exercise name is required.');
        }

        // Collect the data for insertion
        $data = [
            'exerciseName' => $_POST['exercise_name'],
            'exerciseInstructions' => isset($_POST['instructions']) ? $_POST['instructions'] : '',
            'exerciseInitialCode' => isset($_POST['initial_code']) ? $_POST['initial_code'] : '',
            'exerciseValidationFunction' => isset($_POST['validation_function']) ? $_POST['validation_function'] : '',
        ];

        // Insert the new exercise
        $exerciseId = Db::insert('exercises', $data);

        // Return the new exercise ID to the frontend
        stop(200, ['id' => $exerciseId]);
    }

    function AJAX_addGroup(): void
    {
        if (empty($_POST['groupName'])) {
            stop(400, 'Grupi nimi on kohustuslik');
        }

        $existingGroup = Db::getFirst("SELECT groupId FROM groups WHERE groupName = ?", [$_POST['groupName']]);
        if ($existingGroup) {
            stop(409, 'Grupp nimega ' . $_POST['groupName'] . ' on juba olemas');
        }

        $groupId = Db::insert('groups', ['groupName' => $_POST['groupName']]);
        Activity::create(ACTIVITY_CREATE_GROUP, $this->auth->userId, $groupId);


        $this->addStudentsToGroup($groupId);

        stop(200, ['groupId' => $groupId]);
    }

    private function checkStudentNameAndPersonalCode($student): array
    {
        try {

            if (empty($student['name'])) {
                return ['status' => 400, 'message' => 'Nimi on kohustuslik'];
            }
            if (empty($student['idcode'])) {
                return ['status' => 400, 'message' => "Isikukood on kohustuslik"];
            }

            $userPersonalCode = $student['idcode'];

            if (!$this->validatePersonalCode($userPersonalCode)) {
                return ['status' => 400, 'message' => "Isikukood $userPersonalCode ei vasta nõuetele"];
            }

            if (User::get(["userPersonalCode = '$userPersonalCode'"])) {
                return ['status' => 409, 'message' => "Õpilane isikukoodiga $userPersonalCode on juba olemas"];
            }
        } catch (\Exception $e) {
            return ['status' => 400, 'message' => 'Õpilase lisamine ebaõnnestus: ' . $e->getMessage()];
        }

        return [];
    }


    private function addStudentsToGroup($groupId): void
    {
        if (!empty($_POST['students'])) {


            foreach ($_POST['students'] as $student) {

                $checkStudentNameAndPersonalCode = $this->checkStudentNameAndPersonalCode($student);
                if ($checkStudentNameAndPersonalCode) {
                    stop($checkStudentNameAndPersonalCode['status'], json_encode($checkStudentNameAndPersonalCode['message']));
                }

                try {
                    $userId = Db::insert('users', [
                        'userName' => addslashes($student['name']),
                        'userPersonalCode' => $student['idcode'],
                        'userExternalId' => $student['studentId'],
                        'groupId' => $groupId
                    ]);

                    Activity::create(ACTIVITY_ADD_USER, $this->auth->userId, $userId);
                } catch (\Exception $e) {
                    stop(400, 'Õpilase lisamine ebaõnnestus: ' . $e->getMessage());
                }
            }
            stop(200, ['groupId' => $groupId]);
        }
    }
}
