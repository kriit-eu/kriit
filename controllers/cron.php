<?php namespace App;

use App\Mail;
use DateTime;

class cron extends Controller
{
    public $requires_auth = false;

    function index(): void
    {
        $this->sendNotificationAboutDeadline();
        $this->sendNotificationAboutUngradedAssignments();
        $this->sendNotificationAboutAssignmentsWithOverDeadlines();

        stop(200, 'Emails sent successfully');

    }


    private function sendNotificationAboutAssignmentsWithOverDeadlines(): void
    {

        $students = $this->StudentsWithPassedDeadlines();

        if (!empty($students)) {
            foreach ($students as $studentId => $studentData) {
                $messageBody = $this->generateEmailMessageForStudentsWithPassedDeadlines($studentData);
                $studentEmail = $studentData['studentEmail'];
                $subject = "Esitamata ülesanded!";
                Mail::send($studentEmail, $subject, $messageBody);
            }
        }
    }


    private function sendNotificationAboutDeadline(): void
    {
        $assignments = $this->getStudentsWithNotSubmittedWorks();

        if (empty($assignments)) {
            return;
        }

        foreach ($assignments as $assignment) {
            $mailData = $this->getSubjectAndBodyTemplateForMail($assignment['assignmentDueAt'], $assignment['subjectName'], $assignment['assignmentName'], $assignment['assignmentId']);
            $this->sendEmailsToStudents($assignment, $mailData['subject'], $mailData['bodyTemplate'], $assignment['teacherName']);
        }


    }

    private function getSubjectAndBodyTemplateForMail($dueAt, $subjectName, $assignmentName, $assignmentId): array
    {
        $assignmentLink = BASE_URL . "assignments/" . $assignmentId;
        if (!empty($dueAt) && $dueAt == date('Y-m-d', strtotime('+1 day'))) {
            $subject = "Tähtaeg homme! $subjectName: {$assignmentName}";
            $bodyTemplate = "<p>Tere, {studentName},</p>"
                . "<p>See on sõbralik meeldetuletus, et aines '{subjectName}' on homme, {assignmentDueDate}, ülesande '<a href=\"{$assignmentLink}\">{$assignmentName}</a>' tähtaeg.</p>"
                . "<p>Palun veendu, et oled oma ülesande esitanud enne tähtaega.</p>"
                . "<p>Kui sul on küsimusi või vajad abi, võta kindlasti ühendust.</p>"
                . "<p>Parimate soovidega,<br>{teacherName}</p>";
        } elseif (!empty($dueAt) && $dueAt == date('Y-m-d')) {
            $subject = "Ära jää hiljaks! {$subjectName}: {$assignmentName}";
            $bodyTemplate = "<p>Tere, {studentName},</p>"
                . "<p>Tähelepanu! Täna, {assignmentDueDate}, on aines '{subjectName}' ülesande '<a href=\"{$assignmentLink}\">{$assignmentName}</a>' tähtaeg! Tegutse kohe, et mitte tähtaega maha magada!</p>"
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


    function sendNotificationAboutUngradedAssignments(): void
    {
        $teachers = $this->getTeachersWithUngradedAssignments();

        if (empty($teachers)) {
            return;
        }

        foreach ($teachers as $teacherEmail => $teacherData) {

            $messageBody = $this->createTableWithUngradedWorks($teacherData);

            if (!empty($teacherEmail) && !empty(trim($teacherEmail))) {
                Mail::send($teacherEmail, "Hindamata ülesanded!", $messageBody);
            }
        }

    }

    function generateEmailMessageForStudentsWithPassedDeadlines($studentData): string
    {
        $messageBody = "<h3>Tähelepanu! Teil {} on esitamata ülesanded, mille tähtaeg on möödunud:</h3>";
        $messageBody .= "<table border='1' cellpadding='10' cellspacing='0' style='border-collapse: collapse; width: 100%;'>";
        $messageBody .= "<thead>";
        $messageBody .= "<tr>";
        $messageBody .= "<th>Aine nimi</th>";
        $messageBody .= "<th>Ülesande nimi</th>";
        $messageBody .= "<th>Tähtaeg</th>";
        $messageBody .= "<th>Õpetaja</th>";
        $messageBody .= "</tr>";
        $messageBody .= "</thead>";
        $messageBody .= "<tbody>";

        foreach ($studentData['advertisements'] as $assignment) {
            $assignmentLink = BASE_URL . "assignments/" . $assignment['assignmentId'];
            $messageBody .= "<tr>";
            $messageBody .= "<td>{$assignment['subjectName']}</td>";
            $messageBody .= "<td><a href=\"{$assignmentLink}\">{$assignment['assignmentName']}</a></td>";
            $messageBody .= "<td>" . date('d.m.Y', strtotime($assignment['assignmentDueAt'])) . "</td>";
            $messageBody .= "<td>{$assignment['teacherName']}</td>";
            $messageBody .= "</tr>";
        }

        $messageBody .= "</tbody>";
        $messageBody .= "</table>";
        $messageBody .= "<p><b>Palun esitage need ülesanded esimesel võimalusel.</b></p>";

        return $messageBody;

    }

    private function StudentsWithPassedDeadlines(): array
    {
        $students = [];
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
            WHERE a.assignmentDueAt < CURDATE() AND ua.assignmentStatusId IS NULL
        ");

        if (!empty($data)) {
            foreach ($data as $entry) {
                $studentId = $entry['studentId'];
                if (empty($studentId)) {
                    continue;
                }

                if (!isset($students[$studentId])) {
                    $students[$studentId] = [
                        'studentName' => $entry['studentName'],
                        'studentEmail' => $entry['studentEmail'],
                        'advertisements' => []
                    ];
                }

                $students[$studentId]['advertisements'][] = [
                    'assignmentId' => $entry['assignmentId'],
                    'assignmentName' => $entry['assignmentName'],
                    'assignmentInstructions' => $entry['assignmentInstructions'],
                    'assignmentDueAt' => $entry['assignmentDueAt'],
                    'subjectName' => $entry['subjectName'],
                    'teacherName' => $entry['teacherName']
                ];
            }
        }

        return $students;
    }

    private function getTeachersWithUngradedAssignments(): array
    {
        $teachers = [];

        $data = Db::getAll("
        SELECT
            a.assignmentId, a.assignmentName, a.assignmentInstructions, a.assignmentDueAt,
            subj.subjectName, t.userName AS teacherName, t.userEmail AS teacherEmail,
            u.userId AS studentId, u.userName AS studentName, u.groupId,
            ua.assignmentStatusId, ua.userGrade, ua.solutionUrl
        FROM assignments a
        JOIN subjects subj ON a.subjectId = subj.subjectId
        JOIN users t ON subj.teacherId = t.userId
        LEFT JOIN groups g ON subj.groupId = g.groupId
        LEFT JOIN users u ON u.groupId = g.groupId
        LEFT JOIN userAssignments ua ON ua.assignmentId = a.assignmentId AND ua.userId = u.userId
        WHERE ua.assignmentStatusId IS NOT NULL
          AND ua.assignmentStatusId = 2
        GROUP BY a.assignmentName, u.userName
        ORDER BY a.assignmentName, u.userName
    ");

        if (!empty($data)) {

            foreach ($data as $row) {
                $teacherEmail = $row['teacherEmail'];

                if (!isset($teachers[$teacherEmail])) {
                    $teachers[$teacherEmail] = [
                        'teacherName' => $row['teacherName'],
                        'assignments' => []
                    ];
                }

                $assignmentId = $row['assignmentId'];

                if (!isset($teachers[$teacherEmail]['assignments'][$assignmentId])) {
                    $teachers[$teacherEmail]['assignments'][$assignmentId] = [
                        'assignmentName' => $row['assignmentName'],
                        'assignmentDueAt' => $row['assignmentDueAt'],
                        'subjectName' => $row['subjectName'],
                        'assignmentLink' => BASE_URL . "assignments/" . $assignmentId,
                        'students' => []
                    ];
                }

                $teachers[$teacherEmail]['assignments'][$assignmentId]['students'][] = [
                    'studentName' => $row['studentName'],
                    'solutionUrl' => $row['solutionUrl']
                ];
            }
        }

        return $teachers;
    }

    private function createTableWithUngradedWorks($teacherData): string
    {
        $teacherName = $teacherData['teacherName'];
        $messageBody = "<p>Tere, $teacherName,</p>";
        $messageBody .= "<p>Järgnevad ülesanded on veel hindamata:</p>";

        // Table header
        $messageBody .= "<table border='1' cellpadding='10' cellspacing='0' style='border-collapse: collapse; width: 100%;'>";
        $messageBody .= "<thead>";
        $messageBody .= "<tr>";
        $messageBody .= "<th>Ülesande nimi</th>";
        $messageBody .= "<th>Tähtaeg</th>";
        $messageBody .= "<th>Õpilased ja lahendused</th>";
        $messageBody .= "</tr>";
        $messageBody .= "</thead>";
        $messageBody .= "<tbody>";

        foreach ($teacherData['assignments'] as $assignment) {
            $messageBody .= "<tr>";
            $messageBody .= "<td><a href='{$assignment['assignmentLink']}'>{$assignment['assignmentName']} ({$assignment['subjectName']})</a></td>";
            $messageBody .= "<td>" . date('d.m.Y', strtotime($assignment['assignmentDueAt'])) . "</td>";
            $messageBody .= "<td>";
            $messageBody .= "<ul>";

            foreach ($assignment['students'] as $student) {
                $messageBody .= "<li>{$student['studentName']} (<a href='{$student['solutionUrl']}'>Lahendus</a>)</li>";
            }

            $messageBody .= "</ul>";
            $messageBody .= "</td>";
            $messageBody .= "</tr>";
        }

        $messageBody .= "</tbody>";
        $messageBody .= "</table>";

        $messageBody .= "<p>Palun hinda ülesandeid esimesel võimalusel.</p>";
        $messageBody .= "<p>Parimate soovidega,<br>Teie Kriit süsteem</p>";

        return $messageBody;
    }

    private function getStudentsWithNotSubmittedWorks(): array
    {
        $assignments = [];
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


        if (!empty($data)) {
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
        }

        return $assignments;
    }

}
