<?php
namespace App;

class Assignment
{
    /**
     * Create a new assignment
     *
     * @param array $data Assignment data
     * @return int The new assignment ID
     */
    public static function create(array $data): int
    {
        return Db::insert('assignments', $data);
    }

    /**
     * Update an existing assignment
     *
     * @param int $assignmentId Assignment ID
     * @param array $data Data to update
     * @return bool Success
     */
    public static function update(int $assignmentId, array $data): bool
    {
        return Db::update('assignments', $data, 'assignmentId = ?', [$assignmentId]);
    }

    /**
     * Delete an assignment
     *
     * @param int $assignmentId Assignment ID
     * @return bool Success
     */
    public static function delete(int $assignmentId): bool
    {
        // First delete related user assignments
        Db::delete('userAssignments', 'assignmentId = ?', [$assignmentId]);

        // Then delete the assignment
        return Db::delete('assignments', 'assignmentId = ?', [$assignmentId]);
    }

    /**
     * Delete an assignment by external ID
     *
     * @param mixed $externalId External ID
     * @param int $systemId System ID
     * @return bool Success
     */
    public static function deleteByExternalId($externalId, int $systemId = 1): bool
    {
        // First get the assignment
        $assignment = self::getByExternalId($externalId, $systemId);

        if (!$assignment) {
            return false;
        }

        // Delete the assignment
        return self::delete($assignment['assignmentId']);
    }

    /**
     * Get assignment by ID
     *
     * @param int $assignmentId Assignment ID
     * @return array|null Assignment data
     */
    public static function getById(int $assignmentId): ?array
    {
        return Db::getFirst("SELECT * FROM assignments WHERE assignmentId = ?", [$assignmentId]);
    }

    /**
     * Get assignments for a subject
     *
     * @param int $subjectId Subject ID
     * @return array Assignments
     */
    public static function getBySubject(int $subjectId): array
    {
        return Db::getAll("SELECT * FROM assignments WHERE subjectId = ?", [$subjectId]);
    }

    /**
     * Submit an assignment solution
     *
     * @param int $assignmentId Assignment ID
     * @param int $studentId Student ID
     * @param string $solutionUrl Solution URL
     * @return bool Success
     */
    public static function submitSolution(int $assignmentId, int $studentId, string $solutionUrl): bool
    {
        $existingAssignment = Db::getFirst(
            "SELECT * FROM userAssignments WHERE userId = ? AND assignmentId = ?",
            [$studentId, $assignmentId]
        );

        $currentTime = date('Y-m-d H:i:s');

        if (!$existingAssignment) {
            return Db::insert('userAssignments', [
                'userId' => $studentId,
                'assignmentId' => $assignmentId,
                'assignmentStatusId' => ASSIGNMENT_STATUS_WAITING_FOR_REVIEW, // 2 = Waiting for review
                'solutionUrl' => $solutionUrl,
                'userAssignmentSubmittedAt' => $currentTime
            ]) > 0;
        } else {
            return Db::update('userAssignments',
                [
                    'assignmentStatusId' => ASSIGNMENT_STATUS_WAITING_FOR_REVIEW,
                    'solutionUrl' => $solutionUrl,
                    'userGrade' => NULL,
                    'userAssignmentSubmittedAt' => $currentTime
                ],
                'userId = ? AND assignmentId = ?',
                [$studentId, $assignmentId]
            );
        }
    }

    /**
     * Add a comment to an assignment
     *
     * @param int $assignmentId Assignment ID
     * @param int $studentId Student ID
     * @param string $comment Comment text
     * @return bool Success
     */
    public static function addComment(int $assignmentId, int $studentId, string $comment): bool
    {
        $existingAssignment = Db::getFirst(
            "SELECT * FROM userAssignments WHERE userId = ? AND assignmentId = ?",
            [$studentId, $assignmentId]
        );

        if (!$existingAssignment) {
            return false;
        }

        $comments = json_decode($existingAssignment['comments'] ?? '[]', true);
        $comments[] = [
            'comment' => $comment,
            'createdAt' => date('Y-m-d H:i:s')
        ];

        return Db::update('userAssignments',
            ['comments' => json_encode($comments)],
            'userId = ? AND assignmentId = ?',
            [$studentId, $assignmentId]
        );
    }
    /**
     * Creates an assignment from remote data and returns the new ID
     *
     * @param array $assignmentData The assignment data from the external system
     * @param int $subjectId The local subject ID
     * @param int $systemId The external system ID
     * @param int|null $teacherId The teacher ID for activity logging
     * @param string|null $subjectName The subject name for activity logging
     * @return int The new assignment ID
     */
    public static function createFromExternalData(
        array $assignmentData,
        int $subjectId,
        int $systemId,
        ?int $teacherId = null,
        ?string $subjectName = null
    ): int {
        // Check if required fields exist and set defaults if not
        $assignmentName = !empty($assignmentData['assignmentName']) ? $assignmentData['assignmentName'] : 'Unnamed assignment';
        $assignmentInstructions = !empty($assignmentData['assignmentInstructions']) ? $assignmentData['assignmentInstructions'] : '';
        $assignmentDueAt = !empty($assignmentData['assignmentDueAt']) ? $assignmentData['assignmentDueAt'] : null;
        $assignmentEntryDate = !empty($assignmentData['assignmentEntryDate']) ? $assignmentData['assignmentEntryDate'] : null;

        // Check if assignment already exists (to avoid duplicate key constraint)
        $existingAssignment = Db::getFirst(
            "SELECT assignmentId FROM assignments WHERE assignmentExternalId = ? AND systemId = ?",
            [$assignmentData['assignmentExternalId'], $systemId]
        );

        if ($existingAssignment) {
            // Assignment already exists, use its ID
            $newAssignId = $existingAssignment['assignmentId'];
            
            // Log that we're reusing an existing assignment
            if ($teacherId !== null) {
                Activity::create(ACTIVITY_CREATE_ASSIGNMENT_SYNC, $teacherId, $newAssignId, [
                    'systemId' => $systemId,
                    'assignmentName' => $assignmentName,
                    'assignmentExternalId' => $assignmentData['assignmentExternalId'],
                    'action' => 'reusing_existing_assignment',
                    'subjectId' => $subjectId,
                    'subjectName' => $subjectName ?? 'Unknown'
                ]);
            }
        } else {
            // Create new assignment
            Db::insert('assignments', [
                'subjectId'             => $subjectId,
                'assignmentName'        => $assignmentName,
                'assignmentExternalId'  => $assignmentData['assignmentExternalId'],
                'systemId'              => $systemId,
                'assignmentDueAt'       => $assignmentDueAt,
                'assignmentEntryDate'   => $assignmentEntryDate,
                'assignmentInstructions'=> $assignmentInstructions,
                'assignmentHours'       => isset($assignmentData['assignmentHours']) && $assignmentData['assignmentHours'] !== '' && is_numeric($assignmentData['assignmentHours']) ? (int)$assignmentData['assignmentHours'] : null
            ]);

            $newAssignId = Db::getOne("
                SELECT assignmentId FROM assignments
                WHERE subjectId=? AND assignmentExternalId=? AND systemId=?
            ", [$subjectId, $assignmentData['assignmentExternalId'], $systemId]);

            // Log assignment creation if teacher ID is provided
            if ($teacherId !== null) {
                Activity::create(ACTIVITY_CREATE_ASSIGNMENT_SYNC, $teacherId, $newAssignId, [
                    'systemId' => $systemId,
                    'assignmentName' => $assignmentName,
                    'assignmentExternalId' => $assignmentData['assignmentExternalId'],
                    'subjectId' => $subjectId,
                    'subjectName' => $subjectName ?? 'Unknown'
                ]);
            }
        }

        return $newAssignId;
    }

    /**
     * Add or update a grade for an assignment
     *
     * @param int $assignmentId The assignment ID
     * @param int $userId The user (student) ID
     * @param string $grade The grade value
     * @param int|null $teacherId The teacher ID for activity logging
     * @param int $systemId The external system ID for logging
     * @param string|null $subjectName The subject name for logging
     * @param string|null $assignmentName The assignment name for logging
     * @return bool True if a new grade was created, false if updated
     */
    public static function setGrade(
        int $assignmentId,
        int $userId,
        string $grade,
        ?int $teacherId = null,
        int $systemId = 1,
        ?string $subjectName = null,
        ?string $assignmentName = null,
        ?string $studentName = null
    ): bool {
        // Check if there's a userAssignment already
        $existingUA = Db::getFirst("
            SELECT * FROM userAssignments
            WHERE assignmentId=? AND userId=?
        ", [$assignmentId, $userId]);

        $isNew = false;

        // If no existing userAssignment, create with the grade
        if (!$existingUA) {
            $currentTime = date('Y-m-d H:i:s');
            Db::insert('userAssignments', [
                'assignmentId' => $assignmentId,
                'userId'       => $userId,
                'userGrade'    => $grade,
                'assignmentStatusId' => ASSIGNMENT_STATUS_GRADED, // 3 = Hinnatud
                'userAssignmentGradedAt' => $currentTime
            ]);
            $isNew = true;
        } else {
            // Update existing grade if grade has changed
            if ($existingUA['userGrade'] !== $grade) {
                $currentTime = date('Y-m-d H:i:s');
                Db::update('userAssignments', [
                    'userGrade' => $grade,
                    'assignmentStatusId' => ASSIGNMENT_STATUS_GRADED, // 3 = Hinnatud
                    'userAssignmentGradedAt' => $currentTime
                ], 'assignmentId = ? AND userId = ?', [$assignmentId, $userId]);
            } else {
                // No changes needed
                return false;
            }
        }

        // Log grade synchronization if teacher ID is provided
        if ($teacherId !== null) {
            Activity::create(ACTIVITY_GRADE_SYNC, $teacherId, $assignmentId, [
                'systemId' => $systemId,
                'studentId' => $userId,
                'studentName' => $studentName ?? 'Unknown',
                'assignmentName' => $assignmentName ?? 'Unknown',
                'grade' => $grade,
                'subjectName' => $subjectName ?? 'Unknown',
                'action' => $isNew ? 'created' : 'updated'
            ]);
        }

        return $isNew;
    }

    /**
     * Retrieves an assignment by its external ID and system ID
     *
     * @param string|int $externalId The external ID of the assignment
     * @param int $systemId The system ID
     * @param int $subjectId Optional subject ID to further filter the query
     * @return array|null The assignment data or null if not found
     */
    public static function getByExternalId($externalId, int $systemId, ?int $subjectId = null): ?array
    {
        $params = [$externalId, $systemId];
        $subjectClause = '';

        if ($subjectId !== null) {
            $subjectClause = ' AND subjectId = ?';
            $params[] = $subjectId;
        }

        return Db::getFirst("
            SELECT * FROM assignments
            WHERE assignmentExternalId = ? AND systemId = ?{$subjectClause}
        ", $params);
    }

    public static function statusClassMap($isStudent, $isTeacher): array
    {
        return [
            'Esitamata' => $isStudent ? 'yellow-cell' : '',
            'Kontrollimisel' => $isTeacher ? 'red-cell' : '',
        ];
    }

    public static function cellColor($isStudent, $isTeacher, $isNegGrade, $daysLeft, $statusId, $statusName): string
    {
        if ($isStudent && $isNegGrade) {
            return $statusName == 'Kontrollimisel' ? 'yellow-cell' : 'red-cell';
        }
        if ($daysLeft <= 0) {
            if (($isStudent && $statusId == ASSIGNMENT_STATUS_NOT_SUBMITTED) ||
                ($isTeacher && $statusId == ASSIGNMENT_STATUS_WAITING_FOR_REVIEW))
                return 'red-cell';
            if ($isTeacher && $statusId == ASSIGNMENT_STATUS_NOT_SUBMITTED || $isNegGrade)
                return 'yellow-cell';
            return '';
        }

        if ($isTeacher && $statusId != ASSIGNMENT_STATUS_WAITING_FOR_REVIEW) return '';

        return self::statusClassMap($isStudent, $isTeacher)[$statusName] ?? '';
    }
}