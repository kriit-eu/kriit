<?php namespace App;

class subjects extends Controller
{
    public $template = 'master';

    function index()
    {
        $this->isStudent = $this->auth->groupId && !$this->auth->userIsAdmin && !$this->auth->userIsTeacher;
        $this->isTeacher = !$this->auth->userIsAdmin && $this->auth->userIsTeacher;

        $conditions = [
            "s.teacherId = {$this->auth->userId}",
            $this->auth->groupId ? "s.groupId = {$this->auth->groupId}" : null,
            $this->auth->userIsAdmin ? 'true' : null
        ];

        $whereClause = implode(' OR ', array_filter($conditions));

        $this->data = Db::getAll("SELECT
            s.subjectId,
            s.subjectName,
            s.teacherId,
            t.userName AS teacherName,
            u.userId AS studentId,
            u.userName AS studentName,
            u.groupId,
            g.groupName,
            a.assignmentId,
            a.assignmentName,
            a.assignmentDueAt,
            ua.userGrade,
            ua.assignmentStatusId,
            ast.statusName AS assignmentStatusName
        FROM
            subjects s
        JOIN
            users t ON s.teacherId = t.userId
        JOIN
            groups g ON s.groupId = g.groupId
        JOIN
            users u ON u.groupId = g.groupId
        LEFT JOIN
            assignments a ON a.subjectId = s.subjectId
        LEFT JOIN
            userAssignments ua ON ua.assignmentId = a.assignmentId AND ua.userId = u.userId
        LEFT JOIN
            assignmentStatuses ast ON ua.assignmentStatusId = ast.assignmentStatusId
        WHERE
           {$whereClause}
            ORDER BY
            g.groupName,
            u.userName,
            s.subjectName,
            a.assignmentDueAt");


        $groups = [];

        foreach ($this->data as $row) {
            $groupName = $row['groupName'];
            $studentId = $row['studentId'];
            $studentName = $row['studentName'];
            $subjectId = $row['subjectId'];
            $subjectName = $row['subjectName'];
            $teacherName = $row['teacherName'];
            $assignmentId = $row['assignmentId'];
            $assignmentName = $row['assignmentName'];
            $assignmentDueAt = $row['assignmentDueAt'];
            $assignmentStatusName = $row['assignmentStatusName'];
            $grade = $row['userGrade'];

            if ($this->isStudent) {
                if ($studentId !== $this->auth->userId) {
                    continue;
                }
            }

            if (!isset($groups[$groupName])) {
                $groups[$groupName] = [
                    'groupName' => $groupName,
                    'students' => [],
                    'subjects' => []
                ];
            }

            if (!isset($groups[$groupName]['students'][$studentId])) {
                $groups[$groupName]['students'][$studentId] = [
                    'userName' => $studentName,
                    'subjectId' => $subjectId,
                    'status' => $assignmentStatusName,
                    'userId' => $studentId
                ];
            }

            if (!isset($groups[$groupName]['subjects'][$subjectId])) {
                $groups[$groupName]['subjects'][$subjectId] = [
                    'subjectId' => $subjectId,
                    'subjectName' => $subjectName,
                    'teacherName' => $teacherName,
                    'assignments' => []
                ];
            }

            if ($assignmentId) {
                if (!isset($groups[$groupName]['subjects'][$subjectId]['assignments'][$assignmentId])) {
                    $groups[$groupName]['subjects'][$subjectId]['assignments'][$assignmentId] = [
                        'assignmentId' => $assignmentId,
                        'assignmentName' => $assignmentName,
                        'assignmentDueAt' => $assignmentDueAt,
                        'assignmentStatuses' => []
                    ];
                }

                $groups[$groupName]['subjects'][$subjectId]['assignments'][$assignmentId]['assignmentStatuses'][$studentId] = [
                    'userId' => $studentId,
                    'assignmentStatusName' => $assignmentStatusName,
                    'grade' => $grade
                ];
            }
        }

        $groups = array_values($groups);
        foreach ($groups as &$group) {
            $group['students'] = array_values($group['students']);
            $group['subjects'] = array_values($group['subjects']);
            foreach ($group['subjects'] as &$subject) {
                $subject['assignments'] = array_values($subject['assignments']);
            }
        }

        $this->groups = $groups;
    }
}
