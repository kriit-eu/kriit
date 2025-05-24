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
        if (empty($remoteSubjects)) {
            return;
        }

        // Reset our data caches
        self::resetCaches();

        // Preload all relevant data at once
        self::preloadAllData($remoteSubjects, $systemId);

        // Load all relevant existing data from Kriit
        $kriitSubjects = self::loadKriitSubjectsData($remoteSubjects, $systemId);

        // Preload all group names and teacher personal codes in one batch
        $allGroupNames = array_unique(array_column($remoteSubjects, 'groupName'));
        $allTeacherCodes = array_unique(array_column($remoteSubjects, 'teacherPersonalCode'));

        // Fetch all existing groups in one query
        $existingGroups = [];
        if (!empty($allGroupNames)) {
            $placeholders = implode(',', array_fill(0, count($allGroupNames), '?'));
            $groupsResult = Db::getAll("SELECT * FROM `groups` WHERE groupName IN ({$placeholders})", $allGroupNames);
            foreach ($groupsResult as $group) {
                $existingGroups[$group['groupName']] = $group;
            }
        }

        // Fetch all existing teachers in one query
        $existingTeachers = [];
        if (!empty($allTeacherCodes)) {
            $placeholders = implode(',', array_fill(0, count($allTeacherCodes), '?'));
            $teachersResult = Db::getAll("SELECT * FROM users WHERE userPersonalCode IN ({$placeholders})", $allTeacherCodes);
            foreach ($teachersResult as $teacher) {
                $existingTeachers[$teacher['userPersonalCode']] = $teacher;
            }
        }

        foreach ($remoteSubjects as $remoteSubject) {
            // 1) Ensure teacher exists
            $teacherCode = $remoteSubject['teacherPersonalCode'];
            if (isset($existingTeachers[$teacherCode])) {
                $teacher = $existingTeachers[$teacherCode];

                // Check if teacher name needs updating
                User::updateNameIfNeeded($teacher, $remoteSubject['teacherName'], $systemId);
            } else {
                // Create teacher
                $teacher = self::findOrCreate('users', 'userPersonalCode', $teacherCode, [
                    'userPersonalCode' => $teacherCode,
                    'userName'         => $remoteSubject['teacherName'],
                    'userIsTeacher'    => 1
                ]);

                // Add to our local cache
                $existingTeachers[$teacherCode] = $teacher;
            }

            // 2) Ensure group exists
            $groupName = $remoteSubject['groupName'];
            if (!isset($existingGroups[$groupName])) {
                $group = self::findOrCreate('groups', 'groupName', $groupName, [
                    'groupName' => $groupName
                ]);
                // Add to our local cache
                $existingGroups[$groupName] = $group;
            }

            // 3) Check if the subject exists in Kriit for this system
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

            // 4) Ensure each assignment in Remote is in Kriit; create if missing
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

        // Reset our data caches
        self::resetCaches();

        // Preload all relevant data at once
        self::preloadAllData($remoteSubjects, $systemId);

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

                // If there are field differences, update the assignment in Kriit
                // BUT ONLY for fields that are NULL in Kriit
                if ($fieldDiff) {
                    $updateData = [];
                    foreach ($fieldDiff as $field => $diffVal) {
                        // Only update fields that are NULL in Kriit
                        if ($diffVal['kriit'] === null && $diffVal['remote'] !== null) {
                            // Use the remote value for the update
                            $updateData[$field] = $diffVal['remote'];
                        }
                    }

                    // Only update if we have data to update
                    if (!empty($updateData)) {
                        Assignment::update($kriitAssignment['assignmentId'], $updateData);

                        // Log the update
                        $teacherId = null;
                        $subjectInfo = self::getSubjectsInfo([$kriitSubject['subjectId']]);
                        if (!empty($subjectInfo) && isset($subjectInfo[$kriitSubject['subjectId']]['teacherId'])) {
                            $teacherId = $subjectInfo[$kriitSubject['subjectId']]['teacherId'];
                        }

                        if ($teacherId) {
                            Activity::create(ACTIVITY_UPDATE_ASSIGNMENT_SYNC, $teacherId, $kriitAssignment['assignmentId'], [
                                'systemId' => $systemId,
                                'assignmentName' => $kriitAssignment['assignmentName'],
                                'assignmentExternalId' => $extId,
                                'updatedFields' => array_keys($updateData)
                            ]);
                        }
                    }
                }

                // Compare results for existing students
                $resultsDiff = self::diffAssignmentResults($kriitAssignment['results'], $remoteAssignment['results']);

                // If either fields differ or existing students differ => record difference
                if ($fieldDiff || $resultsDiff) {
                    $assignDiff = [ 'assignmentExternalId' => $extId ];
                    foreach ($fieldDiff as $field => $diffVal) {
                        // We'll show Kriit's final data in the result
                        // If the field was NULL in Kriit and has been updated, show the remote value
                        // Otherwise show the original Kriit value
                        if ($diffVal['kriit'] === null && $diffVal['remote'] !== null) {
                            $assignDiff[$field] = $diffVal['remote'];
                        } else {
                            $assignDiff[$field] = $diffVal['kriit'];
                        }
                    }

                    if ($resultsDiff) {
                        $assignDiff['results'] = [];
                        foreach ($resultsDiff as $studCode => $d) {

                            // The user wants final data, but we show that there's a difference
                            // We could choose Kriit's or Remote's grade; the user specifically wants to see
                            // that the grade is different. We'll show Kriit's final data for now.
                            $resultEntry = [
                                'studentPersonalCode' => $studCode,
                                'studentName'        => $d['studentName']
                            ];

                            // Check if kriitGrade exists (it might not if only studentIsActive differs)
                            if (isset($d['kriitGrade'])) {
                                $resultEntry['grade'] = $d['kriitGrade'];
                            } else {
                                // If no grade difference, use the grade from kriitResults if available
                                $resultEntry['grade'] = $kriitResults[$studCode]['grade'] ?? null;
                            }

                            $assignDiff['results'][] = $resultEntry;
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
                   a.assignmentInstructions, a.assignmentDueAt, a.assignmentEntryDate,
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
                    'assignmentEntryDate'    => $r['assignmentEntryDate'],
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
        if (empty($remoteAssignments)) {
            return;
        }

        // Get subject and teacher info from cache if possible
        if (isset(self::$subjectInfoCache[$kriitSubjectId])) {
            $subject = self::$subjectInfoCache[$kriitSubjectId];
        } else {
            // Not in cache, fetch it
            $subject = Db::getFirst("SELECT s.subjectId, s.subjectName, s.teacherId, u.userName FROM subjects s JOIN users u ON s.teacherId = u.userId WHERE s.subjectId = ?", [$kriitSubjectId]);

            if ($subject) {
                // Store in cache for future use
                self::$subjectInfoCache[$kriitSubjectId] = $subject;
            }
        }

        $teacherId = $subject ? $subject['teacherId'] : null;
        $subjectName = $subject ? $subject['subjectName'] : null;

        // Collect all assignment external IDs that we need to verify
        $assignmentsToCheck = [];
        $assignmentResults = [];
        $assignmentMemoryStatus = [];

        foreach ($remoteAssignments as $ra) {
            $extId = $ra['assignmentExternalId'];
            $assignmentResults[$extId] = $ra['results'] ?? [];

            // Check if assignment exists in memory first (faster)
            $existsInMemory = false;
            foreach ($kriitAssignments as $ka) {
                if ($ka['assignmentExternalId'] == $extId && $ka['systemId'] == $systemId) {
                    $existsInMemory = true;
                    $assignmentMemoryStatus[$extId] = [
                        'exists' => true,
                        'assignmentId' => $ka['assignmentId']
                    ];
                    break;
                }
            }

            // If not found in memory, we need to check the database
            if (!$existsInMemory) {
                $assignmentsToCheck[] = $extId;
                $assignmentMemoryStatus[$extId] = [
                    'exists' => false,
                    'assignmentId' => null
                ];
            }
        }

        // Check all missing assignments in a single query if needed
        $existingDbAssignments = [];
        if (!empty($assignmentsToCheck)) {
            $placeholders = implode(',', array_fill(0, count($assignmentsToCheck), '?'));
            $params = array_merge($assignmentsToCheck, [$systemId]);

            $dbAssignments = Db::getAll("
                SELECT * FROM assignments
                WHERE assignmentExternalId IN ({$placeholders}) AND systemId = ?
            ", $params);

            foreach ($dbAssignments as $a) {
                $existingDbAssignments[$a['assignmentExternalId']] = $a;
                // Update our status record
                $assignmentMemoryStatus[$a['assignmentExternalId']] = [
                    'exists' => true,
                    'assignmentId' => $a['assignmentId']
                ];
            }
        }

        // Now process each assignment with our collected information
        foreach ($remoteAssignments as $ra) {
            $extId = $ra['assignmentExternalId'];
            $status = $assignmentMemoryStatus[$extId];

            if ($status['exists']) {
                // Assignment exists - ensure students and grades
                self::ensureStudentsAndGrades($status['assignmentId'], $assignmentResults[$extId], $systemId);
            } else {
                // Assignment doesn't exist - create it
                $newAssignId = Assignment::createFromExternalData($ra, $kriitSubjectId, $systemId, $teacherId, $subjectName);

                // Process student results for the new assignment
                self::ensureStudentsAndGrades($newAssignId, $assignmentResults[$extId], $systemId);
            }
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
    // Caches for various data to avoid repeated database lookups
    private static $studentCache = [];
    private static $assignmentInfoCache = [];
    private static $subjectInfoCache = [];
    private static $userAssignmentsCache = []; // [assignmentId][userId] = assignment data

    /**
     * Helper method to get assignment info for multiple assignments at once
     */
    private static function getAssignmentsInfo(array $assignmentIds): array
    {
        if (empty($assignmentIds)) {
            return [];
        }

        // Filter out assignment IDs we already have in cache
        $idsToFetch = array_filter($assignmentIds, fn($id) => !isset(self::$assignmentInfoCache[$id]));

        if (!empty($idsToFetch)) {
            $placeholders = implode(',', array_fill(0, count($idsToFetch), '?'));
            $assignmentsInfo = Db::getAll("
                SELECT a.assignmentId, a.assignmentName, s.subjectId, s.subjectName, s.teacherId, s.groupId
                FROM assignments a
                JOIN subjects s ON a.subjectId = s.subjectId
                WHERE a.assignmentId IN ({$placeholders})
            ", $idsToFetch);

            foreach ($assignmentsInfo as $info) {
                self::$assignmentInfoCache[$info['assignmentId']] = $info;
            }
        }

        // Return all requested assignment info from cache
        $result = [];
        foreach ($assignmentIds as $id) {
            if (isset(self::$assignmentInfoCache[$id])) {
                $result[$id] = self::$assignmentInfoCache[$id];
            }
        }

        return $result;
    }

    /**
     * Helper method to preload students by personal codes
     */
    private static function preloadStudents(array $personalCodes, int $systemId = 1): void
    {
        if (empty($personalCodes)) {
            return;
        }

        // Filter out codes we already have in cache
        $codesToLoad = array_filter($personalCodes, fn($code) => !isset(self::$studentCache[$code]));
        if (empty($codesToLoad)) {
            return;
        }

        $placeholders = implode(',', array_fill(0, count($codesToLoad), '?'));
        $studentsResult = Db::getAll("
            SELECT * FROM users
            WHERE userPersonalCode IN ({$placeholders})
        ", $codesToLoad);

        foreach ($studentsResult as $student) {
            self::$studentCache[$student['userPersonalCode']] = $student;
        }
    }

    /**
     * Helper method to get subject info for multiple subjects at once
     */
    private static function getSubjectsInfo(array $subjectIds): array
    {
        if (empty($subjectIds)) {
            return [];
        }

        // Filter out subject IDs we already have in cache
        $idsToFetch = array_filter($subjectIds, fn($id) => !isset(self::$subjectInfoCache[$id]));

        if (!empty($idsToFetch)) {
            $placeholders = implode(',', array_fill(0, count($idsToFetch), '?'));
            $subjectsInfo = Db::getAll("
                SELECT s.subjectId, s.subjectName, s.teacherId, u.userName
                FROM subjects s
                JOIN users u ON s.teacherId = u.userId
                WHERE s.subjectId IN ({$placeholders})
            ", $idsToFetch);

            foreach ($subjectsInfo as $info) {
                self::$subjectInfoCache[$info['subjectId']] = $info;
            }
        }

        // Return all requested subject info from cache
        $result = [];
        foreach ($subjectIds as $id) {
            if (isset(self::$subjectInfoCache[$id])) {
                $result[$id] = self::$subjectInfoCache[$id];
            }
        }

        return $result;
    }

    /**
     * Reset all data caches to prepare for a new sync operation
     */
    private static function resetCaches(): void
    {
        self::$studentCache = [];
        self::$assignmentInfoCache = [];
        self::$subjectInfoCache = [];
        self::$userAssignmentsCache = [];
    }

    /**
     * Preload all data needed for the sync process to minimize database queries
     */
    private static function preloadAllData(array $remoteSubjects, int $systemId): void
    {
        // 1. Collect all personal codes from all assignments across all subjects
        $allStudentCodes = [];
        $allAssignmentIds = [];
        $allSubjectIds = [];

        // First, find all existing subjects and their IDs
        $extIds = array_column($remoteSubjects, 'subjectExternalId');
        if (!empty($extIds)) {
            $extIdsString = implode(',', array_filter($extIds));
            $subjectRows = Db::getAll("
                SELECT subjectId, subjectExternalId
                FROM subjects
                WHERE subjectExternalId IN ({$extIdsString}) AND systemId = ?
            ", [$systemId]);

            foreach ($subjectRows as $row) {
                $allSubjectIds[] = $row['subjectId'];
            }
        }

        // Get all assignments for these subjects
        if (!empty($allSubjectIds)) {
            $subjectIdsString = implode(',', $allSubjectIds);
            $assignmentRows = Db::getAll("
                SELECT assignmentId, subjectId
                FROM assignments
                WHERE subjectId IN ({$subjectIdsString})
            ");

            foreach ($assignmentRows as $row) {
                $allAssignmentIds[] = $row['assignmentId'];
            }
        }

        // Collect all student codes from remote data
        foreach ($remoteSubjects as $subject) {
            if (!empty($subject['assignments'])) {
                foreach ($subject['assignments'] as $assignment) {
                    if (!empty($assignment['results'])) {
                        foreach ($assignment['results'] as $result) {
                            if (!empty($result['studentPersonalCode'])) {
                                $allStudentCodes[] = $result['studentPersonalCode'];
                            }
                        }
                    }
                }
            }
        }

        // 2. Preload students
        $allStudentCodes = array_unique($allStudentCodes);
        if (!empty($allStudentCodes)) {
            self::preloadStudents($allStudentCodes, $systemId);
        }

        // 3. Preload subject info
        $allSubjectIds = array_unique($allSubjectIds);
        if (!empty($allSubjectIds)) {
            $placeholders = implode(',', array_fill(0, count($allSubjectIds), '?'));
            $subjectsInfo = Db::getAll("
                SELECT s.subjectId, s.subjectName, s.teacherId, u.userName
                FROM subjects s
                JOIN users u ON s.teacherId = u.userId
                WHERE s.subjectId IN ({$placeholders})
            ", $allSubjectIds);

            foreach ($subjectsInfo as $info) {
                self::$subjectInfoCache[$info['subjectId']] = $info;
            }
        }

        // 4. Preload assignment info
        $allAssignmentIds = array_unique($allAssignmentIds);
        if (!empty($allAssignmentIds)) {
            $placeholders = implode(',', array_fill(0, count($allAssignmentIds), '?'));
            $assignmentsInfo = Db::getAll("
                SELECT a.assignmentId, a.assignmentName, s.subjectId, s.subjectName, s.teacherId, s.groupId
                FROM assignments a
                JOIN subjects s ON a.subjectId = s.subjectId
                WHERE a.assignmentId IN ({$placeholders})
            ", $allAssignmentIds);

            foreach ($assignmentsInfo as $info) {
                self::$assignmentInfoCache[$info['assignmentId']] = $info;
            }
        }

        // 5. Preload userAssignments
        if (!empty($allAssignmentIds) && !empty($allStudentCodes)) {
            // First get all user IDs for the personal codes
            $userIds = [];
            foreach ($allStudentCodes as $code) {
                if (isset(self::$studentCache[$code])) {
                    $userIds[] = self::$studentCache[$code]['userId'];
                }
            }

            if (!empty($userIds)) {
                $assignmentPlaceholders = implode(',', array_fill(0, count($allAssignmentIds), '?'));
                $userPlaceholders = implode(',', array_fill(0, count($userIds), '?'));

                $params = array_merge($allAssignmentIds, $userIds);

                $userAssignments = Db::getAll("
                    SELECT * FROM userAssignments
                    WHERE assignmentId IN ({$assignmentPlaceholders})
                    AND userId IN ({$userPlaceholders})
                ", $params);

                foreach ($userAssignments as $ua) {
                    if (!isset(self::$userAssignmentsCache[$ua['assignmentId']])) {
                        self::$userAssignmentsCache[$ua['assignmentId']] = [];
                    }
                    self::$userAssignmentsCache[$ua['assignmentId']][$ua['userId']] = $ua;
                }
            }
        }
    }

    private static function ensureStudentsAndGrades(int $assignmentId, array $results, int $systemId = 1): void
    {
        if (empty($results)) {
            return;
        }

        // Extract valid results (with grades)
        $validResults = array_filter($results, fn($r) => !empty($r['grade']));
        if (empty($validResults)) {
            return;
        }
        
        // Collect all student personal codes from remote results
        $remoteStudentCodes = array_column($results, 'studentPersonalCode');

        // Get assignment info from cache if possible
        if (isset(self::$assignmentInfoCache[$assignmentId])) {
            $assignmentInfo = self::$assignmentInfoCache[$assignmentId];
        } else {
            // Not in cache, fetch it and store in cache
            $assignmentInfo = Db::getFirst("
                SELECT a.assignmentId, a.assignmentName, s.subjectId, s.subjectName, s.teacherId, s.groupId
                FROM assignments a
                JOIN subjects s ON a.subjectId = s.subjectId
                WHERE a.assignmentId = ?
            ", [$assignmentId]);

            if (!$assignmentInfo) {
                // Something is wrong with this assignment
                return;
            }

            // Store in cache for future use
            self::$assignmentInfoCache[$assignmentId] = $assignmentInfo;
        }

        $groupId = $assignmentInfo['groupId'];
        $teacherId = $assignmentInfo['teacherId'];
        $subjectName = $assignmentInfo['subjectName'];

        // Extract all student personal codes
        $personalCodes = array_column($validResults, 'studentPersonalCode');

        // Preload students into cache
        self::preloadStudents($personalCodes, $systemId);

        // Process students first - create any missing students
        $allStudentIds = [];
        $newStudentNames = []; // Track which students were created/updated for later use

        foreach ($validResults as $r) {
            $personalCode = $r['studentPersonalCode'];

            if (isset(self::$studentCache[$personalCode])) {
                // Student exists - check if name needs updating
                $student = self::$studentCache[$personalCode];
                User::updateNameIfNeeded($student, $r['studentName'], $systemId);

                // Update student active status if provided
                if (isset($r['studentIsActive'])) {
                    $isActive = (bool)$r['studentIsActive'];
                    if ($student['userIsActive'] != $isActive) {
                        User::edit($student['userId'], ['userIsActive' => $isActive ? 1 : 0]);
                        // Update our cache
                        self::$studentCache[$personalCode]['userIsActive'] = $isActive ? 1 : 0;
                    }
                }

                $allStudentIds[$personalCode] = $student['userId'];
                $newStudentNames[$personalCode] = $r['studentName'];
            } else {
                // Student doesn't exist - create them
                $isActive = isset($r['studentIsActive']) ? (bool)$r['studentIsActive'] : true;
                $newUserId = User::createStudent(
                    $personalCode,
                    $r['studentName'],
                    $systemId,
                    $groupId,
                    $teacherId,
                    $subjectName,
                    $isActive
                );
                $student = User::findById($newUserId);
                $allStudentIds[$personalCode] = $student['userId'];
                $newStudentNames[$personalCode] = $r['studentName'];
                self::$studentCache[$personalCode] = $student; // Add to our cache
            }
        }

        // Get existing userAssignments from cache if possible
        $existingUserAssignments = [];

        if (!empty($allStudentIds)) {
            // Check if we have cache data for this assignment
            if (isset(self::$userAssignmentsCache[$assignmentId])) {
                // Use the cache
                foreach ($allStudentIds as $personalCode => $userId) {
                    if (isset(self::$userAssignmentsCache[$assignmentId][$userId])) {
                        $existingUserAssignments[$userId] = self::$userAssignmentsCache[$assignmentId][$userId];
                    }
                }
            } else {
                // Cache miss - fetch from database and update cache
                $userIds = array_values($allStudentIds);
                $placeholders = implode(',', array_fill(0, count($userIds), '?'));
                $params = array_merge([$assignmentId], $userIds);

                $userAssignments = Db::getAll("
                    SELECT * FROM userAssignments
                    WHERE assignmentId = ? AND userId IN ({$placeholders})
                ", $params);

                // Initialize cache entry if needed
                if (!isset(self::$userAssignmentsCache[$assignmentId])) {
                    self::$userAssignmentsCache[$assignmentId] = [];
                }

                foreach ($userAssignments as $ua) {
                    // Update both local working copy and cache
                    $existingUserAssignments[$ua['userId']] = $ua;
                    self::$userAssignmentsCache[$assignmentId][$ua['userId']] = $ua;
                }
            }
        }

        // Finally, create missing userAssignments
        // Get existing student assignments for this assignment
        $existingStudentAssignments = Db::getAll("SELECT ua.userId, u.userPersonalCode 
                                           FROM userAssignments ua 
                                           JOIN users u ON ua.userId = u.userId 
                                           WHERE ua.assignmentId = ?", [$assignmentId]);
        
        // Extract all students that have this assignment but weren't included in remote results
        $missingStudents = [];
        foreach ($existingStudentAssignments as $existingAssignment) {
            if (!in_array($existingAssignment['userPersonalCode'], $remoteStudentCodes)) {
                $missingStudents[] = $existingAssignment['userId'];
            }
        }
        
        // Mark missing students as deleted
        if (!empty($missingStudents)) {
            foreach ($missingStudents as $missingUserId) {
                $student = User::findById($missingUserId);
                if ($student && !$student['userDeleted']) {
                    // Mark student as deleted (logical delete)
                    Db::update('users', ['userDeleted' => 1], "userId = {$missingUserId}");
                    
                    // Log the deletion
                    Activity::create(ACTIVITY_UPDATE_USER_SYNC, $teacherId, $missingUserId, [
                        'systemId' => $systemId,
                        'action' => 'marked_deleted',
                        'reason' => 'Missing from external system data',
                        'subjectName' => $subjectName,
                        'assignmentName' => $assignmentInfo['assignmentName']
                    ]);
                }
            }
        }

        foreach ($validResults as $r) {
            $personalCode = $r['studentPersonalCode'];
            $userId = $allStudentIds[$personalCode];
            
            // If student was previously marked as deleted but now appears again, undelete them
            $student = User::findById($userId);
            if ($student && $student['userDeleted']) {
                Db::update('users', ['userDeleted' => 0], "userId = {$userId}");
                
                // Log the undeletion
                Activity::create(ACTIVITY_UPDATE_USER_SYNC, $teacherId, $userId, [
                    'systemId' => $systemId,
                    'action' => 'unmarked_deleted',
                    'reason' => 'Reappeared in external system data',
                    'subjectName' => $subjectName,
                    'assignmentName' => $assignmentInfo['assignmentName']
                ]);
            }

            // If no userAssignment exists for this student and assignment
            if (!isset($existingUserAssignments[$userId])) {
                Assignment::setGrade(
                    $assignmentId,
                    $userId,
                    $r['grade'],
                    $teacherId,
                    $systemId,
                    $subjectName,
                    $assignmentInfo['assignmentName'],
                    $newStudentNames[$personalCode]
                );
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
     * Compares assignment-level fields (assignmentName, assignmentInstructions, assignmentDueAt, assignmentEntryDate)
     * returning [fieldName => ['kriit'=>..., 'remote'=>...]] for only the ones that differ.
     * Special handling for NULL values in Kriit - if remote has a value and Kriit has NULL, it's considered a difference.
     */
    private static function diffAssignmentFields(array $kriitAssignment, array $remoteAssignment): array
    {
        $check = ['assignmentDueAt', 'assignmentEntryDate'];
        $diffs = [];
        foreach ($check as $fld) {
            // Consider it a difference if:
            // 1. Remote has the field AND
            // 2. Either Kriit's value is NULL while remote has a value, OR the values are different
            if (isset($remoteAssignment[$fld]) &&
                (($kriitAssignment[$fld] === null && $remoteAssignment[$fld] !== null) ||
                 $kriitAssignment[$fld] !== $remoteAssignment[$fld])) {
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
            $studentIsActive = $t['studentIsActive'] ?? true;

            // If the student doesn't exist in Kriit, they've just been inserted => no difference
            // If the student exists but the grade differs, record difference
            if (array_key_exists($studCode, $kriitResults)) {
                $hasDifferences = false;
                $diffData = [
                    'studentName' => $t['studentName']
                ];

                // Check for grade differences
                if ($remoteGrade !== $kriitGrade) {
                    $diffData['kriitGrade'] = $kriitGrade;
                    $diffData['remoteGrade'] = $remoteGrade;
                    $hasDifferences = true;
                }

                // Check for active status differences if provided
                if (isset($t['studentIsActive'])) {
                    $student = User::findByPersonalCode($studCode);
                    if ($student && isset($student['userIsActive'])) {
                        $kriitIsActive = (bool)$student['userIsActive'];
                        if ($kriitIsActive != $studentIsActive) {
                            $diffData['kriitIsActive'] = $kriitIsActive;
                            $diffData['remoteIsActive'] = $studentIsActive;
                            $hasDifferences = true;
                        }
                    }
                }

                if ($hasDifferences) {
                    $diffs[$studCode] = $diffData;
                }
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