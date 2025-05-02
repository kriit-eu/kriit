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

    .inactive-student {
        opacity: 0.6;
        font-style: italic;
    }

    .narrow-name {
        font-size: 0.6em;
        line-height: 1.1;
        white-space: nowrap;
        text-align: center;
        width: 100%;
        display: block;
    }
</style>
<?php if ($this->auth->userIsAdmin || $this->auth->userIsTeacher): ?>
    <div class="col text-end mb-3 d-flex justify-content-end align-items-center">
        <?php if (!$this->isStudent): ?>
            <div class="form-check form-switch me-3">
                <input class="form-check-input" type="checkbox" id="showAllToggle" <?= $this->showAll ? 'checked' : '' ?>>
                <label class="form-check-label" for="showAllToggle">Näita kõiki</label>
            </div>
        <?php endif; ?>
        <?php if ($this->auth->userIsAdmin): ?>
            <button class="btn btn-primary" onclick="window.location.href='admin/subjects'">Muuda</button>
        <?php endif; ?>
    </div>
<?php endif; ?>
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
                            <?php
                                $isInactive = isset($s['userIsActive']) && !$s['userIsActive'];
                                $hasUngradedAssignments = false;

                                // Check if student has any ungraded assignments
                                if ($isInactive) {
                                    foreach ($group['subjects'] as $subject) {
                                        if (!empty($subject['assignments'])) {
                                            foreach ($subject['assignments'] as $assignment) {
                                                if (isset($assignment['assignmentStatuses'][$s['userId']])) {
                                                    $status = $assignment['assignmentStatuses'][$s['userId']];
                                                    if (empty($status['grade']) || $status['assignmentStatusName'] == 'Kontrollimisel') {
                                                        $hasUngradedAssignments = true;
                                                        break 2;
                                                    }
                                                }
                                            }
                                        }
                                    }
                                }

                                $tooltipText = $s['userName'];
                                if ($isInactive) {
                                    $tooltipText .= $hasUngradedAssignments ? ' (Mitteaktiivne õpilane, hindamata ülesandeid)' : ' (Mitteaktiivne õpilane)';
                                }
                            ?>
                            <th data-bs-toggle="tooltip" title="<?= $tooltipText ?>"
                                class="<?= $isInactive ? 'inactive-student' : '' ?>">
                                <?php
                                    $nameParts = explode(' ', $s['userName']);
                                    $lastName = array_pop($nameParts);
                                    $firstName = implode(' ', $nameParts);
                                ?>
                                <span class="narrow-name"><?= $firstName ?><br><?= $lastName ?></span>
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
                        <?= $a['assignmentDueAt'] ? (new DateTime($a['assignmentDueAt']))->format('d.m.y') : "Pole määratud" ?></span>
                                    <a href="assignments/<?= $a['assignmentId'] ?>"><?= $a['assignmentName'] ?></a>
                                </td>
                                <?php foreach ($group['students'] as $s): ?>
                                    <?php $status = $a['assignmentStatuses'][$s['userId']]; ?>
                                    <?php
                                        $isInactive = isset($s['userIsActive']) && !$s['userIsActive'];
                                        $isUngraded = empty($status['grade']) || $status['assignmentStatusName'] === 'Kontrollimisel';
                                        $inactiveClass = $isInactive ? 'inactive-student' : '';
                                        $inactiveText = $isInactive ? ($isUngraded ? ' (Mitteaktiivne õpilane, hindamata)' : ' (Mitteaktiivne õpilane)') : '';
                                    ?>
                                    <td class="<?= $status['class'] ?> text-center <?= $inactiveClass ?>"
                                        data-bs-toggle="tooltip"
                                        title="<?= $status['tooltipText'] ?><?= $inactiveText ?>"
                                        data-grade="<?= is_numeric($status['grade']) ? intval($status['grade']) : '' ?>"
                                        data-is-student="<?= json_encode($isStudent) ?>"
                                        data-url="assignments/<?= $a['assignmentId'] ?>">
                                        <?= $isStudent ? ($status['assignmentStatusName'] == 'Kontrollimisel' ? 'Kontrollimisel' : ($status['grade'] ?: $status['assignmentStatusName'])) : $status['grade'] ?>
                                    </td>
                                <?php endforeach; ?>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                    <tr>
                        <td colspan="<?= $isStudent ? 2 : count($group['students']) + 2 ?>">&nbsp;</td>
                    </tr>
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

        // Handle the "Show all" toggle
        const showAllToggle = document.getElementById('showAllToggle');
        if (showAllToggle) {
            showAllToggle.addEventListener('change', function() {
                const url = new URL(window.location.href);
                url.searchParams.set('showAll', this.checked ? '1' : '0');
                window.location.href = url.toString();
            });
        }

        // Update badge class for student cells with passing grades
        document.querySelectorAll('td[data-grade]').forEach(studentCell => {
            const grade = parseInt(studentCell.getAttribute('data-grade'), 10);
            const isStudent = JSON.parse(studentCell.getAttribute('data-is-student'));
            const badgeElement = studentCell.closest('tr').querySelector('span[data-days-remaining]');
            if (isStudent && !isNaN(grade) &&
                grade >= 3 &&
                badgeElement.className !== 'badge bg-light text-dark') {
                badgeElement.className = 'badge bg-light text-dark';
            }
        });
    });
</script>
