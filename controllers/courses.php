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
        $this->courses = Db::getAll("SELECT * FROM courses ORDER BY sortOrder, id");
    }

    function view(): void
    {
        // Only teachers and admins may view course pages
        if (!$this->auth->userIsTeacher && !$this->auth->userIsAdmin) {
            stop(403, 'Access denied');
        }

        $courseId = $this->getId();
        // Basic course info
        $this->course = Db::getFirst("SELECT * FROM courses WHERE id = ?", [$courseId]);

        if (!$this->course) {
            stop(404, 'Course not found');
        }

        // Visibility: if private, only admin or creator may view
        if ($this->course['visibility'] === 'private') {
            if (!$this->auth->userIsAdmin && intval($this->course['createdBy']) !== intval($this->auth->userId)) {
                stop(403, 'Sul puudub õigus seda kursust vaadata.');
            }
        }

        // Fetch exercises for this course. We'll assume exercises have a courseId column in future; for now
        // map all existing exercises to the default Sisseastumine course (id=1). If courseId exists use it.
        $hasCourseColumn = Db::getOne("SHOW COLUMNS FROM exercises LIKE 'courseId'") ? true : false;

        if ($hasCourseColumn) {
            $this->exercises = Db::getAll("SELECT * FROM exercises WHERE courseId = ? ORDER BY exerciseId", [$courseId]);
        } else {
            // For backwards compatibility: if course id is 1 (Sisseastumine) show all exercises, otherwise empty
            if ($courseId == 1) {
                $this->exercises = Db::getAll("SELECT * FROM exercises ORDER BY exerciseId");
            } else {
                $this->exercises = [];
            }
        }

        // Determine which tab to show (exercises or ranking). Default is exercises.
        $this->tab = isset($_GET['tab']) && $_GET['tab'] === 'ranking' ? 'ranking' : 'exercises';

        // Prepare ranking data so view has $this->users regardless of initial tab,
        // enabling client-side tab switching without a reload.
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
            // Fallback to admin ranking SQL (no course filtering)
            $allUsers = Db::getAll("\n        SELECT\n            u.*,\n            COUNT(DISTINCT ue.exerciseId) AS userExercisesDone,\n            MIN(a.activityLogTimestamp) AS userFirstLogin,\n            ROW_NUMBER() OVER (\n                ORDER BY\n                    COUNT(DISTINCT ue.exerciseId) DESC,\n                    CASE\n                        WHEN u.userTimeTotal IS NOT NULL THEN 0\n                        ELSE 1\n                    END ASC,\n                    u.userTimeTotal ASC,\n                    u.userId ASC\n            ) AS userRank\n        FROM\n            users u\n        LEFT JOIN\n            activityLog a ON u.userId = a.userId AND a.activityId = 1\n        LEFT JOIN\n            userExercisesWithComputedStatus ue ON u.userId = ue.userId AND ue.status = 'completed'\n        WHERE\n            u.userIsAdmin = 0\n            AND u.userIsTeacher = 0\n            AND u.groupId IS NULL\n        GROUP BY\n            u.userId\n        ORDER BY\n            userRank ASC;\n    ");
        }

        // Filter users who have completed at least one task and compute averages (same as admin)
        $this->filteredUsers = array_filter($allUsers, function ($user) {
            return $user['userExercisesDone'] > 0;
        });

        $this->users = $allUsers;  // Keep all users for ranking display

        $totalSolvedTasks = array_sum(array_column($this->filteredUsers, 'userExercisesDone'));
        $userCount = count($this->filteredUsers);
        $this->averageExercisesDone = $userCount > 0 ? $totalSolvedTasks / $userCount : 0;
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
            'sortOrder' => 0,
            'assignment_id' => $assignmentId,
            'createdBy' => $this->auth->userId,
            'createdAt' => $now,
            'updatedAt' => $now,
        ];

    $courseId = Db::insert('courses', $data);

    // Activity log: course created
    Activity::create(ACTIVITY_CREATE_COURSE ?? 0, $this->auth->userId, $courseId, ['assignment_id' => $assignmentId]);

        // Redirect to courses index or new course view
        $_SESSION['flash_success'] = 'Kursus loodud.';
        header('Location: ' . BASE_URL . "courses/{$courseId}");
        exit();
    }
}
