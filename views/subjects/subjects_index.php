<?php
$statusClassMap = [
        'Esitamata' => $isStudent ? 'yellow-cell' : '',
        'Ülevaatamata' => $this->auth->userIsTeacher ? 'red-cell' : '',
];

?>

<style>
    #subject-table th {
        background-color: #f2f2f2;
    }

    .red-cell {
        background-color: rgb(255, 180, 176) !important;
    }

    .yellow-cell {
        background-color: #fff8b3 !important;
    }

    .text-center {
        text-align: center;
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
                    <?php endif; ?>

                    <?php if (!$isStudent): ?>
                        <?php foreach ($group['students'] as $student): ?>
                            <th data-bs-toggle="tooltip" title="<?= $student['userName'] ?>">
                                <?= mb_substr($student['userName'], 0, 1) . mb_substr($student['userName'], mb_strrpos($student['userName'], ' ') + 1, 1) ?>
                            </th>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($group['subjects'] as $subject): ?>
                    <tr data-href="subjects/<?= $subject['subjectId'] ?>">
                        <td><b><?= $subject['subjectName'] ?></b></td>
                        <?php if ($auth->userIsAdmin || $isStudent): ?>
                            <td><b><?= $subject['teacherName'] ?></b></td>
                        <?php endif; ?>
                    </tr>

                    <?php if (!empty($subject['assignments'])): ?>
                        <?php foreach ($subject['assignments'] as $assignment): ?>
                            <tr>
                                <?php
                                $dueDate = new DateTime($assignment['assignmentDueAt']);
                                $now = new DateTime();
                                $interval = $now->diff($dueDate);
                                $daysRemaining = (int)$interval->format('%r%a');

                                if ($daysRemaining > 3) {
                                    $badgeClass = 'badge bg-light text-dark';
                                } elseif ($daysRemaining >= 0) {
                                    $badgeClass = 'badge bg-warning text-dark';
                                } else {
                                    $badgeClass = 'badge bg-danger';
                                }

                                $formattedDueDate = $dueDate->format('d.m.y');
                                ?>
                                <td colspan="<?= ($auth->userIsAdmin || $isStudent) ? 2 : 1 ?>">
                                    <span class="badge <?= $badgeClass ?>"
                                          data-days-remaining="<?= $daysRemaining ?>"
                                          data-is-student="<?= $isStudent ? 'true' : 'false' ?>">
                                        <?= $formattedDueDate ?>
                                    </span>
                                    <a href="assignments/<?= $assignment['assignmentId'] ?>"><?= $assignment['assignmentName'] ?></a>
                                </td>


                                <?php foreach ($group['students'] as $student): ?>
                                    <?php
                                    $status = $assignment['assignmentStatuses'][$student['userId']]['assignmentStatusName'] ?? '';
                                    $class = $statusClassMap[$status] ?? '';

                                    $grade = $assignment['assignmentStatuses'][$student['userId']]['grade'] ?? '';
                                    if ($daysRemaining < 0) {
                                        $class = (($isStudent && $status == 'Esitamata') || ($isStudent && ($grade == 'MA' || (is_numeric($grade) && intval($grade) < 3))) || ($isTeacher && $status !== 'Hinnatud')) ? 'red-cell' : '';
                                    } else {
                                        $class = ($grade == 'MA' || (is_numeric($grade) && intval($grade) < 3)) ? 'red-cell' : ($statusClassMap[$status] ?? '');
                                    }

                                    $linkText = '';
                                    $actionLink = '';
                                    if ($status == 'Esitamata') {
                                        $linkText =  $isStudent ? 'Esita' : 'Hinda';

                                    } elseif ($status == 'Ülevaatamata') {
                                        $linkText = $isStudent ? 'Muuda' : 'Hinda';
                                    } elseif ($status == 'Hinnatud' && ($grade == 'MA' || (is_numeric($grade) && intval($grade) < 3))) {
                                        $linkText = $isStudent? 'Esita uuesti': 'Muuda hinnet';
                                    }
                                    $tooltipText = $isStudent ? $linkText : ($status ? "($status) $linkText" : 'Ootab alustamist');
                                    ?>
                                    <td class="<?= $class ?> text-center"  data-bs-toggle="tooltip" title="<?=$tooltipText ?>"
                                        data-grade="<?= is_numeric($grade) ? intval($grade) : '' ?>"
                                        data-is-student="<?= $isStudent ? 'true' : 'false' ?>"
                                        data-url="assignments/<?= $assignment['assignmentId'] ?>"
                                    >
                                        <?= $isStudent ? ($grade ?: $status ?: 'Ootab alustamist') : ($grade ?: '') ?>
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


    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('td[data-url]').forEach(function (cell) {
            cell.style.cursor = 'pointer';
            cell.addEventListener('click', function () {
                const url = cell.getAttribute('data-url');
                if (url) {
                    window.location.href = url;
                }
            });
        });
    });


    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('td[data-grade]').forEach(function (studentCell) {
            const grade = parseInt(studentCell.getAttribute('data-grade'), 10);
            const isStudent = studentCell.getAttribute('data-is-student') === 'true';

            if (isStudent && !isNaN(grade) && grade >= 3) {
                const badgeElement = studentCell.closest('tr').querySelector('span[data-days-remaining]');
                const daysRemaining = parseInt(badgeElement.getAttribute('data-days-remaining'), 10);

                if (daysRemaining < 0) {
                    badgeElement.className = 'badge bg-light text-dark';
                }
            }
        });
    });
</script>
