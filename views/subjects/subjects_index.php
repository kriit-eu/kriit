<style>
    /* Add a light gradient background to the page */
    body {
        background: linear-gradient(135deg, #f5f7fa 0%, #e4e9f2 100%);
        min-height: 100vh;
    }

    /* Ensure container has a transparent background to show the gradient */
    #container {
        background-color: transparent;
    }

    /* Make sure table cells have white background to stand out */
    #subject-table td {
        background-color: white;
    }

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
        font-size: 0.55em;
        line-height: 1;
        white-space: nowrap;
        text-align: center;
        font-family: Arial Narrow, Arial, sans-serif;
        font-stretch: condensed;
        letter-spacing: -0.02em;
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
        height: 100%;
    }

    .narrow-name .lastname {
        margin-top: 1px;  /* Changed from -1px to 1px to add a tiny bit of space */
        display: block;
    }

    #subject-table th.student-name-header {
        padding: 1px 4px;
        font-weight: normal;
        vertical-align: middle;
        height: 36px; /* Set a consistent height */
    }

    /* Simple spacing between subject groups */
    .subject-spacer {
        height: 20px;
        background-color: transparent !important;
        border: none;
    }

    .subject-spacer td {
        border: none !important;
        background-color: transparent !important;
    }

    /* Remove shadow from table to avoid artifacts in spacer rows */
    #subject-table {
        background-color: transparent;
        box-shadow: none;
    }

    /* Ensure the table-responsive container is also transparent */
    .table-responsive {
        background-color: transparent;
    }

    /* Make sure the row between subjects is completely transparent and has no borders */
    tr.subject-spacer,
    tr.subject-spacer td {
        background-color: transparent !important;
        border: none !important;
        box-shadow: none !important;
        outline: none !important;
    }

    /* Remove borders from table cells in the spacer area */
    #subject-table tr.subject-spacer td {
        border-width: 0 !important;
        border-style: none !important;
        border-color: transparent !important;
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
        <div class="table-responsive" style="background-color: transparent;">
            <table id="subject-table" class="table table-bordered" style="background-color: transparent;">

            <?php foreach ($group['subjects'] as $index => $subject): ?>
                    <?php if ($index > 0): ?>
                    </table>
                    <!-- Use a div instead of a table row for spacing -->
                    <div style="height: 20px; width: 100%; background-color: transparent;"></div>
                    <table id="subject-table" class="table table-bordered" style="background-color: transparent;">
                    <?php endif; ?>

                    <tr data-href="subjects/<?= $subject['subjectId'] ?>">
                        <th>
                            <b><?= $subject['subjectName'] ?></b>
                        </th>
                        <?php if (!$isStudent): ?>
                            <?php foreach ($group['students'] as $s): ?>
                                <?php
                                    $isInactive = isset($s['userIsActive']) && !$s['userIsActive'];
                                    $tooltipText = $s['userName'];
                                    $nameParts = explode(' ', $s['userName']);
                                    $lastName = array_pop($nameParts);
                                    $firstName = implode(' ', $nameParts);
                                ?>
                                <th data-bs-toggle="tooltip" title="<?= $tooltipText ?>"
                                    class="student-name-header text-center <?= $isInactive ? 'inactive-student' : '' ?>">
                                    <div class="narrow-name">
                                        <?= $firstName ?>
                                        <span class="lastname"><?= $lastName ?></span>
                                    </div>
                                </th>
                            <?php endforeach; ?>
                        <?php endif; ?>
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
                                        title="<?= $s['userName'] ?>"
                                        data-grade="<?= is_numeric($status['grade']) ? intval($status['grade']) : '' ?>"
                                        data-is-student="<?= json_encode($isStudent) ?>"
                                        data-url="assignments/<?= $a['assignmentId'] ?>">
                                        <?= $isStudent ? ($status['assignmentStatusName'] == 'Kontrollimisel' ? 'Kontrollimisel' : ($status['grade'] ?: $status['assignmentStatusName'])) : $status['grade'] ?>
                                    </td>
                                <?php endforeach; ?>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                <?php endforeach; ?>
            </table>
            <!-- Ensure we close the last table properly -->
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
