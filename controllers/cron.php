<?php namespace App;

use App\Mail;
use DateTime;

class cron extends Controller
{
    function index(): void
    {
        //Get all assignments that are due tomorrow  with the group id using subject and users from that group who is not in userAssignment or assignmentStatusId is 1 in one query
        $data = Db::getAll("
                SELECT
                    a.assignmentId, a.assignmentName, a.assignmentInstructions, a.assignmentDueAt,
                    subj.subjectName, t.userName AS teacherName,
                    u.userId AS studentId, u.userName AS studentName, u.groupId, u.userEmail AS studentEmail,
                    ua.assignmentStatusId
                FROM assignments a
                JOIN subjects subj ON a.subjectId = subj.subjectId
                JOIN users t ON subj.teacherId = t.userId
                LEFT JOIN groups g ON subj.groupId = g.groupId
                LEFT JOIN users u ON u.groupId = g.groupId
                LEFT JOIN userAssignments ua ON ua.assignmentId = a.assignmentId AND ua.userId = u.userId
                WHERE (a.assignmentDueAt = CURDATE() OR a.assignmentDueAt = DATE_ADD(CURDATE(), INTERVAL 1 DAY)) AND (ua.assignmentStatusId IS NULL OR ua.assignmentStatusId = 1)                ORDER BY u.userName
            ");

        $assignments = [];

        foreach ($data as $entry) {
            $assignmentId = $entry['assignmentId'];

            if (!isset($assignments[$assignmentId])) {
                $assignments[$assignmentId] = [
                    'assignmentId' => $assignmentId,
                    'teacherName' => $entry['teacherName'],
                    'assignmentName' => $entry['assignmentName'],
                    'assignmentInstructions' => $entry['assignmentInstructions'],
                    'assignmentDueAt' => $entry['assignmentDueAt'],
                    'subjectName' => $entry['subjectName'],
                    'students' => []
                ];
            }

            $assignments[$assignmentId]['students'][] = [
                'userName' => $entry['studentName'],
                'userEmail' => $entry['studentEmail']
            ];
        }


        foreach ($assignments as $assignment) {
            $mailData = $this->getSubjectAndBodyTemplateForMail($assignment['assignmentDueAt'], $assignment['subjectName'], $assignment['assignmentName'], $assignment['assignmentId']);
            $this->sendEmailsToStudents($assignment, $mailData['subject'], $mailData['bodyTemplate'], $assignment['teacherName']);
        }

        stop(200, 'Emails sent successfully');

    }

    private function getSubjectAndBodyTemplateForMail($dueAt, $subjectName, $assignmentName, $assignmentId): array
    {
        $assignmentLink = BASE_URL . "assignments/" . $assignmentId;
        if (!empty($dueAt) && $dueAt == date('Y-m-d', strtotime('+1 day'))) {
            $subject = "Tähtaeg homme! $subjectName: {$assignmentName}";
            $bodyTemplate = "<p>Tere, {studentName},</p>"
                . "<p>See on sõbralik meeldetuletus, et aines '{subjectName}' on homme, {assignmentDueDate}, ülesande '{assignmentName}' tähtaeg. <br>Ülesande link: <a href=\"{$assignmentLink}\">{$assignmentName}</a></p>"
                . "<p>Palun veendu, et oled oma ülesande esitanud enne tähtaega.</p>"
                . "<p>Kui sul on küsimusi või vajad abi, võta kindlasti ühendust.</p>"
                . "<p>Parimate soovidega,<br>{teacherName}</p>";
        } elseif (!empty($dueAt) && $dueAt == date('Y-m-d')) {
            $subject = "Ära jää hiljaks! {$subjectName}: {$assignmentName}";
            $bodyTemplate = "<p>Tere, {studentName},</p>"
                . "<p>Tähelepanu! Täna, {assignmentDueDate}, on aines '{subjectName}' ülesande '{assignmentName}' tähtaeg! Tegutse kohe, et mitte tähtaega maha magada!<br> Ülesande link: <a href=\"{$assignmentLink}\">{$assignmentName}</a></p>"
                . "<p>Palun veendu, et oled oma ülesande esitanud enne tähtaega.</p>"
                . "<p>Kui sul on küsimusi või vajad abi, võta kindlasti ühendust.</p>"
                . "<p>Parimate soovidega,<br>{teacherName}</p>";

        }

        return ["subject" => $subject, "bodyTemplate" => $bodyTemplate];
    }

    private function sendEmailsToStudents($assignment, $subject, $bodyTemplate, $teacherName): void
    {
        foreach ($assignment['students'] as $student) {
            $message = str_replace(
                ['{studentName}', '{subjectName}', '{assignmentName}', '{assignmentDueDate}', '{teacherName}'],
                [$student['userName'],
                    $assignment['subjectName'],
                    $assignment['assignmentName'],
                    (new DateTime($assignment['assignmentDueAt']))->format('d.m.Y'),
                    $teacherName],
                $bodyTemplate
            );

            if (!empty($student['userEmail'])) {
                Mail::send($student['userEmail'], $subject, $message);
            }
        }
    }


}
