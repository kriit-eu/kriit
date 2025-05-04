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

    /* Add subtle shadow to tables for better contrast with background */
    #subject-table {
        background-color: transparent;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.07);
    }

    /* Ensure the table-responsive container is also transparent */
    .table-responsive {
        background-color: transparent;
    }

    /* Set fixed width for all grade cells */
    .student-name-header,
    #subject-table td.text-center:not(:first-child) {
        width: 40px !important;
        min-width: 40px !important;
        max-width: 40px !important;
        text-align: center;
        vertical-align: middle; /* Center grades vertically */
        padding: 4px 2px; /* Reduce padding to ensure content fits */
        box-sizing: border-box; /* Include padding in width calculation */
    }

    /* This rule has been moved below */

    /* Set width for the ID column (first column) */
    #subject-table td:first-child,
    #subject-table th:first-child {
        width: 60px;
        min-width: 60px;
        max-width: 60px;
        text-align: center;
        vertical-align: middle;
    }

    /* Make the assignment name column flexible to fit container */
    #subject-table td:nth-child(<?= $isStudent ? '1' : '2' ?>),
    #subject-table th:nth-child(<?= $isStudent ? '1' : '2' ?>) {
        width: auto;
        min-width: 160px;
        max-width: none;
        flex-grow: 1;
    }

    /* Ensure table layout is fixed for consistent column widths but allows scaling */
    #subject-table {
        table-layout: fixed !important;
        border: 1px solid #d8d8d8 !important; /* More subtle border color */
        border-collapse: collapse !important; /* Ensure borders collapse properly */
        width: 100% !important; /* Use full width */
    }

    /* For students, make tables use full container width */
    .student-view #subject-table {
        width: 100% !important; /* Use full container width */
        table-layout: fixed !important; /* Ensure fixed layout for proper column distribution */
        border-collapse: collapse !important;
    }

    /* For student view, ensure the table has only two columns with proper widths */
    .student-view #subject-table tr {
        display: table-row;
        width: 100%;
    }

    /* For students, ensure assignment name cells are left-aligned */
    .student-view #subject-table td {
        text-align: left !important;
    }

    /* For teachers/admins, make the table fill the container */
    .teacher-view #subject-table {
        width: 100% !important;
    }

    /* More subtle cell borders */
    #subject-table td,
    #subject-table th {
        border-color: #d8d8d8 !important;
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

    /* Style for assignment entry date */
    .entry-date {
        color: #664d03;
        font-size: 0.85em;
        background-color: #fff3cd;
        padding: 2px 5px;
        border-radius: 3px;
        margin-right: 5px;
        text-decoration: none;
    }

    /* Style for assignment name to make it stand out */
    #subject-table td a {
        display: inline;
        line-height: 1.4;
        text-decoration: none !important; /* Force no underline for all views */
        font-weight: 500;
    }

    /* Add padding to assignment name cell and make it scale */
    #subject-table td:nth-child(2) {
        padding: 8px 12px;
        width: auto;
        white-space: nowrap; /* Prevent wrapping for better appearance */
        overflow: hidden;
        text-overflow: ellipsis;
        text-align: left;
    }

    /* Special styling for student view assignment cells */
    .student-view #subject-table td:first-child {
        padding: 8px 12px;
        white-space: nowrap; /* Prevent wrapping for better appearance */
        overflow: hidden;
        text-overflow: ellipsis;
        text-align: left;
        /* Let this cell take up all remaining space */
        width: calc(100% - 120px) !important; /* Subtract the grade cell width */
    }

    /* Override text-center for grade cells in student view */
    .student-view #subject-table td.text-center {
        text-align: center !important;
        width: 120px !important;
        min-width: 120px !important;
        max-width: 120px !important;
        padding: 4px 8px;
        box-sizing: border-box !important;
    }

    /* Style for the link to make it more readable */
    #subject-table td a:hover {
        text-decoration: underline;
        color: #0056b3;
    }

    /* Style for ID badge in the ID column */
    .id-badge {
        background-color: #e9ecef;
        padding: 2px 6px;
        border-radius: 3px;
        font-size: 0.85em;
        color: #495057;
    }

    /* Ensure grade values are centered in their cells */
    #subject-table td[data-grade] {
        text-align: center !important;
        justify-content: center !important;
        align-items: center !important;
        display: table-cell !important;
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
<div class="row <?= $isStudent ? 'student-view' : 'teacher-view' ?>">
    <?php foreach ($groups as $group): ?>
        <h1><?= $group['groupName'] ?></h1>
        <div class="table-responsive" style="background-color: transparent;">
            <table id="subject-table" class="table table-bordered" style="background-color: transparent; table-layout: fixed !important;">

            <?php foreach ($group['subjects'] as $index => $subject): ?>
                    <?php if ($index > 0): ?>
                    </table>
                    <!-- Use a div instead of a table row for spacing -->
                    <div style="height: 20px; width: 100%; background-color: transparent;"></div>
                    <table id="subject-table" class="table table-bordered" style="background-color: transparent; table-layout: fixed !important;">
                    <?php endif; ?>

                    <tr data-href="subjects/<?= $subject['subjectId'] ?>">
                        <?php if (!$isStudent): ?>
                        <th class="text-center">
                            <b>ID</b>
                        </th>
                        <?php endif; ?>
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
                                <?php if (!$isStudent): ?>
                                <td class="text-center">
                                    <span class="id-badge"><?= $a['assignmentId'] ?></span>
                                </td>
                                <?php endif; ?>
                                <td colspan="1">
                                    <?php
                                    // Determine whether to show the due date badge
                                    $showDueDate = true;
                                    $badgeClass = $a['badgeClass'];

                                    if ($isStudent) {
                                        // For students with positive grades, don't show the due date
                                        $currentUserId = $this->auth->userId;
                                        if (isset($a['assignmentStatuses'][$currentUserId])) {
                                            $studentStatus = $a['assignmentStatuses'][$currentUserId];
                                            $grade = $studentStatus['grade'] ?? '';
                                            // A positive grade is 3 or higher, or not 'MA'
                                            $hasPositiveGrade = is_numeric($grade) && intval($grade) >= 3 || ($grade !== '' && $grade !== 'MA');
                                            if ($hasPositiveGrade) {
                                                $showDueDate = false;
                                            }
                                        }
                                    } else {
                                        // For teachers, only show the badge if the due date is not set
                                        // (and make it red)
                                        if (!empty($a['assignmentDueAt'])) {
                                            $showDueDate = false;
                                        } else {
                                            $badgeClass = 'badge bg-danger'; // Red badge for missing due date
                                        }
                                    }

                                    if ($showDueDate): ?>
                                        <span class="badge <?= $badgeClass ?>"
                                              data-days-remaining="<?= $a['daysRemaining'] ?>"
                                              data-is-student="<?= json_encode($isStudent) ?>">
                                            <?= $a['assignmentDueAt'] ? (new DateTime($a['assignmentDueAt']))->format('d.m.y') : "Pole määratud" ?>
                                        </span>
                                    <?php endif; ?>
                                    <a href="assignments/<?= $a['assignmentId'] ?>">
                                        <?php if (!empty($a['assignmentEntryDateFormatted'])): ?>
                                            <span class="entry-date"><?= $a['assignmentEntryDateFormatted'] ?></span>
                                        <?php endif; ?>
                                        <?= $a['assignmentName'] ?>
                                    </a>
                                </td>
                                <?php if (!$isStudent): ?>
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
                                            data-bs-placement="bottom"
                                            data-bs-html="true"
                                            title="<?= nl2br(htmlspecialchars($status['tooltipText'])) ?>"
                                            data-grade="<?= is_numeric($status['grade']) ? intval($status['grade']) : '' ?>"
                                            data-is-student="<?= json_encode($isStudent) ?>"
                                            data-url="assignments/<?= $a['assignmentId'] ?>">
                                            <?= $status['grade'] ?>
                                        </td>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <?php
                                        // For students, show only their own grade
                                        $currentUserId = $this->auth->userId;
                                        if (isset($a['assignmentStatuses'][$currentUserId])) {
                                            $status = $a['assignmentStatuses'][$currentUserId];
                                            $isUngraded = empty($status['grade']) || $status['assignmentStatusName'] === 'Kontrollimisel';
                                        } else {
                                            $status = ['class' => '', 'assignmentStatusName' => 'Esitamata', 'grade' => '', 'tooltipText' => ''];
                                            $isUngraded = true;
                                        }
                                    ?>
                                    <td class="<?= $status['class'] ?> text-center"
                                        data-bs-toggle="tooltip"
                                        data-bs-placement="bottom"
                                        data-bs-html="true"
                                        title="<?= nl2br(htmlspecialchars($status['tooltipText'])) ?>"
                                        data-grade="<?= is_numeric($status['grade'] ?? '') ? intval($status['grade']) : '' ?>"
                                        data-is-student="true"
                                        data-url="assignments/<?= $a['assignmentId'] ?>"
                                        style="width: 120px; min-width: 120px; max-width: 120px;">
                                        <?= $status['assignmentStatusName'] == 'Kontrollimisel' ? 'Kontrollimisel' : ($status['grade'] ?: $status['assignmentStatusName']) ?>
                                    </td>
                                <?php endif; ?>
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
            // Skip if there's no badge element (it might be hidden)
            if (!badgeElement) return;

            // For students with passing grades, update badge class if needed
            if (isStudent && !isNaN(grade) && grade >= 3 &&
                badgeElement.className !== 'badge bg-light text-dark') {
                badgeElement.className = 'badge bg-light text-dark';
            }

            // Note: For teachers, we don't need to modify badges here as it's handled server-side
        });
    });
</script>
