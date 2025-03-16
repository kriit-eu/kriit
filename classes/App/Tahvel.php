<?php

namespace App;

/**
 * This class handles receiving data from Tahvel and synchronizing it with Kriit:
 *  1) If a teacher/group/subject/assignment/student is missing in Kriit, it is created (and the Tahvel grade is inserted for new students).
 *  2) If a student already exists but has a different grade in Tahvel, that difference is included in the final output.
 *  3) If a subject/assignment is completely new, it will not appear in the final output (since Kriit now matches Tahvel for those).
 *
 * The final output is an array of subjects that exist in both Kriit and Tahvel but have differing data:
 *  - subjectExternalId always included
 *  - only the differing fields for the subject (subjectName, groupName, teacherPersonalCode, teacherName) if they differ
 *  - only the differing assignments, each with assignmentExternalId and the differing fields
 *  - only the differing results (existing students in Kriit whose grade differs)
 *    (newly inserted students will not appear in the output, because Kriit’s data matches Tahvel once inserted).
 */
class Tahvel
{
    /**
     * Inserts missing entities (teacher, group, subject, assignment, student) into Kriit
     * using the data from Tahvel. Then returns an array describing the differences
     * (subject-level, assignment-level, and existing student grades) between Tahvel and Kriit.
     *
     * @param array $tahvelJournals Array of subjects from Tahvel, each with assignments and results
     * @return array
     */
    public static function addMissingSubjectsStudentsAssignmentsAndReturnDiff(array $tahvelJournals): array
    {
        $diffSubjects = [];
        // Load all relevant existing data from Kriit
        $kriitSubjects = self::loadKriitSubjectsData($tahvelJournals);

        foreach ($tahvelJournals as $tahvelSubject) {
            // 1) Ensure teacher & group in Kriit
            self::findOrCreate('users', 'userPersonalCode', $tahvelSubject['teacherPersonalCode'], [
                'userPersonalCode' => $tahvelSubject['teacherPersonalCode'],
                'userName'         => $tahvelSubject['teacherName'],
                'userIsTeacher'    => 1
            ]);
            self::findOrCreate('groups', 'groupName', $tahvelSubject['groupName'], [
                'groupName' => $tahvelSubject['groupName']
            ]);

            // 2) Check if the subject exists in Kriit
            $matchingSubjects = array_filter(
                $kriitSubjects,
                fn($s) => $s['subjectExternalId'] == $tahvelSubject['subjectExternalId']
            );

            // If subject not found, create it, along with assignments and students if needed
            // Then skip from final differences, because newly inserted = no difference
            if (!$matchingSubjects) {
                self::createKriitSubject($tahvelSubject);
                continue;
            }

            // Subject found, let's compare subject-level fields + ensure assignments exist
            $kriitSubject = reset($matchingSubjects); // There's only one subject with that externalId
            $subjectDiffFields = self::diffSubjectFields($kriitSubject, $tahvelSubject);

            // 3) Ensure each assignment in Tahvel is in Kriit; create if missing
            self::ensureAssignmentsExist($tahvelSubject['assignments'], $kriitSubject['subjectId'], $kriitSubject['assignments']);

            // 4) Compare assignment-level fields for those that existed in Kriit
            $assignmentsDifferences = [];
            foreach ($tahvelSubject['assignments'] as $tahvelAssignment) {
                $extId = $tahvelAssignment['assignmentExternalId'];
                // If we just created it (step 3), it's new => no difference
                if (!isset($kriitSubject['assignments'][$extId])) {
                    continue;
                }
                // For existing assignment, compare fields + student results
                $kriitAssignment = $kriitSubject['assignments'][$extId];

                // Compare assignment fields
                $fieldDiff = self::diffAssignmentFields($kriitAssignment, $tahvelAssignment);

                // Compare results for existing students
                // Also note that new students are inserted with the Tahvel grade in step 3 or 5
                $resultsDiff = self::diffAssignmentResults($kriitAssignment['results'], $tahvelAssignment['results']);

                // If either fields differ or existing students differ => record difference
                if ($fieldDiff || $resultsDiff) {
                    $assignDiff = [ 'assignmentExternalId' => $extId ];
                    foreach ($fieldDiff as $field => $diffVal) {
                        // We'll show Kriit's final data in the result
                        $assignDiff[$field] = $diffVal['kriit'];
                    }

                    if ($resultsDiff) {
                        $assignDiff['results'] = [];
                        foreach ($resultsDiff as $studCode => $d) {
                            // The user wants final data, but we show that there's a difference
                            // We could choose Kriit's or Tahvel's grade; the user specifically wants to see
                            // that the grade is different. We'll show Kriit's final data for now.
                            $assignDiff['results'][] = [
                                'studentPersonalCode' => $studCode,
                                'grade'              => $d['kriitGrade'],
                                'studentName'        => $d['studentName']
                            ];
                        }
                    }

                    $assignmentsDifferences[] = $assignDiff;
                }
            }

            // If there's no difference in subject-level fields & no assignment differences, skip
            if (!$subjectDiffFields && !$assignmentsDifferences) {
                continue;
            }

            // Otherwise, build a minimal subject-level difference array
            $subjectDiff = [
                'subjectExternalId' => $kriitSubject['subjectExternalId']
            ];
            // Include only the fields that differ
            foreach ($subjectDiffFields as $f => $info) {
                $subjectDiff[$f] = $info['kriit']; // show Kriit's final data
            }
            if ($assignmentsDifferences) {
                $subjectDiff['assignments'] = $assignmentsDifferences;
            }
            $diffSubjects[] = $subjectDiff;
        }
        return $diffSubjects;
    }

    /**
     * Loads from Kriit the subjects that match the external IDs found in the given Tahvel data.
     */
    private static function loadKriitSubjectsData(array $tahvelSubjects): array
    {
        $extIds = array_column($tahvelSubjects, 'subjectExternalId');
        $extIdsString = implode(',', array_filter($extIds));
        if (!$extIdsString) {
            return [];
        }
        $rows = Db::getAll("
            SELECT s.subjectId, s.subjectName, s.subjectExternalId,
                   g.groupName,
                   t.userPersonalCode AS teacherPersonalCode, t.userName AS teacherName,
                   a.assignmentId, a.assignmentExternalId, a.assignmentName,
                   a.assignmentInstructions, a.assignmentDueAt,
                   ua.userGrade, st.userPersonalCode, st.userName
            FROM subjects s
            JOIN `groups` g ON s.groupId = g.groupId
            JOIN users t    ON s.teacherId = t.userId
            JOIN assignments a ON s.subjectId = a.subjectId
            LEFT JOIN userAssignments ua ON a.assignmentId = ua.assignmentId
            LEFT JOIN users st ON ua.userId = st.userId
            WHERE s.subjectExternalId IN ($extIdsString)
        ");

        $subjects = [];
        foreach ($rows as $r) {
            $sxId = $r['subjectExternalId'];
            if (!isset($subjects[$sxId])) {
                $subjects[$sxId] = [
                    'subjectId'          => $r['subjectId'],
                    'subjectName'        => $r['subjectName'],
                    'subjectExternalId'  => $r['subjectExternalId'],
                    'groupName'          => $r['groupName'],
                    'teacherPersonalCode'=> $r['teacherPersonalCode'],
                    'teacherName'        => $r['teacherName'],
                    'assignments'        => []
                ];
            }
            $axId = $r['assignmentExternalId'];
            if (!isset($subjects[$sxId]['assignments'][$axId])) {
                $subjects[$sxId]['assignments'][$axId] = [
                    'assignmentId'          => $r['assignmentId'],
                    'assignmentExternalId'   => $axId,
                    'assignmentName'         => $r['assignmentName'],
                    'assignmentInstructions' => $r['assignmentInstructions'],
                    'assignmentDueAt'        => $r['assignmentDueAt'],
                    'results'                => []
                ];
            }
            // If there's a student record
            if ($r['userPersonalCode']) {
                $subjects[$sxId]['assignments'][$axId]['results'][$r['userPersonalCode']] = [
                    'grade'       => $r['userGrade'],
                    'studentName' => $r['userName']
                ];
            }
        }
        return $subjects;
    }

    /**
     * Creates a subject in Kriit for $tahvelSubject (including assignments and new students).
     * Because it's newly inserted, it won't show up as a difference.
     */
    private static function createKriitSubject(array $tahvelSubject): void
    {
        $teacher = Db::getFirst("SELECT * FROM users WHERE userPersonalCode='{$tahvelSubject['teacherPersonalCode']}'");
        $group   = Db::getFirst("SELECT * FROM `groups` WHERE groupName='{$tahvelSubject['groupName']}'");

        $subjId = Db::insert('subjects', [
            'subjectName'     => $tahvelSubject['subjectName'],
            'tahvelSubjectId' => $tahvelSubject['subjectExternalId'],
            'groupId'         => $group['groupId'],
            'teacherId'       => $teacher['userId']
        ]);

        // Create all assignments
        foreach ($tahvelSubject['assignments'] as $asm) {
            Db::insert('assignments', [
                'subjectId'             => $subjId,
                'assignmentName'        => $asm['assignmentName'],
                'assignmentExternalId'  => $asm['assignmentExternalId'],
                'assignmentDueAt'       => $asm['assignmentDueAt'],
                'assignmentInstructions'=> $asm['assignmentInstructions']
            ]);
            $newAssignId = Db::getOne("
                SELECT assignmentId FROM assignments
                 WHERE subjectId={$subjId} AND assignmentExternalId={$asm['assignmentExternalId']}
            ");

            // Insert userAssignments for any students (if they exist or are newly inserted)
            foreach ($asm['results'] as $res) {
                if (empty($res['grade'])) {
                    continue;
                }
                // 5A) If the student doesn't exist, create them
                $student = Db::getFirst("SELECT * FROM users WHERE userPersonalCode='{$res['studentPersonalCode']}'");
                if (!$student) {
                    $newUserId = Db::insert('users', [
                        'userPersonalCode' => $res['studentPersonalCode'],
                        'userName'         => $res['studentName'],
                        'userIsTeacher'    => 0
                    ]);
                    $student = Db::getFirst("SELECT * FROM users WHERE userId={$newUserId}");
                }
                // 5B) Insert userAssignment with the Tahvel grade for the new user
                Db::insert('userAssignments', [
                    'assignmentId' => $newAssignId,
                    'userId'       => $student['userId'],
                    'userGrade'    => $res['grade']
                ]);
            }
        }
    }

    /**
     * For each $tahvelAssignment, if it doesn't exist in Kriit, create it (and any new students).
     * No difference will be recorded for brand new assignments/students, because Kriit now has the same data.
     */
    private static function ensureAssignmentsExist(array $tahvelAssignments, int $kriitSubjectId, array $kriitAssignments): void
    {
        foreach ($tahvelAssignments as $asm) {
            $extId = $asm['assignmentExternalId'];
            if (isset($kriitAssignments[$extId])) {
                // Already exists -> let's ensure any missing students are created
                self::ensureStudentsAndGrades($kriitAssignments[$extId]['assignmentId'], $asm['results'] ?? []);
                continue;
            }

            // Not found -> create assignment in Kriit
            Db::insert('assignments', [
                'subjectId'             => $kriitSubjectId,
                'assignmentName'        => $asm['assignmentName'],
                'assignmentExternalId'  => $asm['assignmentExternalId'],
                'assignmentDueAt'       => $asm['assignmentDueAt'],
                'assignmentInstructions'=> $asm['assignmentInstructions']
            ]);
            $newAssignId = Db::getOne("
                SELECT assignmentId FROM assignments
                 WHERE subjectId={$kriitSubjectId} AND assignmentExternalId={$extId}
            ");

            // Insert userAssignments for any students, creating new students if needed
            self::ensureStudentsAndGrades($newAssignId, $asm['results'] ?? []);
        }
    }

    /**
     * For each result (student + grade):
     *   - If the student does not exist, create them.
     *   - If no userAssignment found, create it with the Tahvel grade.
     *   - If there's already a userAssignment, do NOT overwrite if the grade differs
     *     (this difference will appear in the final output).
     */
    private static function ensureStudentsAndGrades(int $assignmentId, array $results): void
    {
        foreach ($results as $r) {
            if (empty($r['grade'])) {
                continue;
            }
            // 1) If student doesn't exist in Kriit, create them
            $student = Db::getFirst("SELECT * FROM users WHERE userPersonalCode='{$r['studentPersonalCode']}'");
            if (!$student) {
                $newUserId = Db::insert('users', [
                    'userPersonalCode' => $r['studentPersonalCode'],
                    'userName'         => $r['studentName'],
                    'userIsTeacher'    => 0
                ]);
                $student = Db::getFirst("SELECT * FROM users WHERE userId={$newUserId}");
            }
            // 2) Check if there's a userAssignment already
            $existingUA = Db::getFirst("
                SELECT * FROM userAssignments
                WHERE assignmentId={$assignmentId} AND userId={$student['userId']}
            ");
            // If no existing userAssignment, create with the Tahvel grade
            if (!$existingUA) {
                Db::insert('userAssignments', [
                    'assignmentId' => $assignmentId,
                    'userId'       => $student['userId'],
                    'userGrade'    => $r['grade']
                ]);
            }
            // If there's already a record but the grade differs, we keep it as-is for now
            // so it will appear in final diff. (No override here, since the user wants to see the difference.)
        }
    }

    /**
     * Compares subjectName, groupName, teacherPersonalCode, teacherName between Kriit & Tahvel,
     * returning an array of only fields that differ. The array is [fieldName => ['kriit' => ..., 'tahvel' => ...]].
     */
    private static function diffSubjectFields(array $kriitSubject, array $tahvelSubject): array
    {
        $check = ['subjectName','groupName','teacherPersonalCode','teacherName'];
        $diffs = [];
        foreach ($check as $fld) {
            if ($kriitSubject[$fld] !== $tahvelSubject[$fld]) {
                $diffs[$fld] = [
                    'kriit'  => $kriitSubject[$fld],
                    'tahvel' => $tahvelSubject[$fld]
                ];
            }
        }
        return $diffs;
    }

    /**
     * Compares assignment-level fields (assignmentName, assignmentInstructions, assignmentDueAt)
     * returning [fieldName => ['kriit'=>..., 'tahvel'=>...]] for only the ones that differ.
     */
    private static function diffAssignmentFields(array $kriitAssignment, array $tahvelAssignment): array
    {
        $check = ['assignmentName','assignmentInstructions','assignmentDueAt'];
        $diffs = [];
        foreach ($check as $fld) {
            if ($kriitAssignment[$fld] !== $tahvelAssignment[$fld]) {
                $diffs[$fld] = [
                    'kriit'  => $kriitAssignment[$fld],
                    'tahvel' => $tahvelAssignment[$fld]
                ];
            }
        }
        return $diffs;
    }

    /**
     * For each Tahvel result, checks if Kriit has a different grade for that student.
     * Only returns differences for students who already exist in Kriit (ensured by the array keys in $kriitResults).
     * Returns: [studentCode => ['kriitGrade'=>..., 'tahvelGrade'=>..., 'studentName'=>...], ...]
     */
    private static function diffAssignmentResults(array $kriitResults, array $tahvelResults): array
    {
        $indexedTahvel = [];
        foreach ($tahvelResults as $res) {
            $indexedTahvel[$res['studentPersonalCode']] = $res;
        }

        $diffs = [];
        foreach ($indexedTahvel as $studCode => $t) {
            $tahvelGrade = $t['grade'] ?? null;
            $kriitGrade  = $kriitResults[$studCode]['grade'] ?? null;

            // If the student doesn't exist in Kriit, they've just been inserted => no difference
            // If the student exists but the grade differs, record difference
            if (array_key_exists($studCode, $kriitResults) && $tahvelGrade !== $kriitGrade) {
                $diffs[$studCode] = [
                    'kriitGrade'  => $kriitGrade,
                    'tahvelGrade' => $tahvelGrade,
                    'studentName' => $t['studentName']
                ];
            }
        }
        return $diffs;
    }

    /**
     * Finds or creates a record in one table by a single unique column.
     * Returns the found (or newly inserted) row from the DB.
     */
    private static function findOrCreate(string $table, string $field, $value, array $insertData): array
    {
        $existing = Db::getFirst("SELECT * FROM `{$table}` WHERE {$field} = '{$value}'");
        if (!$existing) {
            $id = Db::insert($table, $insertData);
            $existing = Db::getFirst("SELECT * FROM `{$table}` WHERE {$field} = '{$value}'");
        }
        return $existing;
    }
}
