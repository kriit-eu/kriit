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

    /* No pulsating animation - using static red color intensity based on days passed */
    /* Color and background-color are set directly in JavaScript */

    .days-passed {
        font-size: 0.55em;
    }

    .yellow-cell {
        background-color: #fff8b3 !important;
    }

    /* Student summary table styling */
    .student-summary-table {
        border-collapse: collapse !important;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1) !important;
    }

    .student-summary-table th {
        background-color: #f2f2f2 !important;
        border: 1px solid #dee2e6 !important;
        padding: 8px 12px !important;
    }

    .student-summary-table td {
        background-color: white !important;
        border: 1px solid #dee2e6 !important;
        padding: 8px 12px !important;
    }

    .student-summary-table th:hover {
        background-color: #e9ecef !important;
    }

    /* Student row selection styling */
    .student-row.selected {
        background-color: #0d6efd !important;
        color: white !important;
    }

    .student-row.selected td {
        background-color: #0d6efd !important;
        color: white !important;
    }

    .text-center {
        text-align: center;
    }

    .inactive-student {
        opacity: 0.6;
        font-style: italic;
    }

    .deleted-student {
        opacity: 0.4;
        text-decoration: line-through;
        color: #888;
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

    /* Style for subject header in student view */
    .student-view #subject-table th[colspan="2"] {
        text-align: left;
        padding: 8px 12px;
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

    /* Style for due date badge */
    .due-date-badge {
        float: right;
        margin-left: 8px;
    }

    /* Container for assignment name and badges */
    .assignment-container {
        display: flex;
        justify-content: space-between;
        align-items: center;
        width: 100%;
        position: relative;
    }

    .assignment-info {
        flex-grow: 1;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        padding-right: 10px; /* Add space for the due date badge */
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
    #subject-table td a:hover,
    #subject-table th a:hover {
        text-decoration: underline;
        color: #0056b3;
    }

    /* Style for subject name links */
    #subject-table th a {
        color: inherit;
        text-decoration: none;
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

        <?php if (!$isStudent && !empty($group['subjects'])): ?>
            <!-- Student Summary Table -->
            <div class="mb-4">
                <h5>Õpilaste kokkuvõte</h5>
                <table class="table table-bordered student-summary-table" data-group="<?= htmlspecialchars($group['groupName']) ?>" style="width: auto; background-color: white;">
                    <thead>
                        <tr>
                            <th style="cursor: pointer; background-color: #f2f2f2;" onclick="sortStudentTableByElement(this, 'name')">
                                <b>Õpilane</b>
                                <i class="fas fa-sort"></i>
                            </th>
                            <th style="cursor: pointer; background-color: #f2f2f2; text-align: center;" onclick="sortStudentTableByElement(this, 'pending')">
                                <b>Võlad</b>
                                <i class="fas fa-sort"></i>
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($group['students'] as $s): ?>
                            <?php
                                $pendingCount = $group['pendingGrades'][$s['userId']] ?? 0;
                                $isInactive = isset($s['userIsActive']) && !$s['userIsActive'];
                                $isDeleted = isset($s['userDeleted']) && $s['userDeleted'] == 1;
                                $statusText = '';
                                $cssClass = '';
                                if ($isDeleted) {
                                    $statusText = ' (kustutatud)';
                                    $cssClass = 'deleted-student';
                                } elseif ($isInactive) {
                                    $statusText = ' (mitteaktiivne)';
                                    $cssClass = 'inactive-student';
                                }
                            ?>
                            <tr class="<?= $cssClass ?> student-row" data-student-id="<?= $s['userId'] ?>" data-student-name="<?= htmlspecialchars($s['userName']) ?>" style="cursor: pointer;" onclick="toggleStudentFilter(this)">
                                <td data-sort-value="<?= htmlspecialchars($s['userName']) ?>" style="background-color: white;">
                                    <?= htmlspecialchars($s['userName']) ?><?= $statusText ?>
                                </td>
                                <td data-sort-value="<?= $pendingCount ?>" class="text-center" style="background-color: white; color: <?= $pendingCount > 0 ? '#dc3545' : '#28a745' ?>; font-weight: bold;">
                                    <?= $pendingCount ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>

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
                        <th <?= $isStudent ? 'colspan="2"' : '' ?>>
                            <b>
                                <?php if (!empty($subject['subjectExternalId'])): ?>
                                    <a href="https://tahvel.edu.ee/#/journal/<?= $subject['subjectExternalId'] ?>/edit" target="_blank">
                                        <?= $subject['subjectName'] ?>
                                    </a>
                                <?php else: ?>
                                    <?= $subject['subjectName'] ?>
                                <?php endif; ?>
                            </b>
                        </th>
                        <?php if (!$isStudent): ?>
                            <?php foreach ($group['students'] as $s): ?>
                                <?php
                                    $isInactive = isset($s['userIsActive']) && !$s['userIsActive'];
                                    $isDeleted = isset($s['userDeleted']) && $s['userDeleted'] == 1;
                                    $tooltipText = $s['userName'] . ($isDeleted ? ' (kustutatud)' : ($isInactive ? ' (mitteaktiivne)' : ''));
                                    $nameParts = explode(' ', $s['userName']);
                                    $lastName = array_pop($nameParts);
                                    $firstName = implode(' ', $nameParts);
                                    $cssClass = '';
                                    if ($isDeleted) {
                                        $cssClass = 'deleted-student';
                                    } elseif ($isInactive) {
                                        $cssClass = 'inactive-student';
                                    }
                                ?>
                                <th data-bs-toggle="tooltip" title="<?= $tooltipText ?>"
                                    class="student-name-header text-center <?= $cssClass ?>">
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
                                    <div class="assignment-container">
                                        <div class="assignment-info">
                                            <a href="assignments/<?= $a['assignmentId'] ?>?group=<?= urlencode($group['groupName']) ?>">
                                                <?php if (!empty($a['assignmentEntryDateFormatted'])): ?>
                                                    <span class="entry-date"><?= $a['assignmentEntryDateFormatted'] ?></span>
                                                <?php endif; ?>
                                                <?= $a['assignmentName'] ?>
                                            </a>
                                        </div>

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
                                            <span class="badge <?= $badgeClass ?> due-date-badge"
                                                  data-days-remaining="<?= $a['daysRemaining'] ?>"
                                                  data-is-student="<?= json_encode($isStudent) ?>">
                                                <?= $a['assignmentDueAt'] ? (new DateTime($a['assignmentDueAt']))->format('d.m.y') : "Pole tähtaega" ?>
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <?php if (!$isStudent): ?>
                                    <?php foreach ($group['students'] as $s): ?>
                                        <?php $status = $a['assignmentStatuses'][$s['userId']]; ?>
                                        <?php
                                            $isInactive = isset($s['userIsActive']) && !$s['userIsActive'];
                                            $isDeleted = isset($s['userDeleted']) && $s['userDeleted'] == 1;
                                            $isUngraded = empty($status['grade']) || $status['assignmentStatusName'] === 'Kontrollimisel';

                                            $cssClass = '';
                                            if ($isDeleted) {
                                                $cssClass = 'deleted-student';
                                                $statusTooltip = 'Kustutatud õpilane';
                                            } elseif ($isInactive) {
                                                $cssClass = 'inactive-student';
                                                $statusTooltip = 'Mitteaktiivne õpilane';
                                            }

                                            $statusTooltipText = $isUngraded ?
                                                ($cssClass ? "$statusTooltip, hindamata" : "Hindamata") :
                                                ($cssClass ? $statusTooltip : "");
                                        ?>
                                        <td class="<?= $status['class'] ?> text-center <?= $cssClass ?> <?= ($status['class'] === 'red-cell' && isset($status['daysPassed']) && $status['daysPassed'] > 0) ? 'red-cell-intensity' : '' ?>"
                                            data-bs-toggle="tooltip"
                                            data-bs-placement="bottom"
                                            data-bs-html="true"
                                            title="<?= nl2br(htmlspecialchars(($statusTooltipText ? $statusTooltipText . "\n" : '') . $status['tooltipText'])) ?>"
                                            data-grade="<?= is_numeric($status['grade']) ? intval($status['grade']) : ($status['grade'] ?: '') ?>"
                                            data-student-id="<?= $s['userId'] ?>"
                                            data-is-student="<?= json_encode($this->isStudent) ?>"
                                            data-days-passed="<?= $status['daysPassed'] ?? 0 ?>"
                                            data-url="assignments/<?= $a['assignmentId'] ?>?group=<?= urlencode($group['groupName']) ?>">
                                            <?php if ($status['class'] === 'red-cell' && isset($status['daysPassed'])): ?>
                                                <span class="days-passed"><?= $status['daysPassed'] ?>p</span>
                                            <?php else: ?>
                                                <?= $status['grade'] ?>
                                            <?php endif; ?>
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
                                        data-url="assignments/<?= $a['assignmentId'] ?>?group=<?= urlencode($group['groupName']) ?>"
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
    // Student table sorting functionality - make functions global
    var sortStates = {}; // Track sort state for each group

    // Make functions global so onclick handlers can access them
    window.sortStudentTableByElement = function(element, column) {
        console.log('sortStudentTableByElement called with column:', column);
        // Get the group name from the table element
        const table = element.closest('table');
        const groupName = table.getAttribute('data-group');
        console.log('Found group name:', groupName);
        window.sortStudentTable(groupName, column);
    };

    window.sortStudentTable = function(groupName, column) {
        console.log('sortStudentTable called for group:', groupName, 'column:', column);
        // Find table using a more robust selector
        const tables = document.querySelectorAll('table.student-summary-table');
        let table = null;

        for (let t of tables) {
            if (t.getAttribute('data-group') === groupName) {
                table = t;
                break;
            }
        }

        if (!table) {
            console.log('Table not found for group:', groupName);
            return;
        }

        const tbody = table.querySelector('tbody');
        const rows = Array.from(tbody.querySelectorAll('tr'));

        if (rows.length === 0) {
            console.log('No rows found in table');
            return;
        }

        // Initialize sort state if not exists
        if (!sortStates[groupName]) {
            sortStates[groupName] = { name: 'none', pending: 'none' };
        }

        // Determine new sort direction
        let newDirection;
        if (sortStates[groupName][column] === 'none' || sortStates[groupName][column] === 'desc') {
            newDirection = 'asc';
        } else {
            newDirection = 'desc';
        }

        // Reset all sort states for this group
        sortStates[groupName] = { name: 'none', pending: 'none' };
        sortStates[groupName][column] = newDirection;

        // Update sort icons
        updateSortIcons(groupName);

        // Sort rows
        rows.sort((a, b) => {
            let aValue, bValue;

            if (column === 'name') {
                const aCell = a.querySelector('td[data-sort-value]');
                const bCell = b.querySelector('td[data-sort-value]');
                aValue = aCell ? aCell.getAttribute('data-sort-value').toLowerCase() : '';
                bValue = bCell ? bCell.getAttribute('data-sort-value').toLowerCase() : '';
            } else if (column === 'pending') {
                const aCells = a.querySelectorAll('td[data-sort-value]');
                const bCells = b.querySelectorAll('td[data-sort-value]');
                aValue = aCells.length > 1 ? parseInt(aCells[1].getAttribute('data-sort-value')) : 0;
                bValue = bCells.length > 1 ? parseInt(bCells[1].getAttribute('data-sort-value')) : 0;
            }

            if (newDirection === 'asc') {
                return aValue > bValue ? 1 : -1;
            } else {
                return aValue < bValue ? 1 : -1;
            }
        });

        // Re-append sorted rows
        rows.forEach(row => tbody.appendChild(row));

        console.log(`Sorted ${rows.length} rows by ${column} in ${newDirection} order`);
    };

    // Student filtering functionality
    var selectedStudentId = null;

    window.toggleStudentFilter = function(rowElement) {
        const studentId = rowElement.getAttribute('data-student-id');
        const studentName = rowElement.getAttribute('data-student-name');

        console.log('Toggling filter for student:', studentName, 'ID:', studentId);

        // Clear previous selection styling
        document.querySelectorAll('.student-row').forEach(row => {
            row.classList.remove('selected');
        });

        // If clicking the same student, remove filter
        if (selectedStudentId === studentId) {
            selectedStudentId = null;
            showAllAssignments();
            console.log('Filter removed');
        } else {
            // Select new student and apply filter
            selectedStudentId = studentId;

            // Add selected class for styling
            rowElement.classList.add('selected');

            // Apply filter to show only problematic assignments for this student
            filterAssignmentsByStudent(studentId);
            console.log('Filter applied for student:', studentName);
        }
    };

    function filterAssignmentsByStudent(studentId) {
        console.log('Filtering assignments for student ID:', studentId);

        // Get all assignment tables (the main grade tables)
        const assignmentTables = document.querySelectorAll('#subject-table');

        assignmentTables.forEach(table => {
            const assignmentRows = table.querySelectorAll('tr');
            const subjectVisibility = new Map(); // Track which subjects have visible assignments

            // First pass: process assignment rows and track subject visibility
            assignmentRows.forEach(row => {
                // Skip student summary table rows
                if (row.closest('.student-summary-table')) {
                    return;
                }

                // Check if this is a subject header row (has th elements)
                const isSubjectHeader = row.querySelector('th');

                if (isSubjectHeader) {
                    // Initialize subject visibility tracking
                    const subjectText = row.textContent.trim();
                    if (!subjectVisibility.has(subjectText)) {
                        subjectVisibility.set(subjectText, false);
                    }
                    return;
                }

                // This is an assignment row - check if student has problems
                const studentCell = row.querySelector(`td[data-student-id="${studentId}"]`);
                let hasProblems = false;

                if (studentCell) {
                    const grade = studentCell.getAttribute('data-grade');
                    const cellClass = studentCell.className;

                    // Check if this is a problematic assignment
                    // Problems: red cells (missing/late), grades 1, 2, MA
                    if (cellClass.includes('red-cell') ||
                        grade === '1' || grade === '2' || grade === 'MA') {
                        hasProblems = true;
                    }
                }

                // Show/hide the assignment row based on whether it has problems
                if (hasProblems) {
                    row.style.display = '';
                    // Mark this subject as having visible assignments
                    const subjectName = findSubjectForRow(row);
                    if (subjectName) {
                        subjectVisibility.set(subjectName, true);
                    }
                } else {
                    row.style.display = 'none';
                }
            });

            // Second pass: handle subject header visibility and entire table visibility
            let tableHasVisibleContent = false;

            assignmentRows.forEach(row => {
                // Skip student summary table rows
                if (row.closest('.student-summary-table')) {
                    return;
                }

                const isSubjectHeader = row.querySelector('th');

                if (isSubjectHeader) {
                    const subjectText = row.textContent.trim();
                    const hasVisibleAssignments = subjectVisibility.get(subjectText) || false;

                    if (hasVisibleAssignments) {
                        row.style.display = '';
                        tableHasVisibleContent = true;
                        console.log('Showing subject header:', subjectText);
                    } else {
                        row.style.display = 'none';
                        console.log('Hiding subject header:', subjectText);
                    }
                }
            });

            // Hide the entire table and its preceding spacing div if no content is visible
            if (!tableHasVisibleContent) {
                table.style.display = 'none';

                // Also hide the spacing div that precedes this table
                const prevElement = table.previousElementSibling;
                if (prevElement && prevElement.tagName === 'DIV' && prevElement.style.height === '20px') {
                    prevElement.style.display = 'none';
                    console.log('Hiding spacing div before empty table');
                }
            } else {
                table.style.display = '';

                // Show the spacing div if the table is visible
                const prevElement = table.previousElementSibling;
                if (prevElement && prevElement.tagName === 'DIV' && prevElement.style.height === '20px') {
                    prevElement.style.display = '';
                }
            }
        });
    }

    function findSubjectForRow(assignmentRow) {
        // Find the subject header that precedes this assignment row
        let currentRow = assignmentRow.previousElementSibling;
        while (currentRow) {
            const isSubjectHeader = currentRow.querySelector('th');
            if (isSubjectHeader) {
                return currentRow.textContent.trim();
            }
            currentRow = currentRow.previousElementSibling;
        }
        return null;
    }

    function showAllAssignments() {
        console.log('Showing all assignments');

        // Show all assignment tables and their spacing divs
        const assignmentTables = document.querySelectorAll('#subject-table');

        assignmentTables.forEach(table => {
            // Show the table
            table.style.display = '';

            // Show the spacing div that precedes this table
            const prevElement = table.previousElementSibling;
            if (prevElement && prevElement.tagName === 'DIV' && prevElement.style.height === '20px') {
                prevElement.style.display = '';
            }

            // Show all rows in the table
            const rows = table.querySelectorAll('tr');
            rows.forEach(row => {
                row.style.display = '';
            });
        });
    }

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

        // Set red color intensity based on days passed
        const redCells = document.querySelectorAll('.red-cell[data-days-passed]');
        console.log('Found red cells with days passed:', redCells.length);

        // Keep track of processed cells
        let processedCells = 0;

        redCells.forEach(cell => {
            const daysPassed = parseInt(cell.getAttribute('data-days-passed'), 10);
            if (isNaN(daysPassed)) {
                console.log('Skipping cell with invalid days passed value');
                return;
            }
            processedCells++;

            // Calculate intensity based on days passed
            const maxDays = 10; // Strongest red for 10+ days
            const factor = Math.min(daysPassed, maxDays) / maxDays;

            // Calculate color transition from base color to strongest red
            const baseR = 255;
            const baseG = 180;
            const baseB = 176;

            // Target red color
            const targetR = 255;
            const targetG = 0;
            const targetB = 0;

            // Linearly interpolate between base color and strongest red
            const newR = Math.round(baseR + (targetR - baseR) * factor);
            const newG = Math.round(baseG + (targetG - baseG) * factor);
            const newB = Math.round(baseB + (targetB - baseB) * factor);
            const newColor = `rgb(${newR}, ${newG}, ${newB})`;

            // Set the background color directly with !important to override the .red-cell class
            cell.style.setProperty('background-color', newColor, 'important');

            // Log the color after setting it to verify it was applied
            setTimeout(() => {
                console.log('Color after setting for days passed', daysPassed, ':', 
                            window.getComputedStyle(cell).backgroundColor);
            }, 0);

            // Set text color based on background darkness for better contrast
            // Use white text when the background gets dark enough (factor > 0.5)
            const textColor = factor > 0.5 ? 'white' : 'black';
            cell.style.setProperty('color', textColor, 'important');
        });

        // Update badge class for student cells with passing grades
        document.querySelectorAll('td[data-grade]').forEach(studentCell => {
            const grade = parseInt(studentCell.getAttribute('data-grade'), 10);
            const isStudent = JSON.parse(studentCell.getAttribute('data-is-student'));
            const badgeElement = studentCell.closest('tr').querySelector('span[data-days-remaining]');
            // Skip if there's no badge element (it might be hidden)
            if (!badgeElement) return;

            // For students with passing grades, update badge class if needed
            if (isStudent && !isNaN(grade) && grade >= 3 &&
                badgeElement.className.indexOf('badge bg-light text-dark') === -1) {
                badgeElement.className = badgeElement.className.replace(/badge [^ ]+/, 'badge bg-light text-dark');
            }

            // Note: For teachers, we don't need to modify badges here as it's handled server-side
        });

        // Initialize student table sorting
        initializeStudentTableSorting();
    });

    function initializeStudentTableSorting() {
        console.log('Initializing student table sorting...');
        const tables = document.querySelectorAll('.student-summary-table');
        console.log('Found', tables.length, 'student summary tables');

        // Initialize sort states for all groups and apply default sorting
        tables.forEach(table => {
            const groupName = table.getAttribute('data-group');
            console.log('Initializing table for group:', groupName);
            sortStates[groupName] = { name: 'none', pending: 'none' };

            // Apply default sort by pending grades (ascending - least missing at top)
            window.sortStudentTable(groupName, 'pending');
        });
    }

    function updateSortIcons(groupName) {
        // Find icons using a more robust method
        const tables = document.querySelectorAll('table.student-summary-table');
        let table = null;

        for (let t of tables) {
            if (t.getAttribute('data-group') === groupName) {
                table = t;
                break;
            }
        }

        if (!table) return;

        const nameIcon = table.querySelector('th:first-child i');
        const pendingIcon = table.querySelector('th:last-child i');

        if (nameIcon) {
            nameIcon.className = 'fas fa-sort';
            if (sortStates[groupName] && sortStates[groupName].name === 'asc') {
                nameIcon.className = 'fas fa-sort-up';
            } else if (sortStates[groupName] && sortStates[groupName].name === 'desc') {
                nameIcon.className = 'fas fa-sort-down';
            }
        }

        if (pendingIcon) {
            pendingIcon.className = 'fas fa-sort';
            if (sortStates[groupName] && sortStates[groupName].pending === 'asc') {
                pendingIcon.className = 'fas fa-sort-up';
            } else if (sortStates[groupName] && sortStates[groupName].pending === 'desc') {
                pendingIcon.className = 'fas fa-sort-down';
            }
        }
    }
</script>
