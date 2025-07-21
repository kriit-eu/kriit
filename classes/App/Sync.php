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
class Sync {
    /**
     * Sync a flat array of learning outcomes into LearningOutcomes table
     * @param array $payload Array of {subjectId, curriculumModuleOutcomes, outcomeName, learningOutcomeOrderNr}
     * @return int Number of inserted rows
     */
    public static function syncOutcomes($payload, $userId = null)
    {
        $changed = 0;
        foreach ($payload as $outcome) {
            $subjectId = $outcome['subjectId'] ?? null;
            $curriculumModuleOutcomes = $outcome['curriculumModuleOutcomes'] ?? null;
            $outcomeName = $outcome['outcomeName'] ?? null;
            $learningOutcomeOrderNr = $outcome['learningOutcomeOrderNr'] ?? null;
            if ($subjectId && $curriculumModuleOutcomes && $outcomeName) {
                $existing = \App\Db::getFirst(
                    "SELECT * FROM learningOutcomes WHERE subjectId = ? AND curriculumModuleOutcomes = ?",
                    [$subjectId, $curriculumModuleOutcomes]
                );
                if ($existing) {
                    // Only update if nameEt or learningOutcomeOrderNr has changed
                    $needsUpdate = false;
                    if ($existing['nameEt'] !== $outcomeName) {
                        $needsUpdate = true;
                    }
                    // Compare learningOutcomeOrderNr, allow nulls
                    $existingOrder = isset($existing['learningOutcomeOrderNr']) ? $existing['learningOutcomeOrderNr'] : null;
                    $incomingOrder = isset($learningOutcomeOrderNr) ? $learningOutcomeOrderNr : null;
                    if ($existingOrder != $incomingOrder) {
                        $needsUpdate = true;
                    }
                    if ($needsUpdate) {
                        \App\Db::update('learningOutcomes', [
                            'nameEt' => $outcomeName,
                            'learningOutcomeOrderNr' => $learningOutcomeOrderNr
                        ], 'subjectId = ? AND curriculumModuleOutcomes = ?', [$subjectId, $curriculumModuleOutcomes]);
                        $changed++;
                        \App\Activity::create(
                            defined('ACTIVITY_SYNC_START') ? ACTIVITY_SYNC_START : 18,
                            $userId,
                            null,
                            [
                                'subjectId' => $subjectId,
                                'curriculumModuleOutcomes' => $curriculumModuleOutcomes,
                                'nameEt' => $outcomeName,
                                'learningOutcomeOrderNr' => $learningOutcomeOrderNr,
                                'action' => 'learningOutcomes_update'
                            ]
                        );
                    }
                } else {
                    // Insert new outcome
                    \App\Db::insert('learningOutcomes', [
                        'subjectId' => $subjectId,
                        'curriculumModuleOutcomes' => $curriculumModuleOutcomes,
                        'nameEt' => $outcomeName,
                        'learningOutcomeOrderNr' => $learningOutcomeOrderNr
                    ]);
                    $changed++;
                    \App\Activity::create(
                        defined('ACTIVITY_SYNC_START') ? ACTIVITY_SYNC_START : 18,
                        $userId,
                        null,
                        [
                            'subjectId' => $subjectId,
                            'curriculumModuleOutcomes' => $curriculumModuleOutcomes,
                            'nameEt' => $outcomeName,
                            'learningOutcomeOrderNr' => $learningOutcomeOrderNr,
                            'action' => 'learningOutcomes_insert'
                        ]
                    );
                }
            }
        }
        return $changed;
    }

    /**
     * Inserts missing entities (teacher, group, subject, assignment, student) into Kriit
     * using the data from External System.
     *
     * @param array $remoteSubjects Array of subjects from External System, each with assignments and results
     * @param int $systemId The ID of the external system
     * @return void
     */
    public static function addMissingEntities($remoteSubjects, $systemId)
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
            $extId = $remoteSubject['subjectExternalId'];
            $matchingSubjects = isset($kriitSubjects[$extId]) ? $kriitSubjects[$extId] : [];

            // If subject not found, create it, along with assignments and students if needed
            if (empty($matchingSubjects)) {
                self::createKriitSubject($remoteSubject, $systemId);
                continue;
            }

            // Subject found, ensure assignments exist for all matching subject instances
            foreach ($matchingSubjects as $kriitSubject) {
                // 4) Ensure each assignment in Remote is in Kriit; create if missing
                self::ensureAssignmentsExist($remoteSubject['assignments'], $kriitSubject['subjectId'], $kriitSubject['assignments'], $systemId);
            }
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
    public static function getSystemDifferences($remoteSubjects, $systemId)
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
            $extId = $remoteSubject['subjectExternalId'];
            $matchingSubjects = $kriitSubjects[$extId] ?? [];

            // If subject not found, it should have been created via addMissingEntities
            // Skip from differences, because newly inserted = no difference
            if (empty($matchingSubjects)) {
                continue;
            }

            // Process each matching subject instance (one per group with participating students)
            foreach ($matchingSubjects as $kriitSubject) {
                // Only process if this Kriit subject instance matches the remote subject's group
                // or if the remote subject should appear under this group
                $shouldProcessThisGroup = ($kriitSubject['groupName'] == $remoteSubject['groupName']) ||
                    self::shouldSubjectAppearInGroup($remoteSubject, $kriitSubject['groupName'], $systemId);

                if (!$shouldProcessThisGroup) {
                    continue;
                }

                // Subject found, let's compare subject-level fields
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
                        $assignDiff = [
                            'assignmentExternalId' => $extId,
                            'assignmentId' => $kriitAssignment['assignmentId'],
                            'assignmentDueAt' => $kriitAssignment['assignmentDueAt']
                        ];
                        foreach ($fieldDiff as $field => $diffVal) {
    // Always use the latest value from the database (kriit) for assignment fields, unless it's null and remote has a value
    if ($diffVal['kriit'] !== null) {
        $assignDiff[$field] = $diffVal['kriit'];
    } else if ($diffVal['remote'] !== null) {
        $assignDiff[$field] = $diffVal['remote'];
    } else {
        $assignDiff[$field] = null;
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

                // If there are no assignment-level differences, we don't need to report this subject
                if (!$assignmentsDifferences) {
                    continue;
                }

                // Otherwise, build a minimal subject-level difference array
                $subjectDiff = [
                    'subjectExternalId' => $kriitSubject['subjectExternalId'],
                    'groupName' => $kriitSubject['groupName'] // Include the group this difference is for
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
    private static function loadKriitSubjectsData($remoteSubjects, $systemId)
    {
        $extIds = array_column($remoteSubjects, 'subjectExternalId');
        $extIdsString = implode(',', array_filter($extIds));
        if (!$extIdsString) {
            return [];
        }

        // Include student groups in addition to the subject's assigned group
        // This ensures we capture all groups that have students participating in the subject
        // We now include deleted students in the query to support reactivation
        $rows = Db::getAll("
            SELECT s.subjectId, s.subjectName, s.subjectExternalId, s.systemId,
                   COALESCE(ug.groupName, g.groupName) as groupName,
                   t.userPersonalCode AS teacherPersonalCode, t.userName AS teacherName,
                   a.assignmentId, a.assignmentExternalId, a.systemId as assignmentSystemId, a.assignmentName,
                   a.assignmentInstructions, a.assignmentDueAt, a.assignmentEntryDate,
                   ua.userGrade, st.userPersonalCode, st.userName, st.userDeleted
            FROM subjects s
            LEFT JOIN `groups` g ON s.groupId = g.groupId
            LEFT JOIN users t    ON s.teacherId = t.userId
            LEFT JOIN assignments a ON s.subjectId = a.subjectId
            LEFT JOIN userAssignments ua ON a.assignmentId = ua.assignmentId
            LEFT JOIN users st ON ua.userId = st.userId
            LEFT JOIN `groups` ug ON st.groupId = ug.groupId
            WHERE s.subjectExternalId IN ($extIdsString) AND s.systemId = ?
        ", [$systemId]);

        $subjects = [];
        foreach ($rows as $r) {
            // Create a unique key combining external ID and group name
            // This allows the same subject to appear under multiple groups
            $sxId = $r['subjectExternalId'];
            $groupName = $r['groupName'];
            $subjectKey = $sxId . '_' . $groupName;

            if (!isset($subjects[$subjectKey])) {
                $subjects[$subjectKey] = [
                    'subjectId'          => $r['subjectId'],
                    'subjectName'        => $r['subjectName'],
                    'subjectExternalId'  => $r['subjectExternalId'],
                    'systemId'           => $r['systemId'],
                    'groupName'          => $groupName,
                    'teacherPersonalCode' => $r['teacherPersonalCode'],
                    'teacherName'        => $r['teacherName'],
                    'assignments'        => []
                ];
            }
            $axId = $r['assignmentExternalId'];
            if (!isset($subjects[$subjectKey]['assignments'][$axId])) {
                $subjects[$subjectKey]['assignments'][$axId] = [
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
            // If there's a student record and they're not deleted, include them in the results
            // This ensures deleted students don't appear in the response to external system
            if ($r['userPersonalCode'] && (!isset($r['userDeleted']) || $r['userDeleted'] == 0)) {
                $subjects[$subjectKey]['assignments'][$axId]['results'][$r['userPersonalCode']] = [
                    'grade'       => $r['userGrade'],
                    'studentName' => $r['userName']
                ];
            }
        }

        // Convert back to indexed by external ID for compatibility, but now we have separate entries per group
        $result = [];
        foreach ($subjects as $subject) {
            $extId = $subject['subjectExternalId'];
            if (!isset($result[$extId])) {
                $result[$extId] = [];
            }
            $result[$extId][] = $subject;
        }

        return $result;
    }

    // This method has been moved to the Assignment class

    /**
     * Creates a subject in Kriit for $remoteSubject (including assignments and new students).
     * If the subject already exists (same external ID and system), it will be used instead.
     *
     * @param array $remoteSubject Subject data from External System
     * @param int $systemId The ID of the external system
     */
    private static function createKriitSubject($remoteSubject, $systemId)
    {
        $teacher = User::findByPersonalCode($remoteSubject['teacherPersonalCode']);
        $group   = Db::getFirst("SELECT * FROM `groups` WHERE groupName='{$remoteSubject['groupName']}'");

        // First, analyze all students to detect multiple groups
        $studentGroups = self::detectStudentGroups($remoteSubject);

        // Determine the subject's group display name
        $subjectGroupName = count($studentGroups) > 1 ?
            implode('/', array_keys($studentGroups)) :
            $remoteSubject['groupName'];

        // Check if subject already exists for this specific group (to avoid duplicate key constraint)
        $existingSubject = Db::getFirst(
            "SELECT subjectId FROM subjects WHERE subjectExternalId = ? AND systemId = ? AND groupId = ?",
            [$remoteSubject['subjectExternalId'], $systemId, $group['groupId']]
        );

        if ($existingSubject) {
            // Subject already exists, use its ID
            $subjId = $existingSubject['subjectId'];

            // Log that we're reusing an existing subject
            Activity::create(ACTIVITY_CREATE_SUBJECT_SYNC, $teacher['userId'], $subjId, [
                'systemId' => $systemId,
                'subjectName' => $remoteSubject['subjectName'],
                'subjectExternalId' => $remoteSubject['subjectExternalId'],
                'action' => 'reusing_existing_subject',
                'groupName' => $subjectGroupName,
                'originalGroupName' => $remoteSubject['groupName'],
                'detectedGroups' => array_keys($studentGroups)
            ]);
        } else {
            // Create new subject
            $subjId = Db::insert('subjects', [
                'subjectName'     => $remoteSubject['subjectName'],
                'subjectExternalId' => $remoteSubject['subjectExternalId'],
                'systemId'        => $systemId,
                'groupId'         => $group['groupId'], // Keep original for compatibility
                'teacherId'       => $teacher['userId']
            ]);

            // Log subject creation with detected multi-group info
            Activity::create(ACTIVITY_CREATE_SUBJECT_SYNC, $teacher['userId'], $subjId, [
                'systemId' => $systemId,
                'subjectName' => $remoteSubject['subjectName'],
                'subjectExternalId' => $remoteSubject['subjectExternalId'],
                'groupName' => $subjectGroupName,
                'originalGroupName' => $remoteSubject['groupName'],
                'detectedGroups' => array_keys($studentGroups)
            ]);
        }

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

            // Insert userAssignments for all students (with or without grades)
            foreach ($asm['results'] as $res) {
                // Determine the correct group for this student
                $studentGroup = self::determineStudentGroup($res, $studentGroups, $remoteSubject['groupName']);

                // If the student doesn't exist, create them
                $student = User::findByPersonalCode($res['studentPersonalCode']);
                if (!$student) {
                    $newUserId = User::createStudent(
                        $res['studentPersonalCode'],
                        $res['studentName'],
                        $systemId,
                        $studentGroup['groupId'], // Use detected group instead of subject's main group
                        $teacher['userId'],
                        $remoteSubject['subjectName']
                    );
                    $student = User::findById($newUserId);
                } else {
                    // Student exists - check if they need to be reassigned to correct group
                    if ($student['groupId'] != $studentGroup['groupId']) {
                        Db::update('users', ['groupId' => $studentGroup['groupId']], 'userId = ?', [$student['userId']]);

                        // Log group reassignment
                        Activity::create(ACTIVITY_UPDATE_USER_SYNC, $teacher['userId'], $student['userId'], [
                            'systemId' => $systemId,
                            'action' => 'group_reassignment',
                            'oldGroupId' => $student['groupId'],
                            'newGroupId' => $studentGroup['groupId'],
                            'oldGroupName' => self::getGroupNameById($student['groupId']),
                            'newGroupName' => $studentGroup['groupName'],
                            'reason' => 'Multi-group subject sync',
                            'subjectName' => $remoteSubject['subjectName']
                        ]);

                        // Update student data for further processing
                        $student['groupId'] = $studentGroup['groupId'];
                    }
                }

                if (!empty($res['grade'])) {
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
                } else {
                    // No grade: create userAssignment with null grade if not exists
                    $existingUA = \App\Db::getFirst(
                        "SELECT * FROM userAssignments WHERE assignmentId=? AND userId=?",
                        [$newAssignId, $student['userId']]
                    );
                    if (!$existingUA) {
                        \App\Db::insert('userAssignments', [
                            'assignmentId' => $newAssignId,
                            'userId' => $student['userId'],
                            'userGrade' => null,
                            'assignmentStatusId' => defined('ASSIGNMENT_STATUS_NOT_SUBMITTED') ? ASSIGNMENT_STATUS_NOT_SUBMITTED : 1, // 1 = Not submitted
                            'userAssignmentGradedAt' => null,
                            'comments' => '[]'
                        ]);
                    }
                }
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
    private static function ensureAssignmentsExist($remoteAssignments, $kriitSubjectId, $kriitAssignments, $systemId)
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
    private static function getAssignmentsInfo($assignmentIds)
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
    private static function preloadStudents($personalCodes, $systemId)
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
    private static function getSubjectsInfo($subjectIds)
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
    private static function resetCaches()
    {
        self::$studentCache = [];
        self::$assignmentInfoCache = [];
        self::$subjectInfoCache = [];
        self::$userAssignmentsCache = [];
    }

    /**
     * Preload all data needed for the sync process to minimize database queries
     */
    private static function preloadAllData($remoteSubjects, $systemId)
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

    /**
     * Ensures that all students and their grades from external system results are properly synchronized
     * with the Kriit system for a specific assignment.
     *
     * This function performs comprehensive student and grade synchronization by:
     * 1. Processing all students (with and without grades) from external system results
     * 2. Creating missing students in the Kriit system with appropriate group assignments
     * 3. Updating existing student information (names, active status, deleted status, group assignments)
     * 4. Creating or updating user assignments and grades
     * 5. Handling student deletion/undeletion based on external system data
     * 6. Preserving manual grade changes by not overwriting existing grades
     *
     * The function uses caching mechanisms to optimize database queries and avoid repeated lookups
     * for assignment info, student data, and user assignments.
     *
     * @param int $assignmentId The ID of the assignment in Kriit system to sync students and grades for
     * @param array $results Array of student result data from external system. Each result should contain:
     *                      - 'studentPersonalCode' (string): Unique identifier for the student
     *                      - 'studentName' (string): Full name of the student
     *                      - 'grade' (string|null): Grade value, can be empty/null for students without grades
     *                      - 'studentIsActive' (bool|null): Active status of the student
     *                      - 'studentIsDeleted' (bool|null): Deleted status of the student
     * @param int $systemId The ID of the external system (default: 1) used for activity logging and tracking
     *
     * @return void
     *
     * @throws \Exception If assignment data cannot be retrieved or if database operations fail
     *
     * @uses self::$assignmentInfoCache Static cache for assignment information to avoid repeated DB queries
     * @uses self::$studentCache Static cache for student data indexed by personal code
     * @uses self::$userAssignmentsCache Static cache for user assignment data indexed by assignment and user ID
     * @uses User::createStudent() To create new student users in the system
     * @uses User::updateNameIfNeeded() To update student names if they've changed in external system
     * @uses User::edit() To update student properties like active status and group assignments
     * @uses Assignment::setGrade() To set grades for students who have them
     * @uses Activity::create() To log various synchronization activities for audit trail
     * @uses self::detectStudentGroups() To determine which groups students should belong to
     * @uses self::determineStudentGroup() To assign individual students to appropriate groups
     *
     * @since 1.0.0
     */
    private static function ensureStudentsAndGrades($assignmentId, $results, $systemId)
    {
        if (empty($results)) {
            return;
        }

        // Process all results (students with and without grades)
        // Students without grades should still be added to the system
        $allResults = $results;

        // Extract valid results (with grades) - these will get grade assignments
        $validResults = array_filter($results, fn($r) => !empty($r['grade']));

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
        $personalCodes = array_column($allResults, 'studentPersonalCode');

        // Preload students into cache
        self::preloadStudents($personalCodes, $systemId);

        // Create a mock subject structure for group detection
        $mockSubject = [
            'groupName' => self::getGroupNameById($groupId),
            'assignments' => [['results' => $allResults]]
        ];

        // Detect student groups for this assignment
        $studentGroups = self::detectStudentGroups($mockSubject);

        // Process students first - create any missing students (including those without grades)
        $allStudentIds = [];
        $newStudentNames = []; // Track which students were created/updated for later use

        foreach ($allResults as $r) {
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

                // Update student deleted status if provided
                if (isset($r['studentIsDeleted'])) {
                    $isDeleted = (bool)$r['studentIsDeleted'];
                    if ($student['userDeleted'] != $isDeleted) {
                        User::edit($student['userId'], ['userDeleted' => $isDeleted ? 1 : 0]);

                        // Log the deletion/undeletion
                        Activity::create(ACTIVITY_UPDATE_USER_SYNC, $teacherId, $student['userId'], [
                            'systemId' => $systemId,
                            'action' => $isDeleted ? 'marked_deleted' : 'unmarked_deleted',
                            'reason' => 'External system sync - studentIsDeleted field',
                            'subjectName' => $subjectName,
                            'assignmentName' => $assignmentInfo['assignmentName']
                        ]);

                        // Update our cache
                        self::$studentCache[$personalCode]['userDeleted'] = $isDeleted ? 1 : 0;
                    }
                }

                // Check if student needs group reassignment
                $correctGroup = self::determineStudentGroup($r, $studentGroups, $mockSubject['groupName']);
                if ($student['groupId'] != $correctGroup['groupId']) {
                    User::edit($student['userId'], ['groupId' => $correctGroup['groupId']]);

                    // Log group reassignment
                    Activity::create(ACTIVITY_UPDATE_USER_SYNC, $teacherId, $student['userId'], [
                        'systemId' => $systemId,
                        'action' => 'group_reassignment_existing',
                        'oldGroupId' => $student['groupId'],
                        'newGroupId' => $correctGroup['groupId'],
                        'oldGroupName' => self::getGroupNameById($student['groupId']),
                        'newGroupName' => $correctGroup['groupName'],
                        'reason' => 'Multi-group assignment sync',
                        'subjectName' => $subjectName,
                        'assignmentId' => $assignmentId
                    ]);

                    // Update cache
                    self::$studentCache[$personalCode]['groupId'] = $correctGroup['groupId'];
                }

                $allStudentIds[$personalCode] = $student['userId'];
                $newStudentNames[$personalCode] = $r['studentName'];
            } else {
                // Student doesn't exist - create them with correct group
                $correctGroup = self::determineStudentGroup($r, $studentGroups, $mockSubject['groupName']);
                $isActive = isset($r['studentIsActive']) ? (bool)$r['studentIsActive'] : true;

                $newUserId = User::createStudent(
                    $personalCode,
                    $r['studentName'],
                    $systemId,
                    $correctGroup['groupId'], // Use detected group instead of assignment's main group
                    $teacherId,
                    $subjectName,
                    $isActive
                );
                $student = User::findById($newUserId);

                // If student is marked as deleted in external system, mark them as deleted immediately
                if (isset($r['studentIsDeleted']) && (bool)$r['studentIsDeleted']) {
                    User::edit($student['userId'], ['userDeleted' => 1]);

                    // Log the deletion
                    Activity::create(ACTIVITY_UPDATE_USER_SYNC, $teacherId, $student['userId'], [
                        'systemId' => $systemId,
                        'action' => 'marked_deleted_on_creation',
                        'reason' => 'External system sync - student created as deleted',
                        'subjectName' => $subjectName,
                        'assignmentName' => $assignmentInfo['assignmentName']
                    ]);

                    // Update student data and cache
                    $student['userDeleted'] = 1;
                }

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

        // Note: Removed aggressive student deletion logic that was marking students as deleted
        // based on individual assignment data. Students should only be marked as deleted
        // when they are completely absent from the external system across all subjects,
        // not just missing from individual assignments.

        // Create userAssignments for all students, but only set grades for those who have them
        foreach ($allResults as $r) {
            $personalCode = $r['studentPersonalCode'];
            $userId = $allStudentIds[$personalCode];

            // Handle student deletion status based on external system data
            $student = User::findById($userId);
            if ($student && $student['userDeleted']) {
                // Student is currently marked as deleted in Kriit
                // Only undelete if external system explicitly says student is not deleted
                if (isset($r['studentIsDeleted']) && !(bool)$r['studentIsDeleted']) {
                    Db::update('users', ['userDeleted' => 0], "userId = {$userId}");

                    // Log the undeletion
                    Activity::create(ACTIVITY_UPDATE_USER_SYNC, $teacherId, $userId, [
                        'systemId' => $systemId,
                        'action' => 'unmarked_deleted',
                        'reason' => 'External system explicitly marked as not deleted',
                        'subjectName' => $subjectName,
                        'assignmentName' => $assignmentInfo['assignmentName']
                    ]);
                } elseif (!isset($r['studentIsDeleted'])) {
                    // If no deletion status provided, assume student should be undeleted since they appear in data
                    Db::update('users', ['userDeleted' => 0], "userId = {$userId}");

                    // Log the undeletion
                    Activity::create(ACTIVITY_UPDATE_USER_SYNC, $teacherId, $userId, [
                        'systemId' => $systemId,
                        'action' => 'unmarked_deleted',
                        'reason' => 'Reappeared in external system data without deletion flag',
                        'subjectName' => $subjectName,
                        'assignmentName' => $assignmentInfo['assignmentName']
                    ]);
                }
            }

            // If no userAssignment exists for this student and assignment
            if (!isset($existingUserAssignments[$userId])) {
                // Check if student has a grade
                if (!empty($r['grade'])) {
                    // Student has a grade - set it
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
                } else {
                    // Student has no grade - create userAssignment without grade
                    // This ensures the student is associated with the assignment even without a grade

                    // Double-check that the userAssignment doesn't exist (race condition protection)
                    $existingCheck = Db::getFirst("
                        SELECT * FROM userAssignments
                        WHERE assignmentId = ? AND userId = ?
                    ", [$assignmentId, $userId]);

                    if (!$existingCheck) {
                        $currentTime = date('Y-m-d H:i:s');
                        Db::insert('userAssignments', [
                            'assignmentId' => $assignmentId,
                            'userId'       => $userId,
                            'userGrade'    => null,
                            'assignmentStatusId' => ASSIGNMENT_STATUS_NOT_SUBMITTED, // 1 = Not submitted
                            'userAssignmentGradedAt' => null,
                            'comments' => '[]'
                        ]);

                        // Log activity for creating user assignment without grade
                        if ($teacherId !== null) {
                            Activity::create(ACTIVITY_CREATE_ASSIGNMENT_SYNC, $teacherId, $assignmentId, [
                                'systemId' => $systemId,
                                'assignmentName' => $assignmentInfo['assignmentName'],
                                'studentName' => $newStudentNames[$personalCode],
                                'action' => 'created_user_assignment_without_grade',
                                'subjectName' => $subjectName ?? 'Unknown'
                            ]);
                        }
                    }
                }
            }
            // Note: If userAssignment already exists, we don't overwrite grades to preserve manual changes
        }
    }

    /**
     * Compares subjectName, groupName, teacherPersonalCode, teacherName between Kriit & Remote,
     * returning an array of only fields that differ. The array is [fieldName => ['kriit' => ..., 'remote' => ...]].
     */
    private static function diffSubjectFields($kriitSubject, $remoteSubject)
    {
        $check = ['subjectName', 'groupName', 'teacherPersonalCode', 'teacherName'];
        $diffs = [];
        foreach ($check as $fld) {
            $kriitVal = isset($kriitSubject[$fld]) ? $kriitSubject[$fld] : '';
            $remoteVal = isset($remoteSubject[$fld]) ? $remoteSubject[$fld] : '';
            if ($kriitVal !== $remoteVal) {
                $diffs[$fld] = [
                    'kriit'  => $kriitVal,
                    'remote' => $remoteVal
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
    private static function diffAssignmentFields($kriitAssignment, $remoteAssignment)
    {
        $check = ['assignmentName', 'assignmentInstructions', 'assignmentDueAt', 'assignmentEntryDate'];
        $diffs = [];
        // Only compare if assignmentExternalId exists in both
        $kriitId = isset($kriitAssignment['assignmentExternalId']) ? $kriitAssignment['assignmentExternalId'] : null;
        $remoteId = isset($remoteAssignment['assignmentExternalId']) ? $remoteAssignment['assignmentExternalId'] : null;
        if ($kriitId && $remoteId && $kriitId == $remoteId) {
            foreach ($check as $fld) {
                $kriitVal = isset($kriitAssignment[$fld]) ? $kriitAssignment[$fld] : null;
                $remoteVal = isset($remoteAssignment[$fld]) ? $remoteAssignment[$fld] : null;

                // For assignmentName, assignmentDueAt, and assignmentEntryDate, always return as difference object if IDs match and values differ
                if (($fld === 'assignmentName' || $fld === 'assignmentDueAt' || $fld === 'assignmentEntryDate') && $kriitVal !== $remoteVal) {
                    $diffs[$fld] = [
                        'kriit' => $kriitVal,
                        'remote' => $remoteVal
                    ];
                } else {
                    // For other fields, use previous logic
                    if (
                        isset($remoteAssignment[$fld]) &&
                        (($kriitVal === null && $remoteVal !== null) || $kriitVal !== $remoteVal)
                    ) {
                        $diffs[$fld] = [
                            'kriit' => $kriitVal,
                            'remote' => $remoteVal
                        ];
                    }
                }
            }
        }
        return $diffs;
    }

    /**
     * For each Remote result, checks if Kriit has a different grade for that student.
     * Only returns differences for students who already exist in Kriit (ensured by the array keys in $kriitResults).
     * Returns: [studentCode => ['kriitGrade'=>..., 'remoteGrade'=>..., 'studentName'=>...], ...]
     */
    private static function diffAssignmentResults($kriitResults, $remoteResults)
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
                    // But don't count as a difference if Kriit has no grade and remote grade is empty
                    if (!($kriitGrade === null && $remoteGrade === '')) {
                        $diffData['kriitGrade'] = $kriitGrade;
                        $diffData['remoteGrade'] = $remoteGrade;
                        $hasDifferences = true;
                    }
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

                // Check for deleted status differences if provided
                if (isset($t['studentIsDeleted'])) {
                    $student = User::findByPersonalCode($studCode);
                    if ($student && isset($student['userDeleted'])) {
                        $kriitIsDeleted = (bool)$student['userDeleted'];
                        $remoteIsDeleted = (bool)$t['studentIsDeleted'];
                        if ($kriitIsDeleted != $remoteIsDeleted) {
                            $diffData['kriitIsDeleted'] = $kriitIsDeleted;
                            $diffData['remoteIsDeleted'] = $remoteIsDeleted;
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
    private static function findOrCreate($table, $field, $value, $insertData)
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

    /**
     * Detects which groups are represented in a subject's student data
     * 
     * @param array $remoteSubject Subject data from External System
     * @return array Array of groupName => groupData mappings
     */
    private static function detectStudentGroups($remoteSubject)
    {
        $detectedGroups = [];
        $studentsToAnalyze = [];

        // Collect all students from all assignments in this subject
        foreach ($remoteSubject['assignments'] as $assignment) {
            foreach ($assignment['results'] as $result) {
                $personalCode = $result['studentPersonalCode'];
                if (!isset($studentsToAnalyze[$personalCode])) {
                    $studentsToAnalyze[$personalCode] = $result;
                }
            }
        }

        // Check which groups these students belong to in other subjects
        if (!empty($studentsToAnalyze)) {
            $personalCodes = array_keys($studentsToAnalyze);
            $placeholders = implode(',', array_fill(0, count($personalCodes), '?'));

            // Find existing students and their current groups
            $existingStudents = Db::getAll("
                SELECT u.userPersonalCode, u.groupId, g.groupName 
                FROM users u 
                LEFT JOIN groups g ON u.groupId = g.groupId 
                WHERE u.userPersonalCode IN ({$placeholders})
            ", $personalCodes);

            // Group students by their current groups
            foreach ($existingStudents as $student) {
                if ($student['groupName']) {
                    if (!isset($detectedGroups[$student['groupName']])) {
                        $detectedGroups[$student['groupName']] = [
                            'groupId' => $student['groupId'],
                            'groupName' => $student['groupName'],
                            'studentCodes' => []
                        ];
                    }
                    $detectedGroups[$student['groupName']]['studentCodes'][] = $student['userPersonalCode'];
                }
            }

            // For students not yet in database, try to infer group from personal code patterns
            $newStudents = array_filter($studentsToAnalyze, function ($personalCode) use ($existingStudents) {
                return !array_filter($existingStudents, fn($s) => $s['userPersonalCode'] === $personalCode);
            }, ARRAY_FILTER_USE_KEY);

            foreach ($newStudents as $personalCode => $studentData) {
                // No longer inferring groups - just use the subject's default group
                if (!isset($detectedGroups[$remoteSubject['groupName']])) {
                    $subjectGroup = Db::getFirst("SELECT * FROM `groups` WHERE groupName = ?", [$remoteSubject['groupName']]);
                    if ($subjectGroup) {
                        $detectedGroups[$remoteSubject['groupName']] = [
                            'groupId' => $subjectGroup['groupId'],
                            'groupName' => $remoteSubject['groupName'],
                            'studentCodes' => []
                        ];
                    }
                }
                if (isset($detectedGroups[$remoteSubject['groupName']])) {
                    $detectedGroups[$remoteSubject['groupName']]['studentCodes'][] = $personalCode;
                }
            }
        }

        // Always include the subject's primary group
        $primaryGroup = Db::getFirst("SELECT * FROM `groups` WHERE groupName = ?", [$remoteSubject['groupName']]);
        if ($primaryGroup) {
            if (!isset($detectedGroups[$remoteSubject['groupName']])) {
                $detectedGroups[$remoteSubject['groupName']] = [
                    'groupId' => $primaryGroup['groupId'],
                    'groupName' => $remoteSubject['groupName'],
                    'studentCodes' => []
                ];
            }
        }

        return $detectedGroups;
    }

    /**
     * Determines the correct group for a specific student
     * 
     * @param array $studentResult Student data from external system
     * @param array $detectedGroups Detected groups from detectStudentGroups
     * @param string $defaultGroupName Default group name from subject
     * @return array Group data (groupId, groupName)
     */
    private static function determineStudentGroup($studentResult, $detectedGroups, $defaultGroupName)
    {
        $personalCode = $studentResult['studentPersonalCode'];

        // Check if student belongs to any of the detected groups
        foreach ($detectedGroups as $groupData) {
            if (in_array($personalCode, $groupData['studentCodes'])) {
                return $groupData;
            }
        }

        // Check if student already exists and has a group
        $existingStudent = User::findByPersonalCode($personalCode);
        if ($existingStudent && $existingStudent['groupId']) {
            $existingGroup = Db::getFirst("SELECT * FROM `groups` WHERE groupId = ?", [$existingStudent['groupId']]);
            if ($existingGroup) {
                return [
                    'groupId' => $existingGroup['groupId'],
                    'groupName' => $existingGroup['groupName']
                ];
            }
        }

        // Always fall back to subject's default group from external system
        $defaultGroup = Db::getFirst("SELECT * FROM `groups` WHERE groupName = ?", [$defaultGroupName]);
        return [
            'groupId' => $defaultGroup['groupId'],
            'groupName' => $defaultGroupName
        ];
    }

    /**
     * Infers a student's group from their personal code and other data patterns
     * 
     * @param string $personalCode Student's personal code
     * @param array $studentData Student data from external system
     * @param string $defaultGroupName Default group name
     * @return string|null Inferred group name or null if can't determine
     */
    private static function inferGroupFromStudent($personalCode, $studentData, $defaultGroupName)
    {
        // For this implementation, we'll use a simple heuristic based on birth year patterns
        // This can be improved with more sophisticated logic based on your school's data patterns

        // Extract birth year from personal code (first 3 digits represent year in Estonian format)
        if (strlen($personalCode) >= 3) {
            $birthYear = substr($personalCode, 1, 2);
            $century = substr($personalCode, 0, 1);

            // Convert to full year based on century digit
            if (in_array($century, ['3', '4'])) {
                $fullYear = '19' . $birthYear;
            } elseif (in_array($century, ['5', '6'])) {
                $fullYear = '20' . $birthYear;
            } else {
                $fullYear = null;
            }

            if ($fullYear) {
                $age = date('Y') - intval($fullYear);

                // If student is significantly younger/older than expected for default group,
                // try to find a more appropriate group
                if ($age < 16) {
                    // Very young student, might be in a different year group
                    $possibleGroups = ['AM24', 'KE24', 'SAT24'];
                } elseif ($age > 20) {
                    // Older student, might be in earlier year groups
                    $possibleGroups = ['AM22', 'KE22', 'SAT22'];
                } else {
                    // Normal age range
                    $possibleGroups = ['AM23', 'KE23', 'SAT23'];
                }

                // Return the first existing group that matches the pattern and isn't the default
                foreach ($possibleGroups as $groupName) {
                    if ($groupName !== $defaultGroupName) {
                        $group = Db::getFirst("SELECT groupId FROM `groups` WHERE groupName = ?", [$groupName]);
                        if ($group) {
                            return $groupName;
                        }
                    }
                }
            }
        }

        return null; // Can't determine, use default
    }

    /**
     * Helper method to get group name by ID
     * 
     * @param int $groupId Group ID
     * @return string Group name or 'Unknown'
     */
    private static function getGroupNameById($groupId)
    {
        $group = Db::getFirst("SELECT groupName FROM `groups` WHERE groupId = ?", [$groupId]);
        return $group ? $group['groupName'] : 'Unknown';
    }

    /**
     * Check if a subject should appear under a specific group.
     * This happens when students from that group are participating in the subject,
     * even if the subject is officially assigned to a different group.
     * 
     * @param array $remoteSubject Remote subject data
     * @param string $groupName Group name to check
     * @param int $systemId External system ID
     * @return bool True if subject should appear under this group
     */
    private static function shouldSubjectAppearInGroup($remoteSubject, $groupName, $systemId)
    {
        // Check if there are any students from this group participating in the subject
        $studentCount = Db::getFirst("
            SELECT COUNT(*) as count
            FROM users u
            INNER JOIN groups g ON u.groupId = g.groupId  
            INNER JOIN userAssignments ua ON u.userId = ua.userId
            INNER JOIN assignments a ON ua.assignmentId = a.assignmentId
            INNER JOIN subjects s ON a.subjectId = s.subjectId
            WHERE s.subjectExternalId = ?
            AND s.systemId = ?
            AND g.groupName = ?
        ", [$remoteSubject['subjectExternalId'], $systemId, $groupName]);

        return (int)$studentCount['count'] > 0;
    }
}
