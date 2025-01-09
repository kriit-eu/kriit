<?php

namespace App;

class Notify
{
    /**
     * @throws \Exception
     */
    public static function studentAboutGrade($studentId, $assignmentId, $grade, $feedback): void
    {
        Validate::grade($grade, 'Invalid grade', true);
        Validate::string($feedback, 'Invalid feedback', true);
        Validate::id($studentId, 'Invalid studentId');
        Validate::id($assignmentId, 'Invalid assignmentId');

        // Immediately throw if Assignment::get returns null/false.
        $assignment = Assignment::get($assignmentId, $studentId)
            ?? throw new \Exception("Assignment not found for student $studentId and assignment $assignmentId");

        // Immediately throw if student cannot be fetched.
        $student = Db::getFirst("SELECT userName, userEmail FROM users WHERE userId = ?", [$studentId])
            ?? throw new \Exception("Student not found for ID $studentId");

        // Immediately throw if student has no email.
        $email = $student['userEmail']
            ?? throw new \Exception("Student $studentId has no email");

        // Destructure assignment keys to variables.
        ['subjectName' => $subjectName, 'teacherName' => $teacherName, 'assignmentName' => $assignmentName] = $assignment;

        // Build the link
        $link = BASE_URL . "assignments/$assignmentId/$studentId/" . slugify($student['userName']);

        Mail::send(
            $email,
            "$subjectName: $teacherName hindas teie esitatud lahendust ülesandele '$assignmentName'",
            "<strong>$teacherName</strong> hindas teie esitatud lahendust ülesandele "
            . "'<strong>$assignmentName</strong>'.<br><br>"
            . "Ülesande link: <a href='$link'>$link</a><br>"
        );
    }
}