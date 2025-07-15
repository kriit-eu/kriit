<?php

namespace App;

use App\Db;
use App\User;

/**
 * AssignmentExport: Exports Kriit assignments in the format required for Tahvel sync.
 */
class AssignmentExport
{
    /**
     * Export all assignments created in Kriit, grouped by subject, in Tahvel sync format.
     * @param int $systemId The system ID for Kriit (default: 1)
     * @return array
     */
    public static function exportForTahvel($systemId = 1)
    {
        // Query all subjects for this system
        $subjects = Db::getAll("SELECT * FROM subjects WHERE systemId = ?", [$systemId]);

        $result = [];
        foreach ($subjects as $subject) {
            // Get assignments for this subject
            $assignments = Db::getAll("SELECT * FROM assignments WHERE subjectId = ?", [$subject['subjectId']]);

            $assignmentArr = [];
            foreach ($assignments as $assignment) {
                $assignmentArr[] = [
                    'assignmentExternalId'    => $assignment['assignmentExternalId'],
                    'assignmentName'          => $assignment['assignmentName'],
                    'assignmentInstructions'  => $assignment['assignmentInstructions'],
                    'assignmentDueAt'         => $assignment['assignmentDueAt'],
                    'assignmentEntryDate'     => $assignment['assignmentEntryDate']
                ];
            }

            // Fetch groupName if available
            $groupName = null;
            if (!empty($subject['groupId'])) {
                $group = Db::getFirst("SELECT groupName FROM groups WHERE groupId = ?", [$subject['groupId']]);
                if ($group) $groupName = $group['groupName'];
            }
            // Fetch teacher info if available
            $teacherPersonalCode = null;
            $teacherName = null;
            if (!empty($subject['teacherId'])) {
                $teacher = Db::getFirst("SELECT userPersonalCode, userName FROM users WHERE userId = ?", [$subject['teacherId']]);
                if ($teacher) {
                    $teacherPersonalCode = $teacher['userPersonalCode'];
                    $teacherName = $teacher['userName'];
                }
            }

            $result[] = [
                'subjectExternalId'    => $subject['subjectExternalId'],
                'groupName'            => $groupName,
                'teacherPersonalCode'  => $teacherPersonalCode,
                'teacherName'          => $teacherName,
                'assignments'          => $assignmentArr
            ];
        }
        return $result;
    }
}
