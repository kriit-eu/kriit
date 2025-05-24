<?php namespace App;

class subjects extends Controller
{
    public $template = 'master';


    /**
     * Helper method to get relative time in Estonian (e.g., "2 nädalat tagasi")
     *
     * @param \DateTime $timestamp The timestamp to format
     * @return string Formatted relative time in Estonian
     */
    private function getRelativeTimeInEstonian(\DateTime $timestamp): string
    {
        $now = new \DateTime();
        $diff = $timestamp->diff($now);

        // Calculate total difference in seconds for comparison
        $totalSeconds = $now->getTimestamp() - $timestamp->getTimestamp();

        if ($totalSeconds < 60) {
            return 'just praegu';
        } elseif ($totalSeconds < 3600) {
            $minutes = $diff->i;
            return $minutes . ' ' . ($minutes == 1 ? 'minut' : 'minutit') . ' tagasi';
        } elseif ($totalSeconds < 86400) {
            $hours = $diff->h;
            return $hours . ' ' . ($hours == 1 ? 'tund' : 'tundi') . ' tagasi';
        } elseif ($totalSeconds < 604800) {
            $days = $diff->d;
            return $days . ' ' . ($days == 1 ? 'päev' : 'päeva') . ' tagasi';
        } elseif ($totalSeconds < 2592000) {
            $weeks = floor($diff->d / 7);
            return $weeks . ' ' . ($weeks == 1 ? 'nädal' : 'nädalat') . ' tagasi';
        } elseif ($totalSeconds < 31536000) {
            $months = $diff->m;
            return $months . ' ' . ($months == 1 ? 'kuu' : 'kuud') . ' tagasi';
        } else {
            $years = $diff->y;
            return $years . ' ' . ($years == 1 ? 'aasta' : 'aastat') . ' tagasi';
        }
    }

    /**
     * Calculate the time difference between two timestamps in Estonian (e.g., "3 päeva")
     *
     * @param \DateTime $startTime The start timestamp
     * @param \DateTime $endTime The end timestamp
     * @return string Formatted time difference in Estonian
     */
    private function getTimeDifferenceInEstonian(\DateTime $startTime, \DateTime $endTime): string
    {
        // Make sure endTime is after startTime
        if ($endTime < $startTime) {
            return '';
        }

        $diff = $startTime->diff($endTime);
        $totalSeconds = $endTime->getTimestamp() - $startTime->getTimestamp();

        if ($totalSeconds < 60) {
            return 'mõne sekundi';
        } elseif ($totalSeconds < 3600) {
            $minutes = $diff->i;
            return $minutes . ' ' . ($minutes == 1 ? 'minut' : 'minutit');
        } elseif ($totalSeconds < 86400) {
            $hours = $diff->h;
            return $hours . ' ' . ($hours == 1 ? 'tund' : 'tundi');
        } elseif ($totalSeconds < 604800) {
            $days = $diff->d;
            return $days . ' ' . ($days == 1 ? 'päev' : 'päeva');
        } elseif ($totalSeconds < 2592000) {
            $weeks = floor($diff->d / 7);
            return $weeks . ' ' . ($weeks == 1 ? 'nädal' : 'nädalat');
        } elseif ($totalSeconds < 31536000) {
            $months = $diff->m;
            return $months . ' ' . ($months == 1 ? 'kuu' : 'kuud');
        } else {
            $years = $diff->y;
            return $years . ' ' . ($years == 1 ? 'aasta' : 'aastat');
        }
    }

    public function index()
    {
        $this->template = $this->auth->userIsAdmin ? 'admin' : 'master';
        // Define user roles
        $this->isStudent = $this->auth->groupId && !$this->auth->userIsAdmin && !$this->auth->userIsTeacher;
        $this->isTeacher = $this->auth->userIsTeacher;

        // Check if we should show inactive students
        $this->showAll = isset($_GET['showAll']) && $_GET['showAll'] == '1';

        // Construct the WHERE clause for the SQL query
        $whereClause = implode(' OR ', array_filter([
            "s.teacherId = {$this->auth->userId}",
            $this->auth->groupId ? "s.groupId = {$this->auth->groupId}" : null,
            $this->auth->userIsAdmin ? 'true' : null
        ]));

        // Fetch data from the database - include inactive students who have ungraded assignments
        // Only include subjects that have at least one assignment
        $showAllValue = $this->showAll ? 1 : 0;
        $this->data = Db::getAll("
            SELECT
                s.subjectId, s.subjectName, s.teacherId, s.subjectExternalId, t.userName AS teacherName,
                u.userId AS studentId, u.userName AS studentName, u.groupId, g.groupName, u.userIsActive, u.userDeleted,
                a.assignmentId, a.assignmentName, a.assignmentDueAt, a.assignmentEntryDate,
                ua.userGrade, ua.assignmentStatusId, ast.statusName AS assignmentStatusName,
                ua.userAssignmentSubmittedAt, ua.userAssignmentGradedAt
            FROM subjects s
            JOIN users t ON s.teacherId = t.userId
            JOIN groups g ON s.groupId = g.groupId
            LEFT JOIN users u ON u.groupId = g.groupId
            JOIN assignments a ON a.subjectId = s.subjectId  /* INNER JOIN to exclude subjects without assignments */
            LEFT JOIN userAssignments ua ON ua.assignmentId = a.assignmentId AND ua.userId = u.userId
            LEFT JOIN assignmentStatuses ast ON ua.assignmentStatusId = ast.assignmentStatusId
            WHERE ({$whereClause})
            AND (
                (u.userIsActive = 1 AND u.userDeleted = 0)
                OR $showAllValue = 1
                OR EXISTS (
                    SELECT 1
                    FROM assignments a2
                    LEFT JOIN userAssignments ua2 ON ua2.assignmentId = a2.assignmentId AND ua2.userId = u.userId
                    WHERE a2.subjectId = s.subjectId
                    AND ua2.assignmentStatusId = 2
                )
            )
            ORDER BY g.groupName, u.userName, s.subjectName, a.assignmentDueAt");



        $groups = [];



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
                'userIsActive' => $row['userIsActive'] ?? 1,
                'userDeleted' => $row['userDeleted'] ?? 0,
                'initials' => mb_substr($row['studentName'] ?? '', 0, 1)
                    . mb_substr(mb_strrpos($row['studentName'] ?? '', ' ') + 1, 1)
            ];

            // Add or update subject in group
            if (!isset($groups[$groupName]['subjects'][$subjectId])) {
                $groups[$groupName]['subjects'][$subjectId] = [
                    'subjectId' => $subjectId,
                    'subjectName' => $row['subjectName'],
                    'subjectExternalId' => $row['subjectExternalId'],
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
                    // Format the assignmentEntryDate if it exists
                    $entryDateFormatted = '';
                    if (!empty($row['assignmentEntryDate'])) {
                        $entryDate = new \DateTime($row['assignmentEntryDate']);
                        $entryDateFormatted = $entryDate->format('d.m');
                    }

                    $groups[$groupName]['subjects'][$subjectId]['assignments'][$assignmentId] = [
                        'assignmentId' => $assignmentId,
                        'assignmentName' => $row['assignmentName'],
                        'assignmentDueAt' => $row['assignmentDueAt'],
                        'assignmentEntryDate' => $row['assignmentEntryDate'],
                        'assignmentEntryDateFormatted' => $entryDateFormatted,
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
                $class = Assignment::cellColor(
                    $this->isStudent,
                    $this->isTeacher,
                    $isNegativeGrade,
                    $daysRemaining,
                    $statusId,
                    $statusName);

                // Determine the link text based on assignment status
                $linkText = match ($statusName) {
                    'Esitamata' => $this->isStudent ? 'Esita' : 'Hinda',
                    'Kontrollimisel' => $this->isStudent ? 'Muuda' : 'Hinda',
                    'Hinnatud' => $isNegativeGrade ? ($this->isStudent ? 'Esita uuesti' : 'Muuda hinnet') : '',
                    default => ''
                };

                // Default tooltip text based on user role and status
                $tooltipText = $this->isStudent ? $linkText : ($statusName ? "($statusName) $linkText" : 'Esitamata');

                // Get the timestamps for grading and submission
                $gradedTimestamp = null;
                $submittedTimestamp = null;

                if (!empty($row['userAssignmentGradedAt'])) {
                    $gradedTimestamp = new \DateTime($row['userAssignmentGradedAt']);
                }

                if (!empty($row['userAssignmentSubmittedAt'])) {
                    $submittedTimestamp = new \DateTime($row['userAssignmentSubmittedAt']);
                }

                // Format the timestamps for display with shorter year format (25 instead of 2025)
                $gradedTimestampFormatted = $gradedTimestamp ? $gradedTimestamp->format('d.m.y H:i') : '';
                $submittedTimestampFormatted = $submittedTimestamp ? $submittedTimestamp->format('d.m.y H:i') : '';

                // Update tooltip text based on assignment status
                if ($this->isStudent) {
                    // For students
                    if ($statusName === 'Hinnatud' && !empty($gradedTimestampFormatted)) {
                        $tooltipText = "Hinnatud $gradedTimestampFormatted";
                    } elseif ($statusName === 'Kontrollimisel' && !empty($submittedTimestampFormatted)) {
                        $tooltipText = "Esitatud $submittedTimestampFormatted";
                    }
                } else {
                    // For teachers and admins - no need to include student name since tooltip will be below
                    if ($statusName === 'Hinnatud') {
                        // Start with an empty tooltip
                        $tooltipText = "";

                        // Add submission timestamp if available
                        if (!empty($submittedTimestampFormatted)) {
                            $tooltipText .= "Esitatud $submittedTimestampFormatted";
                        }

                        // Add grading timestamp if available
                        if (!empty($gradedTimestampFormatted)) {
                            // Add a line break if we already have submission info
                            if (!empty($submittedTimestampFormatted)) {
                                $tooltipText .= "\n";
                            }

                            $tooltipText .= "Hinnatud $gradedTimestampFormatted";

                            // Add time difference between submission and grading if both timestamps are available
                            if (!empty($submittedTimestampFormatted) && $submittedTimestamp && $gradedTimestamp) {
                                $timeDiff = $this->getTimeDifferenceInEstonian($submittedTimestamp, $gradedTimestamp);
                                if (!empty($timeDiff)) {
                                    $tooltipText .= "\n($timeDiff hiljem)";
                                }
                            }
                        }
                    } elseif ($statusName === 'Kontrollimisel' && !empty($submittedTimestampFormatted)) {
                        $tooltipText = "Esitatud $submittedTimestampFormatted";

                        // Calculate days passed since submission for teachers
                        $daysPassed = null;
                        if ($submittedTimestamp) {
                            $now = new \DateTime();
                            $interval = $submittedTimestamp->diff($now);
                            $hoursPassed = $interval->h + ($interval->days * 24);

                            // Only set daysPassed if at least 24 hours have passed
                            if ($hoursPassed >= 24) {
                                $daysPassed = $interval->days;
                            }
                        }
                    } else {
                        $tooltipText = $statusName;
                    }
                }

                // Add or update assignment status
                $groups[$groupName]['subjects'][$subjectId]['assignments'][$assignmentId]['assignmentStatuses'][$studentId] = [
                    'userId' => $studentId,
                    'assignmentStatusName' => $statusName,
                    'grade' => $grade,
                    'class' => $class,
                    'tooltipText' => $tooltipText,
                    'gradedTimestamp' => $gradedTimestampFormatted,
                    'submittedTimestamp' => $submittedTimestampFormatted,
                    'daysPassed' => $daysPassed ?? null
                ];
            }
        }

        $this->statusClassMap = Assignment::statusClassMap($this->isStudent, $this->isTeacher);
        $this->groups = $groups;


    }
}
