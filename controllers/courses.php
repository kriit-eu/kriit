<?php
namespace App;

class courses extends Controller
{
    public $requires_auth = true;
    // Allow teachers (and admins) to access courses; not admin-only
    public $requires_admin = false;
    public $template = 'master';

    function index(): void
    {
        // Only teachers and admins may access the courses listing
        if (!$this->auth->userIsTeacher && !$this->auth->userIsAdmin) {
            stop(403, 'Access denied');
        }

        // List all courses
        $hasSortOrder = Db::getOne("SHOW COLUMNS FROM courses LIKE 'sortOrder'") ? true : false;
        if ($hasSortOrder) {
            $this->courses = Db::getAll("SELECT * FROM courses ORDER BY sortOrder, id");
        } else {
            $this->courses = Db::getAll("SELECT * FROM courses ORDER BY id");
        }
    }

    function view(): void
    {
        $courseId = $this->getId();
        $isTeacherOrAdmin = $this->auth->userIsTeacher || $this->auth->userIsAdmin;
        $isStudent = !$isTeacherOrAdmin;

        // Basic course info
        $this->course = Db::getFirst("SELECT * FROM courses WHERE id = ?", [$courseId]);

        if (!$this->course) {
            stop(404, 'Kursust ei leitud.');
        }

        // Visibility: if private, teachers/admins must own it; students bypass visibility per requirements
        if ($this->course['visibility'] === 'private' && $isTeacherOrAdmin) {
            if (!$this->auth->userIsAdmin && intval($this->course['createdBy']) !== intval($this->auth->userId)) {
                stop(403, 'Sul puudub õigus seda kursust vaadata.');
            }
        }

        if ($isStudent) {
            $groupId = $this->auth->groupId;
            $userId = $this->auth->userId;

            $hasAssignment = Db::getOne(
                "SELECT COUNT(1)
                 FROM assignments a
                 JOIN subjects s ON a.subjectId = s.subjectId
                 WHERE a.courseId = ? AND s.groupId = ?",
                [$courseId, $groupId]
            );

            if (!$hasAssignment) {
                $hasAssignment = Db::getOne(
                    "SELECT COUNT(1)
                     FROM assignments a
                     JOIN userAssignments ua ON ua.assignmentId = a.assignmentId AND ua.userId = ?
                     WHERE a.courseId = ?",
                    [$userId, $courseId]
                );
            }

            if (!$hasAssignment) {
                stop(404, 'Kursust ei leitud.');
            }
        }

        $this->courseIsActive = ($this->course['status'] ?? '') === 'active';
        $hasCourseColumn = Db::getOne("SHOW COLUMNS FROM exercises LIKE 'courseId'") ? true : false;
        $hasSortOrder = Db::getOne("SHOW COLUMNS FROM exercises LIKE 'sortOrder'") ? true : false;
        $orderBy = $hasSortOrder ? 'ORDER BY sortOrder, exerciseId' : 'ORDER BY exerciseId';

        if ($hasCourseColumn) {
            if ($isStudent) {
                $this->exercises = Db::getAll(
                    "SELECT e.*, ue.status AS userStatus, ue.startTime, ue.endTime
                     FROM exercises e
                     LEFT JOIN userExercisesWithComputedStatus ue
                        ON ue.exerciseId = e.exerciseId AND ue.userId = ?
                     WHERE e.courseId = ?
                     {$orderBy}",
                    [$this->auth->userId, $courseId]
                );
            } else {
                $this->exercises = Db::getAll(
                    "SELECT * FROM exercises WHERE courseId = ? {$orderBy}",
                    [$courseId]
                );
            }
        } else {
            // Backwards compatibility: associate all exercises with default course id 1
            if ($courseId == 1) {
                if ($isStudent) {
                    $this->exercises = Db::getAll(
                        "SELECT e.*, ue.status AS userStatus, ue.startTime, ue.endTime
                         FROM exercises e
                         LEFT JOIN userExercisesWithComputedStatus ue
                            ON ue.exerciseId = e.exerciseId AND ue.userId = ?
                         {$orderBy}",
                        [$this->auth->userId]
                    );
                } else {
                    $this->exercises = Db::getAll("SELECT * FROM exercises {$orderBy}");
                }
            } else {
                $this->exercises = [];
            }
        }

        $allowedTabs = ['overview', 'exercises', 'ranking'];
        $defaultTab = $isStudent ? 'overview' : 'exercises';
        $requestedTab = $_GET['tab'] ?? $defaultTab;
        $this->tab = in_array($requestedTab, $allowedTabs, true) ? $requestedTab : $defaultTab;

        $hasCourseColumn = Db::getOne("SHOW COLUMNS FROM exercises LIKE 'courseId'") ? true : false;

        if ($hasCourseColumn) {
            $allUsers = Db::getAll(
                "
                SELECT
                    u.*,
                    COUNT(DISTINCT ue.exerciseId) AS userExercisesDone,
                    MIN(a.activityLogTimestamp) AS userFirstLogin,
                    ROW_NUMBER() OVER (
                        ORDER BY
                            COUNT(DISTINCT ue.exerciseId) DESC,
                            CASE
                                WHEN u.userTimeTotal IS NOT NULL THEN 0
                                ELSE 1
                            END ASC,
                            u.userTimeTotal ASC,
                            u.userId ASC
                    ) AS userRank
                FROM
                    users u
                LEFT JOIN
                    activityLog a ON u.userId = a.userId AND a.activityId = 1
                LEFT JOIN
                    userExercisesWithComputedStatus ue ON u.userId = ue.userId AND ue.status = 'completed'
                LEFT JOIN
                    exercises e ON ue.exerciseId = e.exerciseId AND e.courseId = ?
                WHERE
                    u.userIsAdmin = 0
                    AND u.userIsTeacher = 0
                    AND u.groupId IS NULL
                GROUP BY
                    u.userId
                ORDER BY
                    userRank ASC
                ", [$courseId]
            );
        } else {
            $allUsers = Db::getAll("\n        SELECT\n            u.*,\n            COUNT(DISTINCT ue.exerciseId) AS userExercisesDone,\n            MIN(a.activityLogTimestamp) AS userFirstLogin,\n            ROW_NUMBER() OVER (\n                ORDER BY\n                    COUNT(DISTINCT ue.exerciseId) DESC,\n                    CASE\n                        WHEN u.userTimeTotal IS NOT NULL THEN 0\n                        ELSE 1\n                    END ASC,\n                    u.userTimeTotal ASC,\n                    u.userId ASC\n            ) AS userRank\n        FROM\n            users u\n        LEFT JOIN\n            activityLog a ON u.userId = a.userId AND a.activityId = 1\n        LEFT JOIN\n            userExercisesWithComputedStatus ue ON u.userId = ue.userId AND ue.status = 'completed'\n        WHERE\n            u.userIsAdmin = 0\n            AND u.userIsTeacher = 0\n            AND u.groupId IS NULL\n        GROUP BY\n            u.userId\n        ORDER BY\n            userRank ASC;\n    ");
        }

        $this->filteredUsers = array_filter($allUsers, function ($user) {
            return $user['userExercisesDone'] > 0;
        });

        $this->users = $allUsers;

        $totalSolvedTasks = array_sum(array_column($this->filteredUsers, 'userExercisesDone'));
        $userCount = count($this->filteredUsers);
        $this->averageExercisesDone = $userCount > 0 ? $totalSolvedTasks / $userCount : 0;

        if ($isStudent) {
            $completed = 0;
            foreach ($this->exercises as &$exercise) {
                $status = $exercise['userStatus'] ?? 'not_started';
                $label = 'Tegemata';
                $action = 'Ava';
                $actionClass = 'btn-primary';

                if ($status === 'completed') {
                    $label = 'Tehtud';
                    $action = 'Vaata';
                    $actionClass = 'btn-outline-success';
                    $completed++;
                } elseif ($status === 'started') {
                    $label = 'Pooleli';
                    $action = 'Jätka';
                    $actionClass = 'btn-warning text-dark';
                }

                $exercise['statusLabel'] = $label;
                $exercise['actionLabel'] = $action;
                $exercise['actionClass'] = $actionClass;
                $exercise['actionDisabled'] = !$this->courseIsActive;
            }
            unset($exercise);

            $this->completedExercisesCount = $completed;
            $this->totalExercisesCount = count($this->exercises);
            $this->progressLabel = sprintf('Edenemine: %d/%d', $completed, $this->totalExercisesCount);

            $this->studentRanking = array_map(function ($user) {
                return [
                    'userRank' => $user['userRank'],
                    'userName' => $user['userName'],
                    'userExercisesDone' => $user['userExercisesDone'],
                ];
            }, $this->users ?? []);

            $this->linkedAssignments = Db::getAll(
                "SELECT DISTINCT a.assignmentId, a.assignmentName, a.assignmentDueAt
                 FROM assignments a
                 JOIN subjects s ON s.subjectId = a.subjectId
                 LEFT JOIN userAssignments ua ON ua.assignmentId = a.assignmentId AND ua.userId = ?
                 WHERE a.courseId = ?
                   AND (s.groupId = ? OR ua.userId IS NOT NULL)
                 ORDER BY ISNULL(a.assignmentDueAt), a.assignmentDueAt",
                [$this->auth->userId, $courseId, $this->auth->groupId]
            );

            $this->showExerciseSavedToast = isset($_GET['exerciseSaved']) && $_GET['exerciseSaved'] === '1';
            $this->action = 'view_student';
        }

        // For teachers/admins also populate linked assignments so the overview shows them
        if (!isset($this->linkedAssignments)) {
            if ($this->auth->userIsAdmin) {
                $this->linkedAssignments = Db::getAll(
                    "SELECT assignmentId, assignmentName, assignmentDueAt FROM assignments WHERE courseId = ? ORDER BY ISNULL(assignmentDueAt), assignmentDueAt",
                    [$courseId]
                );
            } else {
                $this->linkedAssignments = Db::getAll(
                    "SELECT a.assignmentId, a.assignmentName, a.assignmentDueAt
                     FROM assignments a
                     JOIN subjects s ON s.subjectId = a.subjectId
                     WHERE a.courseId = ? AND s.teacherId = ?
                     ORDER BY ISNULL(a.assignmentDueAt), a.assignmentDueAt",
                    [$courseId, $this->auth->userId]
                );
            }
        }

        $this->isStudent = $isStudent;
        $this->courseId = $courseId;
    }

    function ranking(): void
    {
        // Only teachers and admins may access ranking
        if (!$this->auth->userIsTeacher && !$this->auth->userIsAdmin) {
            stop(403, 'Access denied');
        }

        $courseId = $this->getId();
        // Redirect to course view with tab=ranking so user stays on same page and tabs are handled client-side
        header('Location: ' . BASE_URL . "courses/{$courseId}?tab=ranking");
        exit();
    }

    /**
     * AJAX: return courses visible to the current user (teacher's own courses or all for admin)
     */
    function ajax_getMyCourses(): void
    {
        header('Content-Type: application/json; charset=utf-8');
        if (!$this->auth->userIsTeacher && !$this->auth->userIsAdmin) {
            http_response_code(403);
            echo json_encode(['error' => 'Access denied']);
            exit();
        }
        // Some installations might not have a sortOrder column; detect it and adjust ORDER BY accordingly
        $hasSortOrder = Db::getOne("SHOW COLUMNS FROM courses LIKE 'sortOrder'") ? true : false;
        $orderBy = $hasSortOrder ? 'ORDER BY sortOrder, id' : 'ORDER BY id';

        if ($this->auth->userIsAdmin) {
            $courses = Db::getAll("SELECT id AS courseId, name AS courseName FROM courses {$orderBy}");
        } else {
            // Teacher: return courses created by this teacher when possible, fallback to all
            $hasCreatedBy = Db::getOne("SHOW COLUMNS FROM courses LIKE 'createdBy'") ? true : false;
            if ($hasCreatedBy) {
                $courses = Db::getAll("SELECT id AS courseId, name AS courseName FROM courses WHERE createdBy = ? {$orderBy}", [$this->auth->userId]);
            } else {
                $courses = Db::getAll("SELECT id AS courseId, name AS courseName FROM courses {$orderBy}");
            }
        }

        echo json_encode(['courses' => $courses]);
        exit();
    }

    /**
     * Return assignments grouped by subject for the current user to populate the dropdown.
     * Admins see all subjects/assignments; teachers see only their own subjects.
     */
    function assignments_for_dropdown(): void
    {
        header('Content-Type: application/json; charset=utf-8');
        if (!$this->auth->userIsTeacher && !$this->auth->userIsAdmin) {
            http_response_code(403);
            echo json_encode(['error' => 'Access denied']);
            exit();
        }

        if ($this->auth->userIsAdmin) {
            $subjects = Db::getAll("SELECT subjectId, subjectName, groupId, teacherId FROM subjects ORDER BY subjectName");
        } else {
            // teacher: subjects where subjects.teacherId equals current user
            $subjects = Db::getAll("SELECT subjectId, subjectName, groupId, teacherId FROM subjects WHERE teacherId = ? ORDER BY subjectName", [$this->auth->userId]);
        }

        // also include groups and teacher lists to populate filter menus on the client
        $groups = Db::getAll("SELECT groupId, groupName FROM groups ORDER BY groupName");
        $teachers = Db::getAll("SELECT userId, userName FROM users WHERE userIsTeacher = 1 ORDER BY userName");

        $out = ['subjects' => [], 'teacherHasSubjects' => count($subjects) > 0, 'groups' => $groups, 'teachers' => $teachers];
        foreach ($subjects as $s) {
            // assignments table uses assignmentName; align keys for frontend (id/name)
            $assignments = Db::getAll("SELECT assignmentId, assignmentName AS name FROM assignments WHERE subjectId = ? ORDER BY assignmentName", [$s['subjectId']]);
            $out['subjects'][] = ['id' => $s['subjectId'], 'name' => $s['subjectName'], 'groupId' => $s['groupId'], 'teacherId' => $s['teacherId'], 'assignments' => $assignments];
        }

        echo json_encode($out);
        exit();
    }

    // AJAX: create exercise within this course (teachers and admins)
    function AJAX_createExercise()
    {
        if (!$this->auth->userIsTeacher && !$this->auth->userIsAdmin) {
            stop(403, 'Access denied');
        }

        if (empty($_POST['exercise_name'])) {
            stop(400, 'Exercise name is required.');
        }

        $data = [
            'exerciseName' => $_POST['exercise_name'],
            'exerciseInstructions' => $_POST['instructions'] ?? '',
            'exerciseInitialCode' => $_POST['initial_code'] ?? '',
            'exerciseValidationFunction' => $_POST['validation_function'] ?? '',
        ];

        // Attach courseId if provided and column exists
        $hasCourseColumn = Db::getOne("SHOW COLUMNS FROM exercises LIKE 'courseId'") ? true : false;
        if ($hasCourseColumn && !empty($_POST['courseId']) && is_numeric($_POST['courseId'])) {
            $data['courseId'] = (int)$_POST['courseId'];
        }

        try {
            $exerciseId = Db::insert('exercises', $data);
        } catch (\Exception $e) {
            stop(400, $e->getMessage());
        }

        stop(200, ['id' => $exerciseId]);
    }

    function AJAX_editExercise()
    {
        if (!$this->auth->userIsTeacher && !$this->auth->userIsAdmin) {
            stop(403, 'Access denied');
        }

        $exerciseId = $_POST['id'] ?? null;
        if (empty($exerciseId) || !is_numeric($exerciseId)) {
            stop(400, 'Invalid exercise id');
        }

        if (isset($_POST['exercise_name'])) {
            Db::update('exercises', ['exerciseName' => $_POST['exercise_name']], 'exerciseId = ?', [$exerciseId]);
        }

        if (isset($_POST['instructions'])) {
            Db::update('exercises', ['exerciseInstructions' => $_POST['instructions']], 'exerciseId = ?', [$exerciseId]);
        }

        if (isset($_POST['initial_code'])) {
            Db::update('exercises', ['exerciseInitialCode' => $_POST['initial_code']], 'exerciseId = ?', [$exerciseId]);
        }

        if (isset($_POST['validation_function'])) {
            Db::update('exercises', ['exerciseValidationFunction' => $_POST['validation_function']], 'exerciseId = ?', [$exerciseId]);
        }

        stop(200);
    }

    function AJAX_deleteExercise()
    {
        if (!$this->auth->userIsTeacher && !$this->auth->userIsAdmin) {
            stop(403, 'Access denied');
        }

        if (empty($_POST['id']) || !is_numeric($_POST['id'])) {
            stop(400, 'Invalid exercise id');
        }

        Db::delete('userExercises', 'exerciseId = ?', [$_POST['id']]);
        Db::delete('exercises', 'exerciseId = ?', [$_POST['id']]);

        stop(200);
    }

    /**
     * Create a new course (POST).
     */
    function create(): void
    {
        // Only teachers and admins may create courses
        if (!$this->auth->userIsTeacher && !$this->auth->userIsAdmin) {
            stop(403, 'Access denied');
        }

        $name = trim($_POST['name'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $visibility = ($_POST['visibility'] ?? 'private') === 'public' ? 'public' : 'private';
        $status = ($_POST['status'] ?? 'inactive') === 'active' ? 'active' : 'inactive';
    $assignmentId = !empty($_POST['assignmentId']) ? intval($_POST['assignmentId']) : null;

        // Validate
        if ($name === '') {
            $_SESSION['flash_error'] = 'Nimi on kohustuslik.';
            header('Location: ' . BASE_URL . 'courses');
            exit();
        }

        // Ensure unique name
        $exists = Db::getOne("SELECT COUNT(1) FROM courses WHERE name = ?", [$name]);
        if ($exists) {
            $_SESSION['flash_error'] = 'Kursus sellise nimega juba eksisteerib.';
            header('Location: ' . BASE_URL . 'courses');
            exit();
        }

        // If teacher and assignment provided, ensure assignment belongs to one of their subjects
        if (!$this->auth->userIsAdmin && $assignmentId !== null) {
            // ensure the assignment belongs to a subject taught by this teacher
            $valid = Db::getOne("SELECT COUNT(1) FROM assignments a JOIN subjects s ON a.subjectId = s.subjectId WHERE a.assignmentId = ? AND s.teacherId = ?", [$assignmentId, $this->auth->userId]);
            if (!$valid) {
                $_SESSION['flash_error'] = 'Valitud ülesanne pole sinu õppetöös.';
                header('Location: ' . BASE_URL . 'courses');
                exit();
            }
        }

        // Insert using Db helper
        $now = date('Y-m-d H:i:s');
        $data = [
            'name' => $name,
            'description' => $description,
            'visibility' => $visibility,
            'status' => $status,
            'createdBy' => $this->auth->userId,
            'createdAt' => $now,
            'updatedAt' => $now,
        ];

    $courseId = Db::insert('courses', $data);

    // Activity log: course created
    Activity::create(ACTIVITY_CREATE_COURSE ?? 0, $this->auth->userId, $courseId);

        // Redirect to courses index or new course view
        $_SESSION['flash_success'] = 'Kursus loodud.';
        header('Location: ' . BASE_URL . "courses/{$courseId}");
        exit();
    }

    function edit(): void
    {
        $courseId = $this->getId();
        if (!$this->auth->userIsTeacher && !$this->auth->userIsAdmin) {
            $_SESSION['flash_error'] = 'Sul puudub õigus seda kursust muuta.';
            header('Location: ' . BASE_URL . "courses/{$courseId}");
            exit();
        }

        header('Location: ' . BASE_URL . "courses/{$courseId}?tab=exercises");
        exit();
    }
}
