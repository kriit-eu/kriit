
<?php
// Dispatcher: include the correct view for student or admin/teacher
if (!empty($this->isStudent) && $this->isStudent) {
    include __DIR__ . '/subjects_student.php';
} else {
    include __DIR__ . '/subjects_admin.php';
}
?>
