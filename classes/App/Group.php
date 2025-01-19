<?php namespace App;

class Group {
    public static function getAll($userId, $isAdmin) {
        $whereClauses = [];
        $params = [];

        if (!$isAdmin) {
            // Teacher check
            $teacherGroupIds = Db::getCol("
                SELECT DISTINCT groupId
                FROM subjects
                WHERE teacherId = ?
            ", [$userId]);

            if (!empty($teacherGroupIds)) {
                // Teacher can see only his/her group(s)
                $whereClauses[] = "g.groupId IN (" . implode(',', $teacherGroupIds) . ")";
                $whereClauses[] = "s.teacherId = :teacherId";
                $params['teacherId'] = $userId;
            } else {
                // A student can only see his/her data
                $myGroupId = Db::getOne("
                    SELECT groupId 
                    FROM users 
                    WHERE userId = ? AND userDeleted = 0
                ", [$userId]);

                if ($myGroupId) {
                    $whereClauses[] = "g.groupId = :myGroupId";
                    $params['myGroupId'] = $myGroupId;
                    $whereClauses[] = "u.userId = :myUserId";
                    $params['myUserId'] = $userId;
                } else {
                    $whereClauses[] = "0 = 1";
                }
            }
        }

        $whereSql = !empty($whereClauses) ? "WHERE " . implode(" AND ", $whereClauses) : "";

        $sql = "
            SELECT 
                g.groupName,
                u.userId, 
                u.userName AS studentName,
                s.subjectId, 
                s.subjectName,
                s.gradingSystem,
                t.userName AS teacherName,
                t.userId   AS teacherId,
                a.assignmentId, 
                a.assignmentName, 
                a.assignmentDueAt,
                COALESCE(ua.assignmentStatusId, 1) AS assignmentStatusId,
                COALESCE(asn.statusName, 'Esitamata') AS assignmentStatusName,
                COALESCE(ua.grade, '') AS grade
            FROM `groups` g
            JOIN subjects s ON s.groupId = g.groupId
            JOIN users t ON t.userId = s.teacherId
            JOIN users u ON u.groupId = g.groupId AND u.userDeleted = 0
            LEFT JOIN assignments a ON a.subjectId = s.subjectId
            LEFT JOIN userAssignments ua ON ua.assignmentId = a.assignmentId AND ua.userId = u.userId
            LEFT JOIN assignmentStatuses asn ON asn.assignmentStatusId = ua.assignmentStatusId
            $whereSql
            ORDER BY g.groupName, s.subjectId, a.assignmentId, u.userName ASC
        ";

        $rows = Db::getAll($sql, $params);
        return self::formatResults($rows);
    }

    private static function formatResults($rows) {
        $groups = [];
        foreach ($rows as $r) {
            extract($r);

            if (!isset($groups[$groupName])) {
                $groups[$groupName] = [
                    'groupName' => $groupName,
                    'students'  => [],
                    'subjects'  => []
                ];
            }

            if (!isset($groups[$groupName]['students'][$userId])) {
                $groups[$groupName]['students'][$userId] = [
                    'userId'   => $userId,
                    'userName' => $studentName,
                ];
            }

            if (!isset($groups[$groupName]['subjects'][$subjectId])) {
                $groups[$groupName]['subjects'][$subjectId] = [
                    'subjectId'   => $subjectId,
                    'subjectName' => $subjectName,
                    'teacherName' => $teacherName,
                    'gradingSystem' => $gradingSystem,
                    'assignments' => []
                ];
            }

            if ($assignmentId) {
                if (!isset($groups[$groupName]['subjects'][$subjectId]['assignments'][$assignmentId])) {
                    $groups[$groupName]['subjects'][$subjectId]['assignments'][$assignmentId] = [
                        'assignmentId'   => $assignmentId,
                        'assignmentName'  => $assignmentName,
                        'assignmentDueAt' => $assignmentDueAt,
                        'studentProgress' => []
                    ];
                }
                $groups[$groupName]['subjects'][$subjectId]['assignments'][$assignmentId]['studentProgress'][$userId] = [
                    'userId'   => $userId,
                    'assignmentStatusId' => $assignmentStatusId,
                    'grade'  => $grade
                ];
            }
        }
        return $groups;
    }
}