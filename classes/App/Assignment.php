<?php
namespace App;

use App\api\assignments;

class Assignment
{
    public static function statusClassMap($isStudent, $isTeacher): array
    {
        return [
            'Esitamata' => $isStudent ? 'yellow-cell' : '',
            'Kontrollimisel' => $isTeacher ? 'red-cell' : '',
        ];
    }

    public static function userIsTeacher($userId, $assignmentId) {

        return !!Db::getOne('SELECT teacherId FROM assignments JOIN subjects USING (subjectId) WHERE assignmentId = ? AND teacherId = ?', [$assignmentId, $userId]);
    }

    public static function userIsStudent($userId, $assignmentId) {

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

    static function addComment($assignmentId, $studentId, $authorId, $comment, $isSolution = false): void
    {
        @validate($comment, 'Invalid comment. It must be a string.', IS_STRING);
        @validate($assignmentId, 'Invalid assignmentId.');

        Db::insert('assignmentComments', [
            'assignmentId' => $assignmentId,
            'studentId' => $studentId,
            'authorId' => $authorId,
            'assignmentComment' => $comment,
            'isSolution' => $isSolution,
            'assignmentCommentCreatedAt' => date('Y-m-d H:i:s')
        ]);

    }
}
