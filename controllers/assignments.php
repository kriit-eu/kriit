<?php namespace App;

class assignments extends Controller
{
    public $template = 'master';

    public function view()
    {
        $this->isStudent = $this->auth->groupId && !$this->auth->userIsAdmin && !$this->auth->userIsTeacher;
        $this->isTeacher = $this->auth->userIsTeacher;
        $assignmentId = $this->getId();

        $statusClassMap = [
            'Esitamata' => $this->isStudent ? 'yellow-cell' : '',
            'Ülevaatamata' => $this->auth->userIsTeacher ? 'red-cell' : '',
        ];

        // Fetch criteria, students, their userDoneCriteria, and messages for this assignment
        $data = Db::getAll("
            SELECT
                a.assignmentId, a.assignmentName, a.assignmentInstructions, a.assignmentDueAt,
                c.criterionId, c.criterionName,
                u.userId AS studentId, u.userName AS studentName, u.groupId,
                ua.userGrade, ua.assignmentStatusId, ast.statusName AS assignmentStatusName,
                udc.criterionId AS userDoneCriterionId,
                m.messageId, m.content AS messageContent, m.userId AS messageUserId, mu.userName AS messageUserName, m.CreatedAt
            FROM assignments a
            JOIN criteria c ON c.assignmentId = a.assignmentId
            JOIN subjects s ON a.subjectId = s.subjectId
            JOIN groups g ON s.groupId = g.groupId
            JOIN users u ON u.groupId = g.groupId
            LEFT JOIN userAssignments ua ON ua.assignmentId = a.assignmentId AND ua.userId = u.userId
            LEFT JOIN assignmentStatuses ast ON ua.assignmentStatusId = ast.assignmentStatusId
            LEFT JOIN userDoneCriteria udc ON udc.criterionId = c.criterionId AND udc.userId = u.userId
            LEFT JOIN messages m ON m.assignmentId = a.assignmentId
            LEFT JOIN users mu ON mu.userId = m.userId
            WHERE a.assignmentId = ?
            ORDER BY u.userName, c.criterionId, m.messageId
        ", [$assignmentId]);

        $assignment = [
            'assignmentId' => $assignmentId,
            'assignmentName' => null,
            'assignmentInstructions' => null,
            'assignmentDueAt' => null,
            'criteria' => [],
            'students' => [],
            'messages' => []
        ];

        // Process each row of data
        foreach ($data as $row) {
            // Set assignment details (only once)
            if (empty($assignment['assignmentName'])) {
                $assignment['assignmentName'] = $row['assignmentName'];
                $assignment['assignmentInstructions'] = $row['assignmentInstructions'];
                // Format the due date to Estonian format
                $assignment['assignmentDueAt'] = date('d.m.Y', strtotime($row['assignmentDueAt']));
            }

            $studentId = $row['studentId'];
            $criteriaId = $row['criterionId'];
            $messageId = $row['messageId'];

            // Skip if the current user is the student
            if ($this->isStudent && $this->auth->userId !== $studentId) {
                continue;
            }

            // Initialize criteria if not exists
            if (!isset($assignment['criteria'][$criteriaId])) {
                $assignment['criteria'][$criteriaId] = [
                    'criteriaId' => $criteriaId,
                    'criteriaName' => $row['criterionName']
                ];
            }

            // Initialize student if not exists
            if (!isset($assignment['students'][$studentId])) {
                $assignment['students'][$studentId] = [
                    'studentId' => $studentId,
                    'studentName' => $row['studentName'],
                    'grade' => $row['userGrade'],
                    'assignmentStatusName' => $row['assignmentStatusName'] ?? 'Esitamata',
                    'initials' => mb_substr($row['studentName'], 0, 1) . mb_substr($row['studentName'], mb_strrpos($row['studentName'], ' ') + 1, 1),
                    'userDoneCriteria' => [],
                    'userDoneCriteriaCount' => 0,
                    'class' => '',
                    'tooltipText' => ''
                ];
            }

            // Add userDoneCriteria for this student and criteria (just tracking if done or not)
            $assignment['students'][$studentId]['userDoneCriteria'][$criteriaId] = [
                'criteriaId' => $criteriaId,
                'completed' => $row['userDoneCriterionId'] !== null  // True if the student completed the criterion
            ];

            // Count the number of completed criteria
            if ($row['userDoneCriterionId'] !== null) {
                $assignment['students'][$studentId]['userDoneCriteriaCount']++;
            }


            // Add tooltip and class logic for students
            $statusName = $row['assignmentStatusName'] ?? 'Esitamata';
            $grade = $row['userGrade'] ?? '';
            $isLowGrade = $grade == 'MA' || (is_numeric($grade) && intval($grade) < 3);
            $daysRemaining = (int)(new \DateTime())->diff(new \DateTime($row['assignmentDueAt']))->format('%r%a');

            // Determine the CSS class for the assignment status
            $class = $daysRemaining < 0 ?
                (($this->isStudent && $statusName == 'Esitamata') ||
                ($this->isStudent && $isLowGrade) ||
                ($this->isTeacher && $statusName !== 'Hinnatud') ? 'red-cell' : '') :
                ($isLowGrade ? 'red-cell' : ($statusClassMap[$statusName] ?? ''));

            $tooltipText = $statusName ?? 'Esitamata';

            // Add class and tooltip text to the student
            $assignment['students'][$studentId]['class'] = $class;
            $assignment['students'][$studentId]['tooltipText'] = $tooltipText;

            // Initialize and add messages if they exist
            if ($messageId !== null && !isset($assignment['messages'][$messageId])) {
                $assignment['messages'][$messageId] = [
                    'messageId' => $messageId,
                    'content' => $row['messageContent'],
                    'userId' => $row['messageUserId'],
                    'userName' => $row['messageUserName'],
                    'createdAt' => $this->formatMessageDate($row['CreatedAt'])
                ];
            }
        }

        $this->assignment = $assignment;
    }

    // Helper function to check if a date is today
    private function formatMessageDate($date): string
    {
        if (date('Y-m-d') === date('Y-m-d', strtotime($date))) {
            return 'Täna ' . date('H:i', strtotime($date));
        } elseif (date('Y-m-d', strtotime('-1 day')) === date('Y-m-d', strtotime($date))) {
            return 'Eile ' . date('H:i', strtotime($date));
        } else {
            return date('d.m.Y H:i', strtotime($date));
        }
    }
}
