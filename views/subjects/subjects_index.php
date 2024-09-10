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
                    <?= $isStudent ? "<th>Staatus / Hinne</th>" : "" ?>
                    <?php if (!$isStudent): ?>
                        <?php foreach ($group['students'] as $s): ?>
                            <th data-bs-toggle="tooltip" title="<?= $s['userName'] ?>">
                                <?= $s['initials'] ?>
                            </th>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($group['subjects'] as $subject): ?>
                    <tr data-href="subjects/<?= $subject['subjectId'] ?>">
                        <td>
                            <b><?= $subject['subjectName'] ?></b>
                        </td>
                        <td colspan="<?= count($group['students']) + 1 ?>" class="text-end">
                            <b><?= $subject['teacherName'] ?></b>
                        </td>
                    </tr>
                    <?php if (!empty($subject['assignments'])): ?>
                        <?php foreach ($subject['assignments'] as $a): ?>
                            <tr>
                                <td colspan="1">
                                    <span class="badge <?= $a['badgeClass'] ?>"
                                          data-days-remaining="<?= $a['daysRemaining'] ?>"
                                          data-is-student="<?= json_encode($isStudent) ?>">
                        <?= (new DateTime($a['assignmentDueAt']))->format('d.m.y') ?></span>
                                    <a href="assignments/<?= $a['assignmentId'] ?>"><?= $a['assignmentName'] ?></a>
                                </td>
                                <?php foreach ($group['students'] as $s): ?>
                                    <?php $status = $a['assignmentStatuses'][$s['userId']]; ?>
                                    <td class="<?= $status['class'] ?> text-center"
                                        data-bs-toggle="tooltip"
                                        title="<?= $status['tooltipText'] ?>"
                                        data-grade="<?= is_numeric($status['grade']) ? intval($status['grade']) : '' ?>"
                                        data-is-student="<?= json_encode($isStudent) ?>"
                                        data-url="assignments/<?= $a['assignmentId'] ?>">
                                        <?= $isStudent ? ($status['grade'] ?: $status['assignmentStatusName']) : $status['grade'] ?>
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
    document.addEventListener('DOMContentLoaded', function () {
        // Initialize tooltips
        [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
            .map(el => new bootstrap.Tooltip(el));

        // Add click event to cells with data-url
        document.querySelectorAll('td[data-url]').forEach(cell => {
            cell.style.cursor = 'pointer';
            cell.addEventListener('click', () => {
                const url = cell.getAttribute('data-url');
                if (url) window.location.href = url;
            });
        });

        // Update badge class for student cells with passing grades
        document.querySelectorAll('td[data-grade]').forEach(studentCell => {
            const grade = parseInt(studentCell.getAttribute('data-grade'), 10);
            const isStudent = JSON.parse(studentCell.getAttribute('data-is-student'));

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