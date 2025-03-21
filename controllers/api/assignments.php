<?php namespace App\api;

use App\Activity;
use App\Controller;
use App\Db;

// add /api/groups to the URL

class assignments extends Controller
{

    function addOrUpdate()
    {

        $validationError = $this->validateAssignmentRequestData();
        if ($validationError) {
            stop($validationError['code'], $validationError['message']);
        }

        // Construct parameterized query to check if group exists based on either groupId or groupName which ever is provided
        $groupId = $this->addAssignmentGroup();

        $currentTeacher = Db::getFirst("SELECT userId, userName FROM users WHERE userId = ?", [$this->auth->userId]);

        $subjectId = $this->addAssignmentSubject($groupId);

        $this->addAssignmentTeachers();

        //Check if assignment exists with given assignmentExternalId
        $existingAssignment = Db::getFirst("SELECT * FROM assignments WHERE assignmentExternalId = ?", [$_POST['assignmentExternalId']]);
        $assignmentName = mb_strlen($_POST['assignmentInstructions']) > 50 ? mb_substr($_POST['assignmentInstructions'], 0, 47) . '...' : $_POST['assignmentInstructions'];

        if ($existingAssignment) {
            $this->updateAssignment($assignmentName, $currentTeacher, $existingAssignment);
            stop(200, $existingAssignment['assignmentId']);
        } else {

            // Create assignment
            $data = [
                'subjectId' => $subjectId,
                'assignmentName' => $assignmentName,
                'assignmentExternalId' => $_POST['assignmentExternalId'],
                'assignmentDueAt' => $_POST['assignmentDueAt'],
                'assignmentInstructions' => $_POST['assignmentInstructions'],
            ];

            $assignmentId = Db::insert('assignments', $data);
            Activity::create(ACTIVITY_CREATE_ASSIGNMENT, $this->auth->userId, $assignmentId);
            $this->saveMessage($assignmentId, $currentTeacher['userId'], "$currentTeacher[userName] lõi ülesande '$assignmentName'.", true);

            stop(200, $assignmentId);
        }
    }

    private function updateAssignment($assignmentName, $currentTeacher, $existingAssignment): void
    {
        // Overwrite assignment existing data
        $data = [
            'assignmentName' => $assignmentName,
            'assignmentDueAt' => $_POST['assignmentDueAt'],
            'assignmentInstructions' => $_POST['assignmentInstructions'],
        ];

        Db::update('assignments', $data, "assignmentId=$existingAssignment[assignmentId]");

        if ($existingAssignment['assignmentName'] !== $assignmentName) {
            $this->saveMessage($existingAssignment['assignmentId'], $currentTeacher['userId'], "$currentTeacher[userName] muutis ülesande nimeks '$assignmentName'.", true);
            Activity::create(ACTIVITY_UPDATE_ASSIGNMENT, $this->auth->userId, $existingAssignment['assignmentId'], "Changed assignment name from $existingAssignment[assignmentName] to $assignmentName");
        }

        if ($existingAssignment['assignmentInstructions'] !== $_POST['assignmentInstructions']) {
            $this->saveMessage($existingAssignment['assignmentId'], $currentTeacher['userId'], "$currentTeacher[userName] muutis ülesande juhendiks '$_POST[assignmentInstructions]'.", true);
            Activity::create(ACTIVITY_UPDATE_ASSIGNMENT, $this->auth->userId, $existingAssignment['assignmentId'], "Changed assignment instructions from $existingAssignment[assignmentInstructions] to $_POST[assignmentInstructions]");
        }

        if ($existingAssignment['assignmentDueAt'] !== $_POST['assignmentDueAt']) {
            $date = date('d.m.Y', strtotime($_POST['assignmentDueAt']));
            $this->saveMessage($existingAssignment['assignmentId'], $currentTeacher['userId'], "$currentTeacher[userName] muutis ülesande tähtajaks '$date'.", true);
            Activity::create(ACTIVITY_UPDATE_ASSIGNMENT, $this->auth->userId, $existingAssignment['assignmentId'], "Changed assignment due date from $existingAssignment[assignmentDueAt] to $_POST[assignmentDueAt]");
        }
    }

    private function addAssignmentTeachers(): void
    {
        $teachers = $_POST['teachersData'];
        if ($teachers) {
            foreach ($teachers as $teacher) {
                $existingTeacher = Db::getFirst("SELECT * FROM users WHERE userName = ?", [$teacher['teacherName']]);
                if (!$existingTeacher) {
                    Db::insert('users', ['userName' => $teacher['teacherName'], 'userIsTeacher' => 1, 'userIsAdmin' => 0, 'userPersonalCode' => $teacher['teacherPersonalCode'], 'userEmail' => $teacher['teacherEmail']]);
                } else {
                    $existingTeacherId = $existingTeacher['userId'];

                    if ($existingTeacher['userPersonalCode'] !== $teacher['teacherPersonalCode']) {
                        Db::update('users', ['userPersonalCode' => $teacher['teacherPersonalCode']], "userId = ?", [$existingTeacherId]);
                    }
                    if ($existingTeacher['userEmail'] !== $teacher['teacherEmail']) {
                        Db::update('users', ['userEmail' => $teacher['teacherEmail']], "userId = ?", [$existingTeacherId]);
                    }
                }

            }
        }
    }

    private function addAssignmentSubject($groupId): int
    {
        // Construct parameterized query to check if subject exists based on either subjectId or subjectName which ever is provided
        $field = !empty($_POST['subjectId']) ? 'subjectId' : 'subjectName';
        $subjectId = Db::getOne("SELECT subjectId FROM subjects WHERE $field = ?", [$_POST[$field]]);
        if (!$subjectId) {
            if ($field === 'subjectId') {
                stop(400, "Invalid subjectId provided");
            } else {
                $subjectId = Db::insert('subjects', ['subjectName' => $_POST['subjectName'], 'subjectExternalId' => $_POST['subjectExternalId'], 'groupId' => $groupId, 'teacherId' => $this->auth->userId]);
                Activity::create(ACTIVITY_CREATE_SUBJECT, $this->auth->userId, $subjectId);
            }
        } else {
            $subject = Db::getFirst("SELECT * FROM subjects WHERE subjectId = ?", [$subjectId]);
            if ($subject['teacherId'] !== $this->auth->userId) {
                $otherTeacher = Db::getFirst("SELECT * FROM users WHERE userId = ?", [$subject['teacherId']]);
                stop(403, 'Subject belongs to ' . $otherTeacher['userName']);
            }
        }

        return $subjectId;
    }

    private function addAssignmentGroup()
    {
        $field = !empty($_POST['groupId']) ? 'groupId' : 'groupName';
        $groupId = Db::getOne("SELECT groupId FROM groups WHERE $field = ?", [$_POST[$field]]);

        if (!$groupId) {
            if ($field === 'groupId') {
                stop(400, "Invalid groupId provided");
            } else {
                $groupId = Db::insert('groups', ['groupName' => $_POST['groupName']]);
                Activity::create(ACTIVITY_CREATE_GROUP, $this->auth->userId, $groupId);
            }
        }

        return $groupId;
    }

    private function validateAssignmentRequestData(): ?array
    {
        // Check that either subjectId or subjectName is provided but not both
        if (empty($_POST['subjectId']) && empty($_POST['subjectName'])) {
            return ['code' => 400, 'message' => 'subjectId or subjectName is required'];
        }

        if (!empty($_POST['subjectId']) && !empty($_POST['subjectName'])) {
            return ['code' => 400, 'message' => 'subjectId or subjectName is required but not both'];
        }

        // Check that either groupId or groupName is provided but not both
        if (empty($_POST['groupId']) && empty($_POST['groupName'])) {
            return ['code' => 400, 'message' => 'groupId or groupName is required'];
        }

        if (!empty($_POST['groupId']) && !empty($_POST['groupName'])) {
            return ['code' => 400, 'message' => 'groupId or groupName is required but not both'];
        }

        // Check if subjectExternalId is provided
        if (empty($_POST['subjectExternalId'])) {
            return ['code' => 400, 'message' => 'subjectExternalId is required'];
        }

        // Check if assignmentExternalId is provided
        if (empty($_POST['assignmentExternalId'])) {
            return ['code' => 400, 'message' => 'assignmentExternalId is required'];
        }

        // if assignmentDueAt is provided, check if it is a valid date
        if (!empty($_POST['assignmentDueAt'])) {
            $dateParts = explode('-', $_POST['assignmentDueAt']);
            if (count($dateParts) !== 3 || !checkdate((int)$dateParts[1], (int)$dateParts[2], (int)$dateParts[0])) {
                return ['code' => 400, 'message' => 'Invalid assignmentDueAt'];
            }
        }

        return null;
    }


    private function saveMessage($assignmentId, $userId, $content, $isNotification = false): void
    {
        Db::insert('messages', ['assignmentId' => $assignmentId, 'userId' => $userId, 'content' => $content, 'CreatedAt' => date('Y-m-d H:i:s'), 'isNotification' => $isNotification]);

    }

    function deleteAssignment(): void
    {
        if (empty($_POST['assignmentExternalId'])) {
            stop(400, 'Invalid assignmentExternalId');
        }

        try {
            // Get the assignment to log its ID
            $assignment = Assignment::getByExternalId($_POST['assignmentExternalId'], 1);
            
            if (!$assignment) {
                stop(404, 'Assignment not found');
            }
            
            // Use Assignment class to delete by external ID
            $result = Assignment::deleteByExternalId($_POST['assignmentExternalId'], 1);
            
            if ($result) {
                Activity::create(ACTIVITY_DELETE_ASSIGNMENT, $this->auth->userId, $assignment['assignmentId'], 
                    "Deleted assignment with assignmentExternalId: $_POST[assignmentExternalId]");
                stop(200, 'Assignment deleted');
            } else {
                stop(500, 'Failed to delete assignment');
            }
        } catch (\Exception $e) {
            stop(400, $e->getMessage());
        }
    }

}
