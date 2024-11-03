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

        exit();

    }

    /**
     * This function is called by a cron job every day at 00:00
     */
    function assignmentDeadlinePassed()
    {
        $this->sendNotificationAboutDeadlinePassed();

        exit();

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
                Mail::send($teacherEmail, "Kriit vajab Sinu tähelepanu!", $messageBody);
            }
        }

    }

    function generateEmailMessageForStudentsWithPassedDeadlines($studentData): string
    {
        $messageBody = "<h3>Tähelepanu! Teil on esitamata ülesanded, mille tähtaeg on möödunud:</h3>";
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
            WHERE a.assignmentDueAt < CURDATE() AND ua.assignmentStatusId IS NULL OR ua.assignmentStatusId = 1
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

    private function getUnSynchronizedSubjects($teacherId): array
    {
        return Db::getAll("
            SELECT
                subj.subjectId, subj.subjectName
            FROM subjects subj
            WHERE subj.teacherId = ?
              AND subj.isSynchronized = 0
        ", [$teacherId]);
    }

    private function getTeachersWithUngradedAssignments(): array
    {
        $teachers = [];

        $data = Db::getAll("
        SELECT
            a.assignmentId, a.assignmentName, a.assignmentInstructions, a.assignmentDueAt,
            subj.subjectName, subj.isSynchronized, subj.subjectId, t.userId AS teacherId, t.userName AS teacherName, t.userEmail AS teacherEmail,
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

                // Initialize the teacher array if it does not exist
                if (!isset($teachers[$teacherEmail])) {
                    $teachers[$teacherEmail] = [
                        'teacherName' => $row['teacherName'],
                        'assignments' => [],
                        'unSynchronized' => $this->getUnSynchronizedSubjects($row['teacherId'])// Add this key for unsynchronized subjects
                    ];
                }

                $assignmentId = $row['assignmentId'];

                // Initialize the assignment array if it does not exist for this teacher
                if (!isset($teachers[$teacherEmail]['assignments'][$assignmentId])) {
                    $teachers[$teacherEmail]['assignments'][$assignmentId] = [
                        'assignmentName' => $row['assignmentName'],
                        'assignmentDueAt' => $row['assignmentDueAt'],
                        'subjectName' => $row['subjectName'],
                        'assignmentLink' => BASE_URL . "assignments/" . $assignmentId,
                        'students' => []
                    ];
                }

                // Add the student details to the assignment
                $teachers[$teacherEmail]['assignments'][$assignmentId]['students'][] = [
                    'studentName' => $row['studentName'],
                    'solutionUrl' => $row['solutionUrl']
                ];
            }
        }

        var_dump($teachers);
        return $teachers;
    }

    private function createTableWithUngradedWorks($teacherData): string
    {
        $teacherName = $teacherData['teacherName'];
        $messageBody = "<p>Tere, $teacherName,</p>";
        $messageBody .= "<p>Järgnevad ülesanded on veel hindamata:</p>";

        // Table header for ungraded assignments
        $messageBody .= "<table style='border-collapse: collapse; width: 100%; margin-top: 20px;'>";
        $messageBody .= "<thead style='background-color: #f2f2f2;'>";
        $messageBody .= "<tr>";
        $messageBody .= "<th style='border: 1px solid #ddd; padding: 8px; text-align: left;'>Ülesande nimi</th>";
        $messageBody .= "<th style='border: 1px solid #ddd; padding: 8px; text-align: left;'>Tähtaeg</th>";
        $messageBody .= "<th style='border: 1px solid #ddd; padding: 8px; text-align: left;'>Õpilased ja lahendused</th>";
        $messageBody .= "</tr>";
        $messageBody .= "</thead>";
        $messageBody .= "<tbody>";

        foreach ($teacherData['assignments'] as $assignment) {
            $messageBody .= "<tr style='background-color: #f9f9f9;'>";
            $messageBody .= "<td style='border: 1px solid #ddd; padding: 8px;'><a href='{$assignment['assignmentLink']}' style='color: #007bff; text-decoration: none;'>{$assignment['assignmentName']} ({$assignment['subjectName']})</a></td>";
            $messageBody .= "<td style='border: 1px solid #ddd; padding: 8px;'>" . date('d.m.Y', strtotime($assignment['assignmentDueAt'])) . "</td>";
            $messageBody .= "<td style='border: 1px solid #ddd; padding: 8px;'>";
            $messageBody .= "<ul style='list-style-type: none; padding: 0; margin: 0;'>";

            foreach ($assignment['students'] as $student) {
                $messageBody .= "<li style='margin-bottom: 5px;'>{$student['studentName']} (<a href='{$student['solutionUrl']}' style='color: #007bff; text-decoration: none;'>Lahendus</a>)</li>";
            }

            $messageBody .= "</ul>";
            $messageBody .= "</td>";
            $messageBody .= "</tr>";
        }

        $messageBody .= "</tbody>";
        $messageBody .= "</table>";

        $messageBody .= "<p>Palun hinda ülesandeid esimesel võimalusel.</p>";

        if (!empty($teacherData['unSynchronized'])) {
            $messageBody .= "<div style='margin-top: 5px; padding: 0 15px 0; background-color: #f9f9f9; border: 1px solid #ddd; border-radius: 5px;'>";
            $messageBody .= "<p style='font-weight: bold; font-size: 1.1em; color: #333;'>Järgnevad ained ei ole sünkroniseeritud:</p>";
            $messageBody .= "<ol style='padding-left: 20px; margin-top: 10px;'>";

            foreach ($teacherData['unSynchronized'] as $subject) {
                $messageBody .= "<li style='margin-bottom: 5px; color: #555; font-size: 1em;'>{$subject['subjectName']}</li>";
            }

            $messageBody .= "</ol>";
            $messageBody .= "</div>";
        }

        $messageBody .= "<p>Parimate soovidega,<br>Teie Kriit süsteem</p>";

        return $messageBody;
    }

    private function getStudentsWithNotSubmittedWorks($isDeadlinePassed = false): array
    {
        $assignments = [];
        if ($isDeadlinePassed) {
            $whereClause = "a.assignmentDueAt = DATE_SUB(CURDATE(), INTERVAL 1 DAY) AND (ua.assignmentStatusId IS NULL OR ua.assignmentStatusId = 1)";
        } else {
            $whereClause = "(a.assignmentDueAt = CURDATE() OR a.assignmentDueAt = DATE_ADD(CURDATE(), INTERVAL 1 DAY)) AND (ua.assignmentStatusId IS NULL OR ua.assignmentStatusId = 1)";
        }
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
                WHERE $whereClause
                ORDER BY u.userName
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
                    'assignmentId' => $assignmentId,
                    'studentId' => $entry['studentId'],
                    'userName' => $entry['studentName'],
                    'userEmail' => $entry['studentEmail']
                ];
            }
        }

        return $assignments;
    }

    private function sendNotificationAboutDeadlinePassed(): void
    {
        $assignments = $this->getStudentsWithNotSubmittedWorks(true);

        if (empty($assignments)) {
            return;
        }

        foreach ($assignments as $assignment) {
            if ($assignment['students']) {

                $this->gradeStudentsAssignmentWithPassedDeadline($assignment['students']);
                foreach ($assignment['students'] as $student) {
                    $assignmentUrl = BASE_URL . "assignments/" . $assignment['assignmentId'];
                    Mail::send(
                        $student['userEmail'],
                        "Ülesande tähtaeg on möödas!",
                        "<strong>Lugupeetud õppur!</strong><p>Teatame, et eile, " . date('d.m.Y', strtotime($assignment['assignmentDueAt'])) . ", oli aine '{$assignment['subjectName']}' ülesande '<a href='$assignmentUrl'>{$assignment['assignmentName']}</a>' esitamise tähtaeg. Kuna ülesannet ei esitatud õigeaegselt, on see hinnatud hindega 'MA' (hinne sünkroonitakse Kriidist Tahvlisse viitega).</p> <p>Vastavalt õppekorralduseeskirjale tuleb sellisel juhul osaleda esimesel võimalusel konsultatsioonis ja juhul, kui õppevõlg ei ole õppekorralduseeskirjas sätestatud tähtajaks likvideeritud, esitada seletuskiri korralduse mittetäitmise kohta läbi rühmajuhendaja osakonnajuhile.</p> <p>Palun võta esimesel võimalusel ühendust aine õpetajaga, et leppida kokku edasised tegevused.</p> <p>Parimate soovidega,<br>Kriit</p>"
                    );
                }
            }
        }
    }

    private function gradeStudentsAssignmentWithPassedDeadline($students): void
    {
        foreach ($students as $student) {
            try {
                Db::insert('userAssignments', [
                    'assignmentId' => $student['assignmentId'],
                    'userId' => $student['studentId'],
                    'assignmentStatusId' => 3,
                    'userGrade' => 'MA',
                ]);
            }catch (\Exception $e){
                stop(500, $e->getMessage());
            }
        }

    }

}
