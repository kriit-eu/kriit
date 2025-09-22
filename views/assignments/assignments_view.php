<?php
// Dispatcher view: include role-specific view for assignments
// By default the application expects `views/assignments/assignments_view.php` to exist.
// We include the teacher view for teachers/admins and the student view for students.

// Ensure $this->auth is available (set by Controller)
if (isset($this->auth) && ($this->auth->userIsTeacher || $this->auth->userIsAdmin)) {
    // Teacher/admin view (canonical file)
    require __DIR__ . '/assignments_view_teacher.php';
} else {
    // Student view (canonical file)
    require __DIR__ . '/assignments_view_student.php';
}

