<?php
$statusClassMap = [
        'Ootab alustamist' => 'awaiting-start',
        'Töös' => 'in-progress',
        'Ootab esitamist' => 'awaiting-submission',
        'Ootab ülevaatamist' => 'awaiting-review',
        'Vajab parandamist' => 'needs-revision',
        'Ootab uuesti ülevaatamist' => 'awaiting-re-review',
        'Hinnatud' => 'graded',
        'Tähtaeg möödas' => 'deadline-missed'
];

?>

<style>
    #subject-table th {
        background-color: #f2f2f2;
    }

    .bad-grade {
        background-color: rgb(255, 180, 176) !important;
    }

    .awaiting-start {
        background-color: #f0f0f0 !important;
    }

    .in-progress {
        background-color: #fff8b3 !important;
    }

    .awaiting-submission {
        background-color: #cce5ff !important;
    }

    .awaiting-review {
        background-color: #c9f1fd !important;
    }

    .needs-revision {
        background-color: #ffcccc !important;
    }

    .awaiting-re-review {
        background-color: #ffe6cc !important;
    }

    .graded {
        background-color: #d4edda !important;
    }

    .deadline-missed {
        background-color: #f5c6cb !important;
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
                        <th>Tegevused</th>
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

                        <?php if (!$isStudent): ?>
                            <td>
                                <a href="grades/<?= $subject['subjectId'] ?>">Hinded</a>
                            </td>
                        <?php else: ?>
                            <td colspan="<?= (count($group['students']) + 1) ?>"></td>
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
                                <td colspan="<?= ($auth->userIsAdmin || $isStudent) ? 2 : 1 ?>"
                                    style="padding-left: 20px;">
                                    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp; <span
                                            class="<?= $badgeClass ?>"><?= $formattedDueDate ?></span> <a
                                            href="assignments/<?= $assignment['assignmentId'] ?>"><?= $assignment['assignmentName'] ?></a>
                                </td>

                                <?php if (!$isStudent): ?>
                                    <td></td>
                                <?php endif; ?>

                                <?php foreach ($group['students'] as $student): ?>
                                    <?php
                                    $status = $assignment['assignmentStatuses'][$student['userId']]['assignmentStatusName'] ?? '';
                                    $grade = $assignment['assignmentStatuses'][$student['userId']]['grade'] ?? '';
                                    $class = ($grade == '2' || $grade == 'MA') ? 'bad-grade' : ($statusClassMap[$status] ?? '');
                                    ?>
                                    <td class="<?= $class ?> text-center" <?= !$isStudent || $class == 'bad-grade' ? 'data-bs-toggle="tooltip" title="' . $status . '"' : '' ?>>
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
</script>
