<?php
$isStudent = $auth->groupId && !$auth->userIsAdmin && !$auth->userIsTeacher;
$isTeacher = !$auth->userIsAdmin && $auth->userIsTeacher;
?>
<style>
    #subject-table th {
        background-color: #f2f2f2;
    }
</style>

<div class="row">
    <?php foreach ($groups as $group): ?>
        <h1><?= $group['groupName'] ?></h1>
        <div class="table-responsive">
            <table id="subject-table" class="table table-bordered">
                <thead>
                <tr>
                    <th>Aine</th>
                    <?php if ($auth->userIsAdmin || $isStudent): ?>
                        <th>Õpetaja</th>
                    <?php endif; ?>
                    <?php if ($isStudent): ?>
                        <th>Staatus / Hinne</th>
                    <?php else: ?>
                        <th>Tegevused</th>
                    <?php endif; ?>

                    <?php foreach ($group['students'] as $student): ?>
                        <th data-bs-toggle="tooltip"
                            title="<?= $student['userName'] ?>"><?= $student['userName'][0] . $student['userName'][strpos($student['userName'], ' ') + 1] ?></th>
                    <?php endforeach; ?>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($group['subjects'] as $subject): ?>
                    <tr data-href="subjects/<?= $subject['subjectId'] ?>">
                        <td><b><?= $subject['subjectName'] ?></b></td>
                        <?php if ($auth->userIsAdmin || $isStudent): ?>
                            <td><b><?= $subject['teacherName'] ?></b></td>
                        <?php endif; ?>

                        <?php if (!$isStudent): ?>
                            <td>
                                <a href="grades/<?= $subject['subjectId'] ?>">Hinded</a>
                            </td>
                        <?php else: ?>
                            <td colspan="<?= count($group['students']) + 1 ?>"></td>
                        <?php endif; ?>
                    </tr>

                    <?php if (!empty($subject['assignments'])): ?>
                        <?php foreach ($subject['assignments'] as $assignment): ?>
                            <tr>
                                <td colspan="<?= ($auth->userIsAdmin || $isStudent) ? 2 : 1 ?>" style="padding-left: 20px;">
                                    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp; [<?= $assignment['assignmentDueAt'] ?>] <a
                                            href="assignments/<?= $assignment['assignmentId'] ?>"><?= $assignment['assignmentName'] ?></a>
                                </td>

                                <?php if (!$isStudent): ?>
                                    <td></td>
                                <?php endif; ?>

                                <?php foreach ($group['students'] as $student): ?>
                                    <td>
                                        <?php if (isset($assignment['assignmentStatuses'][$student['userId']])): ?>
                                            <?= $assignment['assignmentStatuses'][$student['userId']]['grade'] ?>
                                        <?php endif; ?>
                                    </td>
                                <?php endforeach; ?>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endforeach; ?>
</div>

<script>
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    const tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
</script>
