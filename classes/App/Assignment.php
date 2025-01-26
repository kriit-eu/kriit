<?php

namespace App;

use App\api\assignments;
use Exception;

class Assignment
{
    public static function get(int $assignmentId, int $studentId): array
    {
        $assignment = Db::getFirst("
            SELECT
                -- Assignment
                a.assignmentId, a.assignmentName, a.assignmentInstructions, a.assignmentDueAt,
                
                -- Student
                s.userId AS studentId, s.userName AS studentName, s.userEmail AS studentEmail, s.groupId,
                
                -- Teacher
                subj.teacherId AS teacherId, t.userName AS teacherName, t.userEmail AS teacherEmail,
                
                -- Others
                ast.assignmentStatusId as assignmentStatusId, ast.statusName as assignmentStatusName,
                grade, solutionUrl, subj.subjectName AS subjectName
            FROM assignments a
            JOIN subjects subj ON a.subjectId = subj.subjectId
            JOIN users t ON subj.teacherId = t.userId
            JOIN users s ON s.groupId = subj.groupId AND s.userId = ?
            LEFT JOIN userAssignments ua ON ua.assignmentId = a.assignmentId AND ua.userId = s.userId
            LEFT JOIN assignmentStatuses ast ON ast.assignmentStatusId = COALESCE(ua.assignmentStatusId, 1)
            WHERE a.assignmentId = ?
            GROUP BY a.assignmentId, s.userId
        ", [$studentId, $assignmentId]) ?? [];

        if ($assignment) {
            $assignment['criteria'] = self::criteria($assignmentId, $studentId);
            $assignment['comments'] = self::comments($assignmentId, $studentId);
            $assignment['assignmentDueAt'] = !empty($assignment['assignmentDueAt'])
                ? date('d.m.Y', strtotime($assignment['assignmentDueAt']))
                : 'Pole määratud';
        }

        return $assignment;
    }

    public static function statusClassMap($isStudent, $isTeacher): array
    {
        return [
            'Esitamata' => $isStudent ? 'yellow-cell' : '',
            'Kontrollimisel' => $isTeacher ? 'red-cell' : '',
        ];
    }

    public static function userIsTeacher($userId, $assignmentId)
    {

        return !!Db::getOne('SELECT teacherId FROM assignments JOIN subjects USING (subjectId) WHERE assignmentId = ? AND teacherId = ?', [$assignmentId, $userId]);
    }

    public static function getTeacherId(int $assignmentId): int
    {
        return Db::getOne('SELECT teacherId FROM assignments JOIN subjects USING (subjectId) WHERE assignmentId = ?', [$assignmentId]);
    }

    public static function userIsStudent($userId, $assignmentId)
    {

        return !!Db::getOne('SELECT userId FROM assignments JOIN subjects USING (subjectId) JOIN users USING (groupId) WHERE assignmentId = ? AND userId = ?', [$assignmentId, $userId]);
    }

    public static function cellColor($isStudent, $isTeacher, $isNegGrade, $daysLeft, $statusId, $statusName): string
    {
        if ($isStudent && $isNegGrade) return 'red-cell';

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

    static function addComment($assignmentId, $userId, $assignmentCommentAuthorId, $assignmentCommentText, bool $isProposedSolution = false): int
    {
        @validate($assignmentCommentText, 'Invalid comment. It must be a string.', IS_STRING);
        @validate($assignmentId, 'Invalid assignmentId.');

        return Db::insert('assignmentComments', [
            'assignmentId' => $assignmentId,
            'userId' => $userId,
            'assignmentCommentAuthorId' => $assignmentCommentAuthorId,
            'assignmentCommentText' => $assignmentCommentText,
            'assignmentCommentCreatedAt' => date('Y-m-d H:i:s'),
            'assignmentCommentIsProposedSolution' => $isProposedSolution ? 1 : 0
        ]);

    }

    public static function criteria(int $assignmentId, int $userId): array
    {
        return Db::getAll("
            SELECT 
                c.*, 
                IF(udc.criterionId IS NOT NULL, 1, 0) as done 
            FROM criteria c 
            LEFT JOIN userDoneCriteria udc 
                ON udc.criterionId = c.criterionId 
                AND udc.userId = ? 
            WHERE c.assignmentId = ?",
            [$userId, $assignmentId]
        );
    }

    public static function comments(int $assignmentId, int $userId): array
    {
        return Db::getAll("
            SELECT
                assignmentCommentId,
                assignmentCommentText,
                assignmentCommentGrade,
                assignmentCommentAuthorId,
                assignmentCommentCreatedAt,
                assignmentCommentIsProposedSolution,
                u.userName as assignmentCommentAuthorName
            FROM assignmentComments ac
            JOIN users u ON u.userId = ac.assignmentCommentAuthorId 
            WHERE ac.assignmentId = ? AND ac.userId = ?",
            [$assignmentId, $userId]
        );
    }

    /**
     * @param int $assignmentCommentId
     * @return array|false|null
     */
    public static function getComment(int $assignmentCommentId): array|null|false
    {
        return Db::getFirst("
            SELECT assignmentCommentId, 
                   assignmentCommentText, 
                   assignmentCommentCreatedAt, 
                   assignmentCommentGrade, userName as assignmentCommentAuthorName 
            FROM assignmentComments c 
            JOIN users u ON c.assignmentCommentAuthorId = u.userId 
            WHERE assignmentCommentId = ?", [$assignmentCommentId]);
    }

}
