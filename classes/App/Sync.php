<?php

namespace App;

/**
 * This class handles receiving data from External system and synchronizing it with Kriit:
 *  1) If a teacher/group/subject/assignment/student is missing in Kriit, it is created (and the External system grade is inserted for new students).
 *  2) If a student already exists but has a different grade in External system, that difference is included in the final output.
 *  3) If a subject/assignment is completely new, it will not appear in the final output (since Kriit now matches External System for those).
 *
 * The final output is an array of subjects that exist in both Kriit and External System but have differing data:
 *  - subjectExternalId always included
 *  - only the differing fields for the subject (subjectName, groupName, teacherPersonalCode, teacherName) if they differ
 *  - only the differing assignments, each with assignmentExternalId and the differing fields
 *  - only the differing results (existing students in Kriit whose grade differs)
 *    (newly inserted students will not appear in the output, because Kriit's data matches External System once inserted).
 */
class Sync
{
    /**
     * Inserts missing entities (teacher, group, subject, assignment, student) into Kriit
     * using the data from External System.
     *
     * @param array $remoteSubjects Array of subjects from External System, each with assignments and results
     * @param int $systemId The ID of the external system
     * @return void
     */
    public static function addMissingEntities(array $remoteSubjects, int $systemId = 1): void
    {
        // Load all relevant existing data from Kriit
        $kriitSubjects = self::loadKriitSubjectsData($remoteSubjects, $systemId);

        foreach ($remoteSubjects as $remoteSubject) {
            // 1) Ensure teacher & group in Kriit
            $teacher = self::findOrCreate('users', 'userPersonalCode', $remoteSubject['teacherPersonalCode'], [
                'userPersonalCode' => $remoteSubject['teacherPersonalCode'],
                'userName'         => $remoteSubject['teacherName'],
                'userIsTeacher'    => 1
            ]);
            
            // Check if teacher name needs updating
            User::updateNameIfNeeded($teacher, $remoteSubject['teacherName'], $systemId);
            self::findOrCreate('groups', 'groupName', $remoteSubject['groupName'], [
                'groupName' => $remoteSubject['groupName']
            ]);

            // 2) Check if the subject exists in Kriit for this system
            $matchingSubjects = array_filter(
                $kriitSubjects,
                fn($s) => $s['subjectExternalId'] == $remoteSubject['subjectExternalId'] &&
                          $s['systemId'] == $systemId
            );

            // If subject not found, create it, along with assignments and students if needed
            if (!$matchingSubjects) {
                self::createKriitSubject($remoteSubject, $systemId);
                continue;
            }

            // Subject found, ensure assignments exist
            $kriitSubject = reset($matchingSubjects); // Subject with matching externalId and systemId

            // 3) Ensure each assignment in Remote is in Kriit; create if missing
            self::ensureAssignmentsExist($remoteSubject['assignments'], $kriitSubject['subjectId'], $kriitSubject['assignments'], $systemId);
        }
    }

    /**
     * Returns an array describing the differences (subject-level, assignment-level, and existing student grades) 
     * between External System and Kriit.
     *
     * @param array $remoteSubjects Array of subjects from External System, each with assignments and results
     * @param int $systemId The ID of the external system
     * @return array
     */
    public static function getSystemDifferences(array $remoteSubjects, int $systemId = 1): array
    {
        $diffSubjects = [];
        // Load all relevant existing data from Kriit
        $kriitSubjects = self::loadKriitSubjectsData($remoteSubjects, $systemId);

        foreach ($remoteSubjects as $remoteSubject) {
            // Check if the subject exists in Kriit for this system
            $matchingSubjects = array_filter(
                $kriitSubjects,
                fn($s) => $s['subjectExternalId'] == $remoteSubject['subjectExternalId'] &&
                          $s['systemId'] == $systemId
            );

            // If subject not found, it should have been created via addMissingEntities
            // Skip from differences, because newly inserted = no difference
            if (!$matchingSubjects) {
                continue;
            }

            // Subject found, let's compare subject-level fields
            $kriitSubject = reset($matchingSubjects); // Subject with matching externalId and systemId
            $subjectDiffFields = self::diffSubjectFields($kriitSubject, $remoteSubject);

            // Compare assignment-level fields for those that existed in Kriit
            $assignmentsDifferences = [];
            foreach ($remoteSubject['assignments'] as $remoteAssignment) {
                $extId = $remoteAssignment['assignmentExternalId'];
                // If it doesn't exist in Kriit (should have been created via addMissingEntities)
                // Skip from differences, because newly inserted = no difference
                if (!isset($kriitSubject['assignments'][$extId])) {
                    continue;
                }
                // For existing assignment, compare fields + student results
                $kriitAssignment = $kriitSubject['assignments'][$extId];

                // Compare assignment fields
                $fieldDiff = self::diffAssignmentFields($kriitAssignment, $remoteAssignment);

                // Compare results for existing students
                $resultsDiff = self::diffAssignmentResults($kriitAssignment['results'], $remoteAssignment['results']);

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
                            // We could choose Kriit's or Remote's grade; the user specifically wants to see
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
     * Loads from Kriit the subjects that match the external IDs found in the given Remote data.
     * 
     * @param array $remoteSubjects Array of subjects from External System, each with assignments and results
     * @param int $systemId The ID of the external system
     * @return array
     */
    private static function loadKriitSubjectsData(array $remoteSubjects, int $systemId = 1): array
    {
        $extIds = array_column($remoteSubjects, 'subjectExternalId');
        $extIdsString = implode(',', array_filter($extIds));
        if (!$extIdsString) {
            return [];
        }
        $rows = Db::getAll("
            SELECT s.subjectId, s.subjectName, s.subjectExternalId, s.systemId,
                   g.groupName,
                   t.userPersonalCode AS teacherPersonalCode, t.userName AS teacherName,
                   a.assignmentId, a.assignmentExternalId, a.systemId as assignmentSystemId, a.assignmentName,
                   a.assignmentInstructions, a.assignmentDueAt,
                   ua.userGrade, st.userPersonalCode, st.userName
            FROM subjects s
            LEFT JOIN `groups` g ON s.groupId = g.groupId
            LEFT JOIN users t    ON s.teacherId = t.userId
            LEFT JOIN assignments a ON s.subjectId = a.subjectId
            LEFT JOIN userAssignments ua ON a.assignmentId = ua.assignmentId
            LEFT JOIN users st ON ua.userId = st.userId
            WHERE s.subjectExternalId IN ($extIdsString) AND s.systemId = ?
        ", [$systemId]);

        $subjects = [];
        foreach ($rows as $r) {
            $sxId = $r['subjectExternalId'];
            if (!isset($subjects[$sxId])) {
                $subjects[$sxId] = [
                    'subjectId'          => $r['subjectId'],
                    'subjectName'        => $r['subjectName'],
                    'subjectExternalId'  => $r['subjectExternalId'],
                    'systemId'           => $r['systemId'],
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
                    'systemId'               => $r['assignmentSystemId'],
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

    // This method has been moved to the Assignment class
    
    /**
     * Creates a subject in Kriit for $remoteSubject (including assignments and new students).
     * Because it's newly inserted, it won't show up as a difference.
     * 
     * @param array $remoteSubject Subject data from External System
     * @param int $systemId The ID of the external system
     */
    private static function createKriitSubject(array $remoteSubject, int $systemId = 1): void
    {
        $teacher = User::findByPersonalCode($remoteSubject['teacherPersonalCode']);
        $group   = Db::getFirst("SELECT * FROM `groups` WHERE groupName='{$remoteSubject['groupName']}'");

        $subjId = Db::insert('subjects', [
            'subjectName'     => $remoteSubject['subjectName'],
            'subjectExternalId' => $remoteSubject['subjectExternalId'],
            'systemId'        => $systemId,
            'groupId'         => $group['groupId'],
            'teacherId'       => $teacher['userId']
        ]);
        
        // Log subject creation
        Activity::create(ACTIVITY_CREATE_SUBJECT_SYNC, $teacher['userId'], $subjId, [
            'systemId' => $systemId,
            'subjectName' => $remoteSubject['subjectName'],
            'subjectExternalId' => $remoteSubject['subjectExternalId'],
            'groupName' => $remoteSubject['groupName']
        ]);

        // Create all assignments
        foreach ($remoteSubject['assignments'] as $asm) {
            // Create the assignment and get its ID using Assignment class
            $newAssignId = Assignment::createFromExternalData(
                $asm, 
                $subjId, 
                $systemId, 
                $teacher['userId'], 
                $remoteSubject['subjectName']
            );

            // Insert userAssignments for any students (if they exist or are newly inserted)
            foreach ($asm['results'] as $res) {
                if (empty($res['grade'])) {
                    continue;
                }
                // 5A) If the student doesn't exist, create them
                $student = User::findByPersonalCode($res['studentPersonalCode']);
                if (!$student) {
                    $newUserId = User::createStudent(
                        $res['studentPersonalCode'],
                        $res['studentName'],
                        $systemId,
                        $group['groupId'],
                        $teacher['userId'],
                        $remoteSubject['subjectName']
                    );
                    $student = User::findById($newUserId);
                }
                
                // Set grade using Assignment class
                Assignment::setGrade(
                    $newAssignId,
                    $student['userId'],
                    $res['grade'],
                    $teacher['userId'],
                    $systemId,
                    $remoteSubject['subjectName'],
                    !empty($asm['assignmentName']) ? $asm['assignmentName'] : 'Unnamed assignment',
                    $student['userName']
                );
            }
        }

    }

    /**
     * For each $remoteAssignment, if it doesn't exist in Kriit, create it (and any new students).
     * No difference will be recorded for brand-new assignments/students, because Kriit now has the same data.
     * 
     * @param array $remoteAssignments Array of assignments from External System
     * @param int $kriitSubjectId The subject ID in Kriit
     * @param array $kriitAssignments Existing assignments in Kriit
     * @param int $systemId The ID of the external system
     */
    private static function ensureAssignmentsExist(array $remoteAssignments, int $kriitSubjectId, array $kriitAssignments, int $systemId = 1): void
    {
        // Get subject and teacher info for logging once (optimization)
        $subject = Db::getFirst("SELECT s.subjectName, s.teacherId, u.userName FROM subjects s JOIN users u ON s.teacherId = u.userId WHERE s.subjectId = ?", [$kriitSubjectId]);
        $teacherId = $subject ? $subject['teacherId'] : null;
        $subjectName = $subject ? $subject['subjectName'] : null;
        
        foreach ($remoteAssignments as $ra) {
            $extId = $ra['assignmentExternalId'];
            
            // Check if assignment exists in memory first (faster)
            $existing = false;
            foreach ($kriitAssignments as $ka) {
                if ($ka['assignmentExternalId'] == $extId && $ka['systemId'] == $systemId) {
                    $existing = true;
                    // Already exists -> let's ensure any missing students are created
                    self::ensureStudentsAndGrades($ka['assignmentId'], $ra['results'] ?? [], $systemId);
                    break;
                }
            }
            
            if ($existing) {
                continue;
            }
            
            // Not found in memory, double-check directly in the database
            $existingAssignment = Assignment::getByExternalId($extId, $systemId);
            if ($existingAssignment) {
                // Assignment exists in database but wasn't in our memory cache
                self::ensureStudentsAndGrades($existingAssignment['assignmentId'], $ra['results'] ?? [], $systemId);
                continue;
            }

            // Not found in database -> create assignment in Kriit using Assignment class
            $newAssignId = Assignment::createFromExternalData($ra, $kriitSubjectId, $systemId, $teacherId, $subjectName);

            // Insert userAssignments for any students, creating new students if needed
            self::ensureStudentsAndGrades($newAssignId, $ra['results'] ?? [], $systemId);
        }
    }

    /**
     * For each result (student + grade):
     *   - If the student does not exist, create them.
     *   - If no userAssignment found, create it with the Remote grade.
     *   - If there's already a userAssignment, do NOT overwrite if the grade differs
     *     (this difference will appear in the final output).
     * 
     * @param int $assignmentId The assignment ID in Kriit
     * @param array $results Array of results from External System
     * @param int $systemId The ID of the external system
     */
    private static function ensureStudentsAndGrades(int $assignmentId, array $results, int $systemId = 1): void
    {
        foreach ($results as $r) {
            if (empty($r['grade'])) {
                continue;
            }
            // 1) If student doesn't exist in Kriit, create them
            $student = User::findByPersonalCode($r['studentPersonalCode']);
            if (!$student) {
                // Get assignment and subject info for logging and to get the group
                $assignmentInfo = Db::getFirst("
                    SELECT a.assignmentName, s.subjectId, s.subjectName, s.teacherId, s.groupId
                    FROM assignments a
                    JOIN subjects s ON a.subjectId = s.subjectId
                    WHERE a.assignmentId = ?
                ", [$assignmentId]);
                
                $groupId = $assignmentInfo ? $assignmentInfo['groupId'] : null;
                $teacherId = $assignmentInfo ? $assignmentInfo['teacherId'] : null;
                $subjectName = $assignmentInfo ? $assignmentInfo['subjectName'] : null;
                
                $newUserId = User::createStudent(
                    $r['studentPersonalCode'],
                    $r['studentName'],
                    $systemId,
                    $groupId,
                    $teacherId,
                    $subjectName
                );
                
                $student = User::findById($newUserId);
            } else {
                // Check if name needs to be updated (e.g., the student got married, and their name changed)
                User::updateNameIfNeeded($student, $r['studentName'], $systemId);
            }

            // 2) Check if there's a userAssignment already
            $existingUA = Db::getFirst("
                SELECT * FROM userAssignments
                WHERE assignmentId={$assignmentId} AND userId={$student['userId']}
            ");

            // If no existing userAssignment, create with the Remote grade using Assignment class
            if (!$existingUA) {
                // Get assignment info for passing to Assignment::setGrade
                $assignmentInfo = Db::getFirst("
                    SELECT a.assignmentName, s.subjectId, s.subjectName, s.teacherId
                    FROM assignments a
                    JOIN subjects s ON a.subjectId = s.subjectId
                    WHERE a.assignmentId = ?
                ", [$assignmentId]);
                
                if ($assignmentInfo) {
                    Assignment::setGrade(
                        $assignmentId,
                        $student['userId'],
                        $r['grade'],
                        $assignmentInfo['teacherId'],
                        $systemId,
                        $assignmentInfo['subjectName'],
                        $assignmentInfo['assignmentName'],
                        $student['userName']
                    );
                } else {
                    // Fallback if no assignment info available
                    Assignment::setGrade(
                        $assignmentId,
                        $student['userId'],
                        $r['grade'],
                        null,
                        $systemId
                    );
                }
            }
            // If there's already a record but the grade differs, we keep it as-is for now
            // so it will appear in final diff. (No override here, since the user wants to see the difference.)
        }
    }

    /**
     * Compares subjectName, groupName, teacherPersonalCode, teacherName between Kriit & Remote,
     * returning an array of only fields that differ. The array is [fieldName => ['kriit' => ..., 'remote' => ...]].
     */
    private static function diffSubjectFields(array $kriitSubject, array $remoteSubject): array
    {
        $check = ['subjectName','groupName','teacherPersonalCode','teacherName'];
        $diffs = [];
        foreach ($check as $fld) {
            if ($kriitSubject[$fld] !== $remoteSubject[$fld]) {
                $diffs[$fld] = [
                    'kriit'  => $kriitSubject[$fld],
                    'remote' => $remoteSubject[$fld]
                ];
            }
        }
        return $diffs;
    }

    /**
     * Compares assignment-level fields (assignmentName, assignmentInstructions, assignmentDueAt)
     * returning [fieldName => ['kriit'=>..., 'remote'=>...]] for only the ones that differ.
     */
    private static function diffAssignmentFields(array $kriitAssignment, array $remoteAssignment): array
    {
        $check = ['assignmentDueAt'];
        $diffs = [];
        foreach ($check as $fld) {
            if ($kriitAssignment[$fld] !== $remoteAssignment[$fld]) {
                $diffs[$fld] = [
                    'kriit'  => $kriitAssignment[$fld],
                    'remote' => $remoteAssignment[$fld]
                ];
            }
        }
        return $diffs;
    }

    /**
     * For each Remote result, checks if Kriit has a different grade for that student.
     * Only returns differences for students who already exist in Kriit (ensured by the array keys in $kriitResults).
     * Returns: [studentCode => ['kriitGrade'=>..., 'remoteGrade'=>..., 'studentName'=>...], ...]
     */
    private static function diffAssignmentResults(array $kriitResults, array $remoteResults): array
    {
        $indexedRemote = [];
        foreach ($remoteResults as $res) {
            $indexedRemote[$res['studentPersonalCode']] = $res;
        }

        $diffs = [];
        foreach ($indexedRemote as $studCode => $t) {
            $remoteGrade = $t['grade'] ?? null;
            $kriitGrade  = $kriitResults[$studCode]['grade'] ?? null;

            // If the student doesn't exist in Kriit, they've just been inserted => no difference
            // If the student exists but the grade differs, record difference
            if (array_key_exists($studCode, $kriitResults) && $remoteGrade !== $kriitGrade) {
                $diffs[$studCode] = [
                    'kriitGrade'  => $kriitGrade,
                    'remoteGrade' => $remoteGrade,
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
        // Special case for users table
        if ($table === 'users') {
            $existing = User::findByPersonalCode($value);
            if (!$existing) {
                // For teacher users
                if ($insertData['userIsTeacher'] == 1) {
                    $id = User::createTeacher(
                        $insertData['userPersonalCode'],
                        $insertData['userName'],
                        $insertData['systemId'] ?? 1
                    );
                    $existing = User::findByPersonalCode($value);
                } else {
                    // For students, we should use User::createStudent, but this method is typically
                    // only used for teachers in this class, so this branch should not execute
                    $groupId = $insertData['groupId'] ?? null;
                    $id = User::createStudent(
                        $insertData['userPersonalCode'],
                        $insertData['userName'],
                        $insertData['systemId'] ?? 1,
                        $groupId
                    );
                    $existing = User::findByPersonalCode($value);
                }
            }
            return $existing;
        }
        
        // Regular case for other tables
        $existing = Db::getFirst("SELECT * FROM `{$table}` WHERE {$field} = '{$value}'");
        if (!$existing) {
            $id = Db::insert($table, $insertData);

            // Determine activity id based on table name
            $activityId = match ($table) {
                'subjects' => ACTIVITY_CREATE_SUBJECT_SYNC,
                'assignments' => ACTIVITY_CREATE_ASSIGNMENT_SYNC,
                'groups' => ACTIVITY_CREATE_GROUP,
                default => 9999
            };

            Activity::create($activityId, null, $id, $insertData);

            $existing = Db::getFirst("SELECT * FROM `{$table}` WHERE {$field} = '{$value}'");
        }
        return $existing;
    }
    
}