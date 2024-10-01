<?php namespace App;

class subjects extends Controller
{
    public $template = 'master';


    public function index()
    {
        $this->template = $this->auth->userIsAdmin ? 'admin' : 'master';
        // Define user roles
        $this->isStudent = $this->auth->groupId && !$this->auth->userIsAdmin && !$this->auth->userIsTeacher;
        $this->isTeacher = $this->auth->userIsTeacher;

        // Construct the WHERE clause for the SQL query
        $whereClause = implode(' OR ', array_filter([
            "s.teacherId = {$this->auth->userId}",
            $this->auth->groupId ? "s.groupId = {$this->auth->groupId}" : null,
            $this->auth->userIsAdmin ? 'true' : null
        ]));

        // Fetch data from the database
        $this->data = Db::getAll("
            SELECT
                s.subjectId, s.subjectName, s.teacherId, t.userName AS teacherName,
                u.userId AS studentId, u.userName AS studentName, u.groupId, g.groupName,
                a.assignmentId, a.assignmentName, a.assignmentDueAt,
                ua.userGrade, ua.assignmentStatusId, ast.statusName AS assignmentStatusName
            FROM subjects s
            JOIN users t ON s.teacherId = t.userId
            JOIN groups g ON s.groupId = g.groupId
            LEFT JOIN users u ON u.groupId = g.groupId
            LEFT JOIN assignments a ON a.subjectId = s.subjectId
            LEFT JOIN userAssignments ua ON ua.assignmentId = a.assignmentId AND ua.userId = u.userId
            LEFT JOIN assignmentStatuses ast ON ua.assignmentStatusId = ast.assignmentStatusId
            WHERE {$whereClause}
            ORDER BY g.groupName, u.userName, s.subjectName, a.assignmentDueAt");

        $groups = [];

        // Define status class mapping
        $statusClassMap = [
            'Esitamata' => $this->isStudent ? 'yellow-cell' : '',
            'Ülevaatamata' => $this->auth->userIsTeacher ? 'red-cell' : '',
        ];

        // Process each row of data
        foreach ($this->data as $row) {
            if ($this->isStudent && $row['studentId'] !== $this->auth->userId) {
                continue;
            }

            $groupName = $row['groupName'];
            $studentId = $row['studentId'];
            $subjectId = $row['subjectId'];
            $assignmentId = $row['assignmentId'];

            // Initialize group if not exists
            if (!isset($groups[$groupName])) {
                $groups[$groupName] = [
                    'groupName' => $groupName,
                    'students' => [],
                    'subjects' => []
                ];
            }

            // Add or update student in group
            $groups[$groupName]['students'][$studentId] = [
                'userName' => $row['studentName'],
                'subjectId' => $subjectId,
                'status' => $row['assignmentStatusName'] ?? 'Esitamata',
                'userId' => $studentId,
                'initials' => mb_substr($row['studentName'] ?? '', 0, 1)
                    . mb_substr(mb_strrpos($row['studentName'] ?? '', ' ') + 1, 1)
            ];

            // Add or update subject in group
            if (!isset($groups[$groupName]['subjects'][$subjectId])) {
                $groups[$groupName]['subjects'][$subjectId] = [
                    'subjectId' => $subjectId,
                    'subjectName' => $row['subjectName'],
                    'teacherName' => $row['teacherName'],
                    'assignments' => []
                ];
            }

            // Process assignment data if exists
            if ($assignmentId) {
                $dueDate = !empty($row['assignmentDueAt']) ? new \DateTime($row['assignmentDueAt']) : null;
                $daysRemaining = $dueDate ? (int)(new \DateTime())->diff($dueDate)->format('%r%a') : 1000;

                // Add or update assignment in subject
                if (!isset($groups[$groupName]['subjects'][$subjectId]['assignments'][$assignmentId])) {
                    $groups[$groupName]['subjects'][$subjectId]['assignments'][$assignmentId] = [
                        'assignmentId' => $assignmentId,
                        'assignmentName' => $row['assignmentName'],
                        'assignmentDueAt' => $row['assignmentDueAt'],
                        'badgeClass' => $daysRemaining >= 3 ? 'badge bg-light text-dark' :
                            ($daysRemaining > 0 ? 'badge bg-warning text-dark' : 'badge bg-danger'),
                        'daysRemaining' => $daysRemaining,
                        'assignmentStatuses' => []
                    ];
                }

                $statusName = $row['assignmentStatusName'] ?? 'Esitamata';
                $statusId = $row['assignmentStatusId'] ?? ASSIGNMENT_STATUS_NOT_SUBMITTED;
                $grade = $row['userGrade'] ?? '';
                $isNegativeGrade = $grade == 'MA' || (is_numeric($grade) && intval($grade) < 3);

                // Determine the CSS class for the assignment status
                $class = ($this->isStudent && $isNegativeGrade) ? 'red-cell' :
                    ($daysRemaining < 0 ?
                        (($this->isStudent && $statusId == ASSIGNMENT_STATUS_NOT_SUBMITTED) ||
                        ($this->isTeacher && $statusId == ASSIGNMENT_STATUS_WAITING_FOR_REVIEW) ? 'red-cell' :
                            ($this->isTeacher && $statusId == ASSIGNMENT_STATUS_NOT_SUBMITTED || $isNegativeGrade ? 'yellow-cell' : '')) :
                        ($this->isTeacher && $statusId != ASSIGNMENT_STATUS_WAITING_FOR_REVIEW ? '' : ($statusClassMap[$statusName] ?? '')));


                // Determine the link text based on assignment status
                $linkText = match ($statusName) {
                    'Esitamata' => $this->isStudent ? 'Esita' : 'Hinda',
                    'Ülevaatamata' => $this->isStudent ? 'Muuda' : 'Hinda',
                    'Hinnatud' => $isNegativeGrade ? ($this->isStudent ? 'Esita uuesti' : 'Muuda hinnet') : '',
                    default => ''
                };

                $tooltipText = $this->isStudent ? $linkText : ($statusName ? "($statusName) $linkText" : 'Esitamata');

                // Add or update assignment status
                $groups[$groupName]['subjects'][$subjectId]['assignments'][$assignmentId]['assignmentStatuses'][$studentId] = [
                    'userId' => $studentId,
                    'assignmentStatusName' => $statusName,
                    'grade' => $grade,
                    'class' => $class,
                    'tooltipText' => $tooltipText
                ];
            }
        }

        $this->statusClassMap = $statusClassMap;
        $this->groups = $groups;
    }
}
