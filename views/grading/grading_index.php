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
    #grading-table td {
        background-color: white;
    }

    #grading-table th {
        background-color: #f2f2f2;
    }

    /* Add subtle shadow to tables for better contrast with background */
    #grading-table {
        background-color: transparent;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.07);
    }

    /* Ensure the table-responsive container is also transparent */
    .table-responsive {
        background-color: transparent;
    }

    /* More subtle cell borders */
    #grading-table td,
    #grading-table th {
        border-color: #d8d8d8 !important;
    }

    /* Ensure table layout is fixed for consistent column widths but allows scaling */
    #grading-table {
        table-layout: fixed !important;
        border: 1px solid #d8d8d8 !important; /* More subtle border color */
        border-collapse: collapse !important; /* Ensure borders collapse properly */
        width: 100% !important; /* Use full width */
    }

    /* Set width for the position column (first column) */
    #grading-table td:first-child,
    #grading-table th:first-child {
        width: 50px;
        min-width: 50px;
        max-width: 50px;
        text-align: center;
        vertical-align: middle;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        font-weight: bold;
        color: #666;
    }

    /* Set width for the student name column (second column) */
    #grading-table td:nth-child(2),
    #grading-table th:nth-child(2) {
        width: 140px;
        min-width: 140px;
        max-width: 140px;
        vertical-align: middle;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    /* Set width for the assignment column (third column) */
    #grading-table td:nth-child(3),
    #grading-table th:nth-child(3) {
        vertical-align: middle;
        overflow: hidden;
        text-overflow: ellipsis;
        line-height: 1.3;
    }

    /* Set width for the submitted timestamp column (fourth column) */
    #grading-table td:nth-child(4),
    #grading-table th:nth-child(4) {
        width: 140px;
        min-width: 140px;
        max-width: 140px;
        text-align: center;
        vertical-align: middle;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    /* Set width for the age column (fifth column) */
    #grading-table td:nth-child(5),
    #grading-table th:nth-child(5) {
        width: 60px;
        min-width: 60px;
        max-width: 60px;
        text-align: center;
        vertical-align: middle;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        font-weight: normal;
        color: #666;
    }

    /* Set width for the graded timestamp column (sixth column) */
    #grading-table td:nth-child(6),
    #grading-table th:nth-child(6) {
        width: 140px;
        min-width: 140px;
        max-width: 140px;
        text-align: center;
        vertical-align: middle;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    /* Set width for the difference column (seventh column) */
    #grading-table td:nth-child(7),
    #grading-table th:nth-child(7) {
        width: 60px;
        min-width: 60px;
        max-width: 60px;
        text-align: center;
        vertical-align: middle;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        font-weight: normal;
        color: #666;
    }

    /* Set width for the grade column (eighth column) */
    #grading-table td:nth-child(8),
    #grading-table th:nth-child(8) {
        width: 80px;
        min-width: 80px;
        max-width: 80px;
        text-align: center;
        vertical-align: middle;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    /* Style for assignment name (bold) */
    .assignment-name {
        font-weight: bold;
        display: inline;
        margin-right: 8px;
        text-decoration: none;
        color: inherit;
    }

    /* Ensure assignment name links maintain styling on hover */
    .assignment-name:hover {
        text-decoration: underline;
        color: inherit;
    }

    /* Style for subject name badge - keep neutral gray */
    .subject-name {
        display: inline-block;
        background-color: #e9ecef;
        color: #495057;
        padding: 2px 8px;
        border-radius: 12px;
        font-size: 0.85em;
        font-weight: normal;
        vertical-align: middle;
    }

    /* Rainbow colors for table rows - more visible tints */
    .subject-row-0 {
        background-color: rgba(231, 76, 60, 0.25) !important;
    }

    /* Red */
    .subject-row-1 {
        background-color: rgba(230, 126, 34, 0.25) !important;
    }

    /* Orange */
    .subject-row-2 {
        background-color: rgba(241, 196, 15, 0.3) !important;
    }

    /* Yellow */
    .subject-row-3 {
        background-color: rgba(46, 204, 113, 0.25) !important;
    }

    /* Green */
    .subject-row-4 {
        background-color: rgba(52, 152, 219, 0.25) !important;
    }

    /* Blue */
    .subject-row-5 {
        background-color: rgba(155, 89, 182, 0.25) !important;
    }

    /* Purple */
    .subject-row-6 {
        background-color: rgba(233, 30, 99, 0.25) !important;
    }

    /* Pink */
    .subject-row-7 {
        background-color: rgba(0, 188, 212, 0.25) !important;
    }

    /* Cyan */
    .subject-row-8 {
        background-color: rgba(255, 152, 0, 0.25) !important;
    }

    /* Deep Orange */
    .subject-row-9 {
        background-color: rgba(96, 125, 139, 0.25) !important;
    }

    /* Blue Grey */
    .subject-row-10 {
        background-color: rgba(139, 195, 74, 0.25) !important;
    }

    /* Light Green */
    .subject-row-11 {
        background-color: rgba(255, 87, 34, 0.25) !important;
    }

    /* Deep Orange Red */
    .subject-row-12 {
        background-color: rgba(121, 85, 72, 0.25) !important;
    }

    /* Brown */
    .subject-row-13 {
        background-color: rgba(0, 150, 136, 0.25) !important;
    }

    /* Teal */
    .subject-row-14 {
        background-color: rgba(103, 58, 183, 0.25) !important;
    }

    /* Deep Purple */
    .subject-row-15 {
        background-color: rgba(255, 64, 129, 0.25) !important;
    }

    /* Pink Accent */
    .subject-row-16 {
        background-color: rgba(76, 175, 80, 0.25) !important;
    }

    /* Material Green */
    .subject-row-17 {
        background-color: rgba(33, 150, 243, 0.25) !important;
    }

    /* Material Blue */
    .subject-row-18 {
        background-color: rgba(255, 111, 0, 0.3) !important;
    }

    /* Amber */
    .subject-row-19 {
        background-color: rgba(55, 71, 79, 0.25) !important;
    }

    /* Dark Blue Grey */
    .subject-row-20 {
        background-color: rgba(211, 47, 47, 0.25) !important;
    }

    /* Dark Red */
    .subject-row-21 {
        background-color: rgba(123, 31, 162, 0.25) !important;
    }

    /* Dark Purple */
    .subject-row-22 {
        background-color: rgba(56, 142, 60, 0.25) !important;
    }

    /* Dark Green */
    .subject-row-23 {
        background-color: rgba(25, 118, 210, 0.25) !important;
    }

    /* Dark Blue */

    /* Ensure table cells maintain white background within colored rows */
    .subject-row-0 td, .subject-row-1 td, .subject-row-2 td, .subject-row-3 td,
    .subject-row-4 td, .subject-row-5 td, .subject-row-6 td, .subject-row-7 td,
    .subject-row-8 td, .subject-row-9 td, .subject-row-10 td, .subject-row-11 td,
    .subject-row-12 td, .subject-row-13 td, .subject-row-14 td, .subject-row-15 td,
    .subject-row-16 td, .subject-row-17 td, .subject-row-18 td, .subject-row-19 td,
    .subject-row-20 td, .subject-row-21 td, .subject-row-22 td, .subject-row-23 td {
        background-color: transparent !important;
    }

    /* Make table rows clickable */
    #grading-table tbody tr {
        cursor: pointer;
        transition: opacity 0.2s ease;
    }

    #grading-table tbody tr:hover {
        opacity: 0.8;
    }

    /* Modal styling */
    .modal-header {
        background-color: #f8f9fa;
        border-bottom: 1px solid #dee2e6;
    }

    /* Assignment ID badge in modal title */
    .modal-title .badge {
        font-size: 0.8em;
        vertical-align: middle;
    }

    .modal-body {
        max-height: 70vh;
        overflow-y: auto;
    }

    .message-container {
        max-height: 300px;
        overflow-y: auto;
        padding: 0;
        background-color: transparent;
    }

    .message-item {
        padding: 0.75rem 0;
        margin-bottom: 0.75rem;
        border-bottom: 1px solid #e9ecef;
    }

    .message-item:last-child {
        margin-bottom: 0;
        border-bottom: none;
    }

    .message-author {
        font-weight: bold;
        color: #495057;
        font-size: 0.9em;
    }

    .message-time {
        color: #6c757d;
        font-size: 0.8em;
    }

    .message-content {
        margin-top: 0.5rem;
        color: #212529;
    }

    .loading-spinner {
        display: inline-block;
        width: 1rem;
        height: 1rem;
        border: 2px solid #f3f3f3;
        border-top: 2px solid #007bff;
        border-radius: 50%;
        animation: spin 1s linear infinite;
    }

    @keyframes spin {
        0% {
            transform: rotate(0deg);
        }
        100% {
            transform: rotate(360deg);
        }
    }

    /* Solution URL styling */
    #solutionUrlInput {
        font-size: 0.85em;
        background-color: #f8f9fa;
    }

    /* Markdown content styling */
    .markdown-content {
        line-height: 1.6;
    }

    .markdown-content h1, .markdown-content h2, .markdown-content h3,
    .markdown-content h4, .markdown-content h5, .markdown-content h6 {
        margin-top: 1rem;
        margin-bottom: 0.5rem;
        font-weight: 600;
    }

    .markdown-content h1 {
        font-size: 1.5rem;
    }

    .markdown-content h2 {
        font-size: 1.3rem;
    }

    .markdown-content h3 {
        font-size: 1.1rem;
    }

    .markdown-content h4, .markdown-content h5, .markdown-content h6 {
        font-size: 1rem;
    }

    .markdown-content ul, .markdown-content ol {
        padding-left: 1.5rem;
        margin-bottom: 1rem;
    }

    .markdown-content li {
        margin-bottom: 0.25rem;
    }

    .markdown-content code {
        background-color: #f1f3f4;
        padding: 0.125rem 0.25rem;
        border-radius: 0.25rem;
        font-family: 'Courier New', monospace;
        font-size: 0.9em;
    }

    .markdown-content pre {
        background-color: #f8f9fa;
        border: 1px solid #e9ecef;
        border-radius: 0.375rem;
        padding: 1rem;
        overflow-x: auto;
        margin-bottom: 1rem;
        line-height: 1 !important;
        font-size: 0.875rem;
    }

    .markdown-content pre code {
        background-color: transparent;
        padding: 0 !important;
        margin: 0 !important;
        line-height: 1 !important;
        white-space: pre !important;
        display: block;
        font-size: inherit;
        border: none !important;
    }

    /* Specific targeting for message content code blocks */
    .message-content pre {
        line-height: 1 !important;
        font-size: 0.875rem;
    }

    .message-content pre code {
        line-height: 1 !important;
        white-space: pre !important;
        display: block;
        padding: 0 !important;
        margin: 0 !important;
        font-size: inherit;
        border: none !important;
    }

    .markdown-content blockquote {
        border-left: 4px solid #dee2e6;
        padding-left: 1rem;
        margin-left: 0;
        margin-bottom: 1rem;
        color: #6c757d;
    }

    .markdown-content p {
        margin-bottom: 1rem;
    }

    .markdown-content strong {
        font-weight: 600;
    }

    .markdown-content em {
        font-style: italic;
    }

    /* Table styling within message content */
    .message-content table {
        font-size: 0.9em;
        margin: 0.5rem 0;
    }

    .message-content table th,
    .message-content table td {
        padding: 0.375rem 0.5rem;
        vertical-align: top;
        word-wrap: break-word;
    }

    .message-content table th {
        background-color: #f8f9fa;
        font-weight: 600;
        border-bottom: 2px solid #dee2e6;
    }

    /* Horizontal rule styling within message content */
    .message-content hr {
        margin: 1rem 0;
        border: 0;
        border-top: 1px solid #dee2e6;
        opacity: 0.7;
    }

    /* Style for timestamp badge */
    .id-badge {
        background-color: #e9ecef;
        padding: 2px 6px;
        border-radius: 3px;
        font-size: 0.85em;
        color: #495057;
    }

    /* Comment bubble styling */
    .comment-bubble {
        display: inline-flex;
        align-items: center;
        background-color: #007bff;
        color: white;
        border-radius: 12px;
        padding: 2px 8px;
        font-size: 0.75em;
        margin-left: 8px;
        vertical-align: middle;
        min-width: 24px;
        justify-content: center;
    }

    .comment-bubble i {
        font-size: 0.8em;
        margin-right: 3px;
    }

    .comment-count {
        font-weight: bold;
        font-size: 0.9em;
    }

    /* Hover effect for comment bubble */
    .comment-bubble:hover {
        background-color: #0056b3;
        transform: scale(1.05);
        transition: all 0.2s ease;
    }

    /* Grade error styling */
    #gradeError {
        display: none;
        margin-top: 0.5rem;
    }

    /* Grade button styling */
    #gradeButtons .btn-check:checked + .btn {
        font-weight: bold;
    }

    /* Save grade button styling */
    #saveGradeBtn:disabled {
        opacity: 0.6;
    }

    /* Grade badge styling for table rows */
    .grade-badge {
        font-weight: bold;
        font-size: 0.9em;
        padding: 0.25rem 0.5rem;
        border-radius: 0.375rem;
        display: inline-block;
        min-width: 2rem;
        text-align: center;
    }

    /* Graded row styling */
    .grading-row.graded {
        opacity: 0.7;
        background-color: #f8f9fa !important;
    }

    /* Modal footer button consistency */
    .modal-footer #gradeButtons .btn {
        height: 38px; /* Match standard Bootstrap button height */
        min-width: 40px;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .modal-footer #saveBtn {
        height: 38px; /* Match grade button height */
        min-width: 100px;
    }

    /* Improved modal footer layout */
    .modal-footer .d-flex.gap-3 {
        gap: 1rem !important;
    }

    /* Grade button group spacing */
    .modal-footer #gradeButtons {
        margin-right: 0.5rem;
    }

    /* Grade cell styling */
    .grade-cell {
        vertical-align: middle !important;
        padding: 0.5rem !important;
    }

    /* Grade badge in table styling */
    .grade-cell .grade-badge {
        font-weight: bold;
        font-size: 0.875rem;
        padding: 0.375rem 0.75rem;
        border-radius: 0.375rem;
        display: inline-block;
        min-width: 2.5rem;
        text-align: center;
    }

    /* Sortable table headers */
    .sortable-header {
        cursor: pointer;
        user-select: none;
        position: relative;
        padding-right: 25px !important;
    }

    .sortable-header:hover {
        background-color: #e9ecef !important;
    }

    .sort-indicator {
        position: absolute;
        right: 8px;
        top: 50%;
        transform: translateY(-50%);
        font-size: 0.8em;
        color: #6c757d;
        opacity: 0.7;
    }

    .sort-indicator.active {
        color: #495057;
        opacity: 1;
        font-weight: bold;
    }

    /* Ensure sort indicators don't interfere with text */
    .sortable-header .sort-indicator {
        pointer-events: none;
    }

    /* -------------------------------------------------
       MOBILE - hide unneeded columns + tighten spacing
       -------------------------------------------------*/
    @media (max-width: 990px) {
        /* Hide unimportant columns in phone view */
        /* Esitatud   */
        #grading-table th:nth-child(4),
        #grading-table td:nth-child(4),
            /* Hinnatud   */
        #grading-table th:nth-child(6),
        #grading-table td:nth-child(6),
            /* Vahe       */
        #grading-table th:nth-child(7),
        #grading-table td:nth-child(7),
            /* Hinne      */
        #grading-table th:nth-child(8),
        #grading-table td:nth-child(8) {
            display: none !important;
        }

        /* Make the remaining columns slimmer */
        #grading-table th,
        #grading-table td {
            padding: 4px 6px !important; /* much tighter */
            font-size: .80rem; /* a bit smaller text */
        }

        #grading-table td:first-child,
        #grading-table th:first-child { /* “#” column */
            width: 32px;
            min-width: 32px;
        }

        #grading-table td:nth-child(2),
        #grading-table th:nth-child(2) { /* Õpilane */
            padding-right: 22px !important;
            width: 60px; /* was 110px */
            max-width: 60px;
        }

        /* Let Ülesanne grow/shrink as needed */
        #grading-table td:nth-child(3),
        #grading-table th:nth-child(3) {
            width: auto;
            max-width: initial;
            white-space: normal; /* allow wrapping */
        }

        /* Vanus */
        #grading-table th:nth-child(5) {
            padding-right: 22px !important;
            text-align: left;
            color: black;
            font-weight: bold;
            width: 50px; /* was 110px */
            max-width: 50px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis; /* show “…” when needed */
        }
    }

</style>

<?php if ($this->auth->userIsAdmin || $this->auth->userIsTeacher): ?>
    <div class="col text-end mb-3 d-flex justify-content-end align-items-center">
        <div class="form-check form-switch me-3">
            <input class="form-check-input" type="checkbox"
                   id="showGradedToggle" <?= $this->showGraded ? 'checked' : '' ?>>
            <label class="form-check-label" for="showGradedToggle">Näita hinnatud</label>
        </div>
    </div>
<?php endif; ?>

<div class="row">
    <div class="col-12">
        <h1>Hindamine</h1>
        <div class="table-responsive" style="background-color: transparent;">
            <table id="grading-table" class="table table-bordered"
                   style="background-color: transparent; table-layout: fixed !important;">
                <thead>
                <tr>
                    <th class="sortable-header" data-sort="position" data-bs-toggle="tooltip" data-bs-placement="top"
                        title="Järjekorranumber">
                        #
                        <span class="sort-indicator">⇅</span>
                    </th>
                    <th class="sortable-header" data-sort="student" data-bs-toggle="tooltip" data-bs-placement="top"
                        title="Õpilase nimi">
                        Õpilane
                        <span class="sort-indicator">⇅</span>
                    </th>
                    <th class="sortable-header" data-sort="assignment" data-bs-toggle="tooltip" data-bs-placement="top"
                        title="Ülesande nimi ja aine">
                        Ülesanne
                        <span class="sort-indicator">⇅</span>
                    </th>
                    <th class="sortable-header" data-sort="submitted" data-bs-toggle="tooltip" data-bs-placement="top"
                        title="Ülesande esitamise kuupäev ja kellaaeg">
                        Esitatud
                        <span class="sort-indicator">⇅</span>
                    </th>
                    <th class="sortable-header" data-sort="age" data-bs-toggle="tooltip" data-bs-placement="top"
                        title="Mitu päeva tagasi esitatud">
                        Vanus
                        <span class="sort-indicator">⇅</span>
                    </th>
                    <th class="sortable-header" data-sort="graded" data-bs-toggle="tooltip" data-bs-placement="top"
                        title="Ülesande hindamise kuupäev ja kellaaeg">
                        Hinnatud
                        <span class="sort-indicator">⇅</span>
                    </th>
                    <th class="sortable-header" data-sort="difference" data-bs-toggle="tooltip" data-bs-placement="top"
                        title="Mitu päeva kulus esitamisest hindamiseni">
                        Vahe
                        <span class="sort-indicator">⇅</span>
                    </th>
                    <th class="sortable-header" data-sort="grade" style="width: 80px;" data-bs-toggle="tooltip"
                        data-bs-placement="top" title="Antud hinne">
                        Hinne
                        <span class="sort-indicator">⇅</span>
                    </th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($this->submissions as $index => $submission): ?>
                    <tr class="subject-row-<?= $this->subjectColors[$submission['subjectId']] ?> grading-row"
                        data-assignment-id="<?= $submission['assignmentId'] ?>"
                        data-user-id="<?= $submission['userId'] ?>"
                        data-assignment-name="<?= htmlspecialchars($submission['Ülesanne']) ?>"
                        data-student-name="<?= htmlspecialchars($submission['Õpilane']) ?>"
                        data-solution-url="<?= htmlspecialchars($submission['solutionUrl'] ?? '') ?>"
                        data-assignment-instructions="<?= htmlspecialchars($submission['assignmentInstructions'] ?? '') ?>"
                        data-comments="<?= htmlspecialchars($submission['comments'] ?? '[]') ?>"
                        data-current-grade="<?= htmlspecialchars($submission['userGrade'] ?? '') ?>"
                        data-criteria="<?= htmlspecialchars($submission['criteria'] ?? '[]') ?>"
                        data-sort-position="<?= $index + 1 ?>"
                        data-sort-submitted="<?= $submission['Esitatud'] ? strtotime($submission['Esitatud']) : 0 ?>"
                        data-sort-graded="<?= $submission['Hinnatud'] ? strtotime($submission['Hinnatud']) : 0 ?>"
                        data-sort-age="<?= $submission['Vanus'] ?: 999999 ?>"
                        data-sort-difference="<?= $submission['Vahe'] ?: 999999 ?>"
                        data-sort-student="<?= htmlspecialchars($submission['Õpilane']) ?>"
                        data-sort-subject="<?= htmlspecialchars($submission['Aine']) ?>"
                        data-sort-grade="<?= htmlspecialchars($submission['userGrade'] ?? '') ?>">
                        <td><?= $index + 1 ?></td>
                        <td data-bs-toggle="tooltip" data-bs-placement="top"
                            title="<?= htmlspecialchars($submission['Õpilane']) ?>"><?= $submission['Õpilane'] ?></td>
                        <td data-bs-toggle="tooltip" data-bs-placement="top"
                            title="<?= htmlspecialchars($submission['Aine'] . ' - ' . $submission['Ülesanne']) ?>">
                            <span class="subject-name"><?= $submission['Aine'] ?></span>
                            <a href="grading/assignments/<?= $submission['assignmentId'] ?>/students/<?= $submission['userId'] ?>"
                               onclick="event.preventDefault(); openGradingModal(this.closest('tr')); return false;"
                               class="assignment-name"
                               style="text-decoration: none; color: inherit;"><?= $submission['Ülesanne'] ?></a>
                            <?php if ($submission['commentCount'] > 0): ?>
                                <span class="comment-bubble">
                                        <i class="fas fa-comment"></i>
                                        <span class="comment-count"><?= $submission['commentCount'] ?></span>
                                    </span>
                            <?php endif; ?>
                        </td>
                        <td data-bs-toggle="tooltip" data-bs-placement="top"
                            title="<?= $submission['Esitatud'] ? 'Esitatud: ' . (new DateTime($submission['Esitatud']))->format('d.m.Y H:i') : 'Esitamata' ?>"><?= $submission['Esitatud'] ? '<span class="id-badge"><strong>' . (new DateTime($submission['Esitatud']))->format('d.m.y') . '</strong> ' . (new DateTime($submission['Esitatud']))->format('H:i') . '</span>' : '' ?></td>
                        <td data-bs-toggle="tooltip" data-bs-placement="top"
                            title="<?= $submission['Vanus'] ? $submission['Vanus'] . ' päeva tagasi' : 'Täna esitatud' ?>"><?= $submission['Vanus'] ?></td>
                        <td data-bs-toggle="tooltip" data-bs-placement="top"
                            title="<?= $submission['Hinnatud'] ? 'Hinnatud: ' . (new DateTime($submission['Hinnatud']))->format('d.m.Y H:i') : 'Hindamata' ?>"><?= $submission['Hinnatud'] ? '<span class="id-badge"><strong>' . (new DateTime($submission['Hinnatud']))->format('d.m.y') . '</strong> ' . (new DateTime($submission['Hinnatud']))->format('H:i') . '</span>' : '' ?></td>
                        <td data-bs-toggle="tooltip" data-bs-placement="top"
                            title="<?= $submission['Vahe'] ? 'Hindamine võttis ' . $submission['Vahe'] . ' päeva' : 'Sama päeva jooksul hinnatud' ?>"><?= $submission['Vahe'] ?></td>
                        <td class="grade-cell text-center">
                            <!-- Grade will be populated here after grading -->
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Grading Modal -->
<div class="modal fade" id="gradingModal" tabindex="-1" aria-labelledby="gradingModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-fluid" style="max-width: 95%; width: 95%;">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="gradingModalLabel">Ülesanne</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <!-- Solution URL Section -->
                <div class="mb-3" id="solutionUrlSection"><h6>Lahenduse URL</h6>
                    <div class="mt-2 d-none" id="solutionUrlDetails">
                        <div class="input-group">
                            <input type="text" class="form-control form-control-sm font-monospace" id="solutionUrlInput"
                                   readonly>
                            <button class="btn btn-outline-secondary btn-sm" type="button" id="copySolutionUrl"
                                    title="Kopeeri URL">
                                <i class="fas fa-copy"></i>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Assignment Instructions -->
                <div class="mb-3" id="instructionsSection">
                    <h6>Ülesande kirjeldus</h6>
                    <div id="assignmentInstructions" class="border rounded p-3 bg-white markdown-content">
                        <p class="text-muted">Kirjeldus puudub</p>
                    </div>
                </div>

                <!-- Assignment Criteria Section -->
                <div class="mb-3" id="criteriaSection">
                    <h6>Hindamiskriteeriumid</h6>
                    <div id="criteriaContainer" class="border rounded p-3 bg-light">
                        <p class="text-muted">Kriteeriume pole määratud</p>
                    </div>
                </div>


                <!-- Comments Thread -->
                <div class="mb-3">
                    <h6>Vestlus</h6>
                    <div id="messagesContainer" class="message-container border-top pt-2">
                        <div class="text-center">
                            <span class="loading-spinner"></span>
                            <span class="ms-2">Laen sõnumeid...</span>
                        </div>
                    </div>
                </div>

                <!-- New Message Form -->
                <div class="mb-3">
                    <label for="newMessageContent" class="form-label">Lisa kommentaar</label>
                    <textarea class="form-control" id="newMessageContent" rows="3"
                              placeholder="Kirjuta kommentaar õpilasele..."></textarea>
                    <div class="invalid-feedback" id="messageError"></div>
                </div>
            </div>
            <div class="modal-footer">
                <div class="d-flex justify-content-end w-100 align-items-center gap-3">
                    <div class="d-flex align-items-center">
                        <span class="me-3"><strong>Hinne:</strong></span>
                        <div class="btn-group" role="group" aria-label="Hinded" id="gradeButtons">
                            <input type="radio" class="btn-check" name="grade" id="grade2" value="2">
                            <label class="btn btn-outline-danger" for="grade2">2</label>

                            <input type="radio" class="btn-check" name="grade" id="grade3" value="3">
                            <label class="btn btn-outline-warning" for="grade3">3</label>

                            <input type="radio" class="btn-check" name="grade" id="grade4" value="4">
                            <label class="btn btn-outline-success" for="grade4">4</label>

                            <input type="radio" class="btn-check" name="grade" id="grade5" value="5">
                            <label class="btn btn-outline-success" for="grade5">5</label>

                            <input type="radio" class="btn-check" name="grade" id="gradeA" value="A">
                            <label class="btn btn-outline-primary" for="gradeA">A</label>

                            <input type="radio" class="btn-check" name="grade" id="gradeMA" value="MA">
                            <label class="btn btn-outline-secondary" for="gradeMA">MA</label>
                        </div>
                    </div>
                    <div>
                        <button type="button" class="btn btn-primary" id="saveBtn" disabled>
                            <span id="saveBtnText">Salvesta</span>
                            <span id="saveBtnSpinner" class="loading-spinner d-none ms-2"></span>
                        </button>
                    </div>
                </div>
                <div class="invalid-feedback" id="gradeError"></div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Initialize tooltips
        [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
            .map(el => new bootstrap.Tooltip(el));

        // Add click handlers to grading rows
        const gradingRows = document.querySelectorAll('.grading-row');
        gradingRows.forEach(row => {
            row.addEventListener('click', function () {
                openGradingModal(this);
            });
        });

        // Copy solution URL button handler
        document.getElementById('copySolutionUrl').addEventListener('click', function () {
            copySolutionUrl();
        });

        // Grade button handlers
        const gradeButtons = document.querySelectorAll('input[name="grade"]');

        gradeButtons.forEach(button => {
            button.addEventListener('change', function () {
                updateSaveButtonState();
            });
        });

        // Save button handler (consolidated save functionality)
        document.getElementById('saveBtn').addEventListener('click', function () {
            saveGradeAndComment();
        });

        // Initialize table sorting
        initializeTableSorting();

        // Handle the "Show graded" toggle
        const showGradedToggle = document.getElementById('showGradedToggle');
        if (showGradedToggle) {
            showGradedToggle.addEventListener('change', function () {
                const url = new URL(window.location.href);
                url.searchParams.set('showGraded', this.checked ? '1' : '0');
                window.location.href = url.toString();
            });
        }
    });

    let currentAssignmentId = null;
    let currentUserId = null;
    let gradingModalInstance = null;

    function openGradingModal(row) {
        // Extract data from row
        const assignmentId = row.dataset.assignmentId;
        const userId = row.dataset.userId;
        const assignmentName = row.dataset.assignmentName;
        const studentName = row.dataset.studentName;
        const solutionUrl = row.dataset.solutionUrl;
        const assignmentInstructions = row.dataset.assignmentInstructions;
        const comments = row.dataset.comments;
        const currentGrade = row.dataset.currentGrade;
        const criteriaData = row.dataset.criteria;

        // Store current IDs
        currentAssignmentId = assignmentId;
        currentUserId = userId;

        // Update modal title with new format: "[ID] Student Name | Assignment Name"
        document.getElementById('gradingModalLabel').innerHTML = `<span class="badge bg-secondary me-2">${assignmentId}</span>${studentName} | ${assignmentName}`;

        // Clear any previously selected grades to force manual selection
        const gradeButtons = document.querySelectorAll('input[name="grade"]');

        gradeButtons.forEach(button => {
            button.checked = false;
        });

        // Ensure save button starts disabled to enforce manual grade selection
        const saveBtn = document.getElementById('saveBtn');
        saveBtn.disabled = true;

        // Load and display criteria
        loadCriteria(criteriaData);

        // Update solution URL section
        const solutionUrlContainer = document.getElementById('solutionUrlContainer');
        const solutionUrlDetails = document.getElementById('solutionUrlDetails');
        const solutionUrlInput = document.getElementById('solutionUrlInput');

        if (solutionUrl && solutionUrl.trim() !== '') {

            solutionUrlInput.value = solutionUrl;
            solutionUrlDetails.classList.remove('d-none');
        } else {

            solutionUrlDetails.classList.add('d-none');
        }

        // Update instructions section with Markdown rendering
        const instructionsDiv = document.getElementById('assignmentInstructions');
        if (assignmentInstructions && assignmentInstructions.trim() !== '') {
            instructionsDiv.innerHTML = parseMarkdown(assignmentInstructions);
        } else {
            instructionsDiv.innerHTML = '<p class="text-muted">Kirjeldus puudub</p>';
        }

        // Load messages
        loadMessages(assignmentId, userId);

        // Clear new message form
        document.getElementById('newMessageContent').value = '';
        document.getElementById('messageError').textContent = '';

        // Clear grade error and hide it
        const gradeError = document.getElementById('gradeError');
        gradeError.textContent = '';
        gradeError.style.display = 'none';

        // Get modal element
        const modalElement = document.getElementById('gradingModal');

        // Check if modal instance already exists
        if (gradingModalInstance) {
            // Dispose of existing instance to prevent conflicts
            gradingModalInstance.dispose();
        }

        // Create new modal instance
        gradingModalInstance = new bootstrap.Modal(modalElement);

        // Add event listener for proper cleanup when modal is hidden
        modalElement.addEventListener('hidden.bs.modal', function () {
            // Remove any lingering backdrops
            const backdrops = document.querySelectorAll('.modal-backdrop');
            backdrops.forEach(backdrop => backdrop.remove());

            // Restore body scroll
            document.body.classList.remove('modal-open');
            document.body.style.overflow = '';
            document.body.style.paddingRight = '';
        }, {once: true});

        // Show modal
        gradingModalInstance.show();
    }

    function loadCriteria(criteriaData) {
        const criteriaContainer = document.getElementById('criteriaContainer');

        try {
            const criteria = JSON.parse(criteriaData || '[]');

            if (criteria.length === 0) {
                criteriaContainer.innerHTML = '<p class="text-muted">Kriteeriume pole määratud</p>';
                return;
            }

            let criteriaHtml = '';
            criteria.forEach(criterion => {
                const checked = criterion.isCompleted ? 'checked' : '';
                criteriaHtml += `
                    <div class="form-check">
                        <input type="checkbox" class="form-check-input" id="criterion${criterion.criterionId}"
                               data-criterion-id="${criterion.criterionId}" ${checked}>
                        <label class="form-check-label" for="criterion${criterion.criterionId}">
                            ${criterion.criterionName}
                        </label>
                    </div>
                `;
            });

            criteriaContainer.innerHTML = criteriaHtml;

            // Add event listeners to criteria checkboxes
            const criteriaCheckboxes = criteriaContainer.querySelectorAll('input[type="checkbox"]');
            criteriaCheckboxes.forEach(checkbox => {
                checkbox.addEventListener('change', function () {
                    updateSaveButtonState();
                });
            });

        } catch (error) {
            console.error('Error parsing criteria data:', error);
            criteriaContainer.innerHTML = '<p class="text-danger">Viga kriteeriumide laadimisel</p>';
        }
    }

    function updateSaveButtonState() {
        const saveBtn = document.getElementById('saveBtn');
        const selectedGrade = document.querySelector('input[name="grade"]:checked');

        // Enable save button if grade is selected
        saveBtn.disabled = !selectedGrade;
    }

    function saveGradeAndComment() {
        const selectedGrade = document.querySelector('input[name="grade"]:checked')?.value;
        const comment = document.getElementById('newMessageContent').value.trim();
        const gradeError = document.getElementById('gradeError');
        const saveBtn = document.getElementById('saveBtn');
        const saveBtnText = document.getElementById('saveBtnText');
        const saveBtnSpinner = document.getElementById('saveBtnSpinner');

        // Clear previous errors
        gradeError.textContent = '';

        // Validate grade selection
        if (!selectedGrade) {
            gradeError.textContent = 'Palun valige hinne';
            gradeError.style.display = 'block';
            return;
        }

        // Collect criteria data
        const criteriaData = {};
        const criteriaCheckboxes = document.querySelectorAll('#criteriaContainer input[type="checkbox"]');
        criteriaCheckboxes.forEach(checkbox => {
            const criterionId = checkbox.dataset.criterionId;
            criteriaData[criterionId] = checkbox.checked ? 'true' : 'false';
        });

        // Show loading state
        saveBtn.disabled = true;
        saveBtnText.textContent = 'Salvestab...';
        saveBtnSpinner.classList.remove('d-none');

        // Prepare form data
        const formData = new URLSearchParams();
        formData.append('assignmentId', currentAssignmentId);
        formData.append('studentId', currentUserId);
        formData.append('grade', selectedGrade);
        formData.append('comment', comment);

        // Add criteria data
        Object.keys(criteriaData).forEach(criterionId => {
            formData.append(`criteria[${criterionId}]`, criteriaData[criterionId]);
        });

        // Send grade and criteria
        fetch('<?= BASE_URL ?>grading/saveGrade', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: formData.toString()
        })
            .then(response => response.json())
            .then(data => {
                if (data.status === 200) {
                    // Clear comment form and reload messages
                    document.getElementById('newMessageContent').value = '';
                    loadMessages(currentAssignmentId, currentUserId);

                    // Update the table row to reflect the new grade
                    updateTableRowGrade(currentAssignmentId, currentUserId, selectedGrade);

                    // Add grade badge to table row
                    addGradeBadgeToTableRow(currentAssignmentId, currentUserId, selectedGrade);

                    // Auto-close modal after successful save
                    try {
                        if (gradingModalInstance) {
                            gradingModalInstance.hide();
                        } else {
                            console.error('Modal instance not found for auto-close');
                        }
                    } catch (error) {
                        console.error('Error auto-closing modal:', error);
                    }

                    // If "Show graded" toggle is unchecked, remove the graded row from the table
                    const showGradedToggle = document.getElementById('showGradedToggle');
                    if (showGradedToggle && !showGradedToggle.checked) {
                        const targetRow = document.querySelector('.grading-row[data-assignment-id="' + currentAssignmentId + '"][data-user-id="' + currentUserId + '"]');
                        if (targetRow) {
                            targetRow.remove();
                            // Update position numbers for remaining rows
                            const remainingRows = Array.from(document.querySelectorAll('.grading-row'));
                            updatePositionNumbers(remainingRows, 'asc');
                        }
                    }
                } else {
                    gradeError.textContent = data.message || 'Viga andmete salvestamisel';
                    gradeError.style.display = 'block';
                    gradeError.classList.add('d-block');

                    // Re-enable button immediately on error
                    saveBtn.disabled = false;
                    saveBtnText.textContent = 'Salvesta';
                    saveBtnSpinner.classList.add('d-none');
                }
            })
            .catch(error => {
                console.error('Error saving data:', error);
                gradeError.textContent = 'Viga andmete salvestamisel';
                gradeError.style.display = 'block';
                gradeError.classList.add('d-block');

                // Re-enable button immediately on error
                saveBtn.disabled = false;
                saveBtnText.textContent = 'Salvesta';
                saveBtnSpinner.classList.add('d-none');
            });
    }

    function updateTableRowGrade(assignmentId, userId, grade) {
        // Find the table row and update its data attribute
        const rows = document.querySelectorAll('.grading-row');
        rows.forEach(row => {
            if (row.dataset.assignmentId === assignmentId && row.dataset.userId === userId) {
                row.dataset.currentGrade = grade;
            }
        });
    }

    function addGradeBadgeToTableRow(assignmentId, userId, grade) {
        try {
            // Find the specific table row using the reliable selector
            const targetRow = document.querySelector('.grading-row[data-assignment-id="' + assignmentId + '"][data-user-id="' + userId + '"]');

            if (!targetRow) {
                console.warn('Table row not found for adding grade badge:', {assignmentId, userId});
                return;
            }

            // Find the grade cell (8th column - last cell in the row with class grade-cell)
            const gradeCell = targetRow.querySelector('td:nth-child(8).grade-cell');
            if (!gradeCell) {
                console.warn('Grade cell not found in table row');
                return;
            }

            // Clear any existing content in the grade cell
            gradeCell.innerHTML = '';

            // Create grade badge with appropriate styling based on grade value
            const gradeBadge = document.createElement('span');
            gradeBadge.className = 'grade-badge badge';
            gradeBadge.textContent = grade;

            // Apply grade-specific styling
            switch (grade) {
                case '2':
                    gradeBadge.classList.add('bg-danger');
                    break;
                case '3':
                    gradeBadge.classList.add('bg-warning', 'text-dark');
                    break;
                case '4':
                case '5':
                    gradeBadge.classList.add('bg-success');
                    break;
                case 'A':
                    gradeBadge.classList.add('bg-primary');
                    break;
                case 'MA':
                    gradeBadge.classList.add('bg-secondary');
                    break;
                default:
                    gradeBadge.classList.add('bg-info');
            }

            // Add the badge to the grade cell
            gradeCell.appendChild(gradeBadge);

            // Add tooltip to the grade badge
            gradeCell.setAttribute('data-bs-toggle', 'tooltip');
            gradeCell.setAttribute('data-bs-placement', 'top');
            gradeCell.setAttribute('title', `Hinne: ${grade}`);

            // Initialize tooltip for the new grade cell
            new bootstrap.Tooltip(gradeCell);

            // Add visual indication that this row has been graded
            targetRow.classList.add('graded');

        } catch (error) {
            console.error('Error adding grade badge to table row:', error);
        }
    }


    function loadMessages(assignmentId, studentId) {
        const messagesContainer = document.getElementById('messagesContainer');
        messagesContainer.innerHTML = `
            <div class="text-center">
                <span class="loading-spinner"></span>
                <span class="ms-2">Laen sõnumeid...</span>
            </div>
        `;

        // Make AJAX request to load messages
        fetch('<?= BASE_URL ?>grading/getMessages', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `assignmentId=${assignmentId}&studentId=${studentId}`
        })
            .then(response => {
                console.log('Response status:', response.status);
                return response.json();
            })
            .then(data => {
                console.log('Response data:', data);
                if (data.status === 200) {
                    displayMessages(data.data);
                } else {
                    messagesContainer.innerHTML = `<p class="text-danger">Viga sõnumite laadimisel: ${data.message || 'Tundmatu viga'}</p>`;
                }
            })
            .catch(error => {
                console.error('Error loading messages:', error);
                messagesContainer.innerHTML = `<p class="text-danger">Viga sõnumite laadimisel: ${error.message}</p>`;
            });
    }

    function displayMessages(messages) {
        const messagesContainer = document.getElementById('messagesContainer');

        if (messages.length === 0) {
            messagesContainer.innerHTML = '<p class="text-muted">Sõnumeid pole veel</p>';
            return;
        }

        let messagesHtml = '';
        messages.forEach(message => {
            if (!message.isNotification) {
                const messageDate = new Date(message.createdAt).toLocaleString('et-EE');
                messagesHtml += `
                    <div class="message-item">
                        <div class="d-flex justify-content-between">
                            <span class="message-author">${message.userName || 'Tundmatu'}</span>
                            <span class="message-time">${messageDate}</span>
                        </div>
                        <div class="message-content">${parseMarkdown(message.content)}</div>
                    </div>
                `;
            }
        });

        messagesContainer.innerHTML = messagesHtml || '<p class="text-muted">Sõnumeid pole veel</p>';

        // Scroll to bottom
        messagesContainer.scrollTop = messagesContainer.scrollHeight;
    }


    function copySolutionUrl() {
        const solutionUrlInput = document.getElementById('solutionUrlInput');
        solutionUrlInput.select();
        solutionUrlInput.setSelectionRange(0, 99999); // For mobile devices

        try {
            document.execCommand('copy');
            // Show temporary feedback
            const copyBtn = document.getElementById('copySolutionUrl');
            const originalHtml = copyBtn.innerHTML;
            copyBtn.innerHTML = '<i class="fas fa-check text-success"></i>';
            setTimeout(() => {
                copyBtn.innerHTML = originalHtml;
            }, 2000);
        } catch (err) {
            console.error('Failed to copy URL:', err);
        }
    }

    function parseMarkdown(text) {
        if (!text) return '';

        // Simple Markdown parser for basic formatting
        let html = text;

        // Headers
        html = html.replace(/^#### (.*$)/gim, '<h4>$1</h4>');
        html = html.replace(/^### (.*$)/gim, '<h3>$1</h3>');
        html = html.replace(/^## (.*$)/gim, '<h2>$1</h2>');
        html = html.replace(/^# (.*$)/gim, '<h1>$1</h1>');

        // Bold
        html = html.replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>');
        html = html.replace(/__(.*?)__/g, '<strong>$1</strong>');

        // Italic
        html = html.replace(/\*(.*?)\*/g, '<em>$1</em>');
        html = html.replace(/_(.*?)_/g, '<em>$1</em>');

        // Code blocks
        html = html.replace(/```([\s\S]*?)```/g, '<pre><code>$1</code></pre>');

        // Inline code
        html = html.replace(/`(.*?)`/g, '<code>$1</code>');

        // Links
        html = html.replace(/\[([^\]]+)\]\(([^)]+)\)/g, '<a href="$2" target="_blank">$1</a>');

        // Unordered lists
        html = html.replace(/^\* (.+)$/gm, '<li>$1</li>');
        html = html.replace(/(<li>.*<\/li>)/s, '<ul>$1</ul>');

        // Ordered lists
        html = html.replace(/^\d+\. (.+)$/gm, '<li>$1</li>');
        html = html.replace(/(<li>.*<\/li>)/s, function (match) {
            if (match.includes('<ul>')) return match;
            return '<ol>' + match + '</ol>';
        });

        // Blockquotes
        html = html.replace(/^> (.+)$/gm, '<blockquote>$1</blockquote>');

        // Horizontal rules
        html = html.replace(/^---+$/gm, '<hr>');

        // Tables - process before line breaks to handle multi-line table blocks
        html = parseMarkdownTables(html);

        // Clean up excessive whitespace and line breaks before processing
        html = html.replace(/\n{3,}/g, '\n\n'); // Limit to maximum 2 consecutive newlines

        // Process paragraphs and line breaks with better control
        html = cleanupLineBreaksAndParagraphs(html);

        return html;
    }

    function cleanupLineBreaksAndParagraphs(text) {
        // Split into paragraphs based on double newlines
        const paragraphs = text.split(/\n\s*\n/);
        const processedParagraphs = [];

        paragraphs.forEach(paragraph => {
            paragraph = paragraph.trim();
            if (paragraph === '') return; // Skip empty paragraphs

            // Check if paragraph already contains HTML tags (like tables, lists, etc.)
            if (paragraph.includes('<table') || paragraph.includes('<ul>') ||
                paragraph.includes('<ol>') || paragraph.includes('<blockquote>') ||
                paragraph.includes('<h1>') || paragraph.includes('<h2>') ||
                paragraph.includes('<h3>') || paragraph.includes('<h4>') ||
                paragraph.includes('<pre>') || paragraph.includes('<hr>')) {
                // Already formatted content - add as-is
                processedParagraphs.push(paragraph);
            } else {
                // Regular text paragraph - convert single newlines to <br> and wrap in <p>
                let processedParagraph = paragraph.replace(/\n/g, '<br>');

                // Limit consecutive <br> tags to maximum of 2
                processedParagraph = processedParagraph.replace(/(<br>\s*){3,}/g, '<br><br>');

                // Wrap in paragraph tags if it doesn't start with a tag
                if (!processedParagraph.startsWith('<')) {
                    processedParagraph = '<p>' + processedParagraph + '</p>';
                }

                processedParagraphs.push(processedParagraph);
            }
        });

        // Join paragraphs with single newlines (no extra spacing)
        let result = processedParagraphs.join('\n');

        // Final cleanup: remove any empty paragraphs that might have been created
        result = result.replace(/<p>\s*<\/p>/g, '');

        // Clean up any remaining excessive line breaks
        result = result.replace(/(<br>\s*){3,}/g, '<br><br>');

        // Remove <br> tags between list items (they create unwanted spacing)
        result = result.replace(/<\/li>\s*<br>\s*<li>/g, '</li><li>');
        result = result.replace(/<\/li>\s*<br>\s*<\/ul>/g, '</li></ul>');
        result = result.replace(/<\/li>\s*<br>\s*<\/ol>/g, '</li></ol>');
        result = result.replace(/<ul>\s*<br>\s*<li>/g, '<ul><li>');
        result = result.replace(/<ol>\s*<br>\s*<li>/g, '<ol><li>');

        // Remove <br> tags inside code blocks (they break ASCII art and directory structures)
        result = result.replace(/<pre><code>([\s\S]*?)<\/code><\/pre>/g, function (match, codeContent) {
            // Remove all <br> tags and any extra whitespace from code content
            let cleanedContent = codeContent.replace(/<br\s*\/?>/g, '\n');
            // Also remove any <p> tags that might have been added
            cleanedContent = cleanedContent.replace(/<\/?p>/g, '');
            // Clean up any double newlines
            cleanedContent = cleanedContent.replace(/\n\s*\n/g, '\n');
            return `<pre><code>${cleanedContent}</code></pre>`;
        });

        // Remove any trailing/leading whitespace around HTML tags
        result = result.replace(/>\s+</g, '><');

        return result;
    }

    function parseMarkdownTables(text) {
        // Split text into lines for processing
        const lines = text.split('\n');
        const result = [];
        let i = 0;

        while (i < lines.length) {
            const line = lines[i].trim();

            // Check if this line looks like a table header (contains |)
            if (line.includes('|') && line.startsWith('|') && line.endsWith('|')) {
                // Look ahead to see if next line is a separator
                if (i + 1 < lines.length) {
                    const nextLine = lines[i + 1].trim();
                    if (nextLine.includes('|') && nextLine.includes('-')) {
                        // This is a table - parse it
                        const tableResult = parseTable(lines, i);
                        result.push(tableResult.html);
                        i = tableResult.nextIndex;
                        continue;
                    }
                }
            }

            // Not a table, add the line as-is
            result.push(lines[i]);
            i++;
        }

        return result.join('\n');
    }

    function parseTable(lines, startIndex) {
        let i = startIndex;
        const tableLines = [];

        // Collect all table lines
        while (i < lines.length) {
            const line = lines[i].trim();
            if (line.includes('|') && (line.startsWith('|') || line.endsWith('|'))) {
                tableLines.push(line);
                i++;
            } else {
                break;
            }
        }

        if (tableLines.length < 2) {
            // Not a valid table
            return {html: lines[startIndex], nextIndex: startIndex + 1};
        }

        // Parse header row
        const headerRow = tableLines[0];
        const separatorRow = tableLines[1];
        const dataRows = tableLines.slice(2);

        // Extract header cells
        const headerCells = headerRow.split('|')
            .map(cell => cell.trim())
            .filter(cell => cell !== '');

        // Build table HTML with Bootstrap classes
        let tableHtml = '<table class="table table-bordered table-sm mt-2 mb-2">\n';

        // Add header
        tableHtml += '  <thead class="table-light">\n';
        tableHtml += '    <tr>\n';
        headerCells.forEach(cell => {
            tableHtml += `      <th>${cell}</th>\n`;
        });
        tableHtml += '    </tr>\n';
        tableHtml += '  </thead>\n';

        // Add body
        if (dataRows.length > 0) {
            tableHtml += '  <tbody>\n';
            dataRows.forEach(row => {
                const cells = row.split('|')
                    .map(cell => cell.trim())
                    .filter(cell => cell !== '');

                if (cells.length > 0) {
                    tableHtml += '    <tr>\n';
                    cells.forEach((cell, index) => {
                        // Pad with empty cells if needed
                        if (index < headerCells.length) {
                            tableHtml += `      <td>${cell}</td>\n`;
                        }
                    });
                    // Add empty cells if row has fewer cells than headers
                    for (let j = cells.length; j < headerCells.length; j++) {
                        tableHtml += '      <td></td>\n';
                    }
                    tableHtml += '    </tr>\n';
                }
            });
            tableHtml += '  </tbody>\n';
        }

        tableHtml += '</table>';

        return {html: tableHtml, nextIndex: i};
    }

    // Table sorting functionality
    let currentSort = {column: null, direction: 'asc'};

    function initializeTableSorting() {
        const sortableHeaders = document.querySelectorAll('.sortable-header');

        sortableHeaders.forEach(header => {
            header.addEventListener('click', function () {
                const sortType = this.dataset.sort;
                handleSort(sortType, this);
            });
        });
    }

    function handleSort(sortType, headerElement) {
        const table = document.getElementById('grading-table');
        const tbody = table.querySelector('tbody');
        const rows = Array.from(tbody.querySelectorAll('tr'));

        // Determine sort direction
        let direction = 'asc';
        if (currentSort.column === sortType && currentSort.direction === 'asc') {
            direction = 'desc';
        }

        // Update current sort state
        currentSort = {column: sortType, direction: direction};

        // Clear all sort indicators
        document.querySelectorAll('.sort-indicator').forEach(indicator => {
            indicator.classList.remove('active');
            indicator.textContent = '⇅';
        });

        // Update active sort indicator to show current sort direction
        const indicator = headerElement.querySelector('.sort-indicator');
        indicator.classList.add('active');
        // Show the current sort direction: ↑ for ascending (A→Z, 1→9, old→new), ↓ for descending (Z→A, 9→1, new→old)
        indicator.textContent = direction === 'asc' ? '↑' : '↓';

        // Sort rows
        rows.sort((a, b) => {
            return compareRows(a, b, sortType, direction);
        });

        // Re-append sorted rows
        rows.forEach(row => tbody.appendChild(row));

        // Update position numbers if sorting by position
        if (sortType === 'position') {
            updatePositionNumbers(rows, direction);
        }

        // Reinitialize tooltips after sorting
        reinitializeTooltips();
    }

    function compareRows(rowA, rowB, sortType, direction) {
        let valueA, valueB;

        switch (sortType) {
            case 'position':
                valueA = parseInt(rowA.dataset.sortPosition);
                valueB = parseInt(rowB.dataset.sortPosition);
                break;

            case 'submitted':
                valueA = parseInt(rowA.dataset.sortSubmitted);
                valueB = parseInt(rowB.dataset.sortSubmitted);
                // Handle unsubmitted assignments (timestamp = 0) - they should appear last
                if (valueA === 0 && valueB === 0) return 0;
                if (valueA === 0) return direction === 'asc' ? 1 : -1;
                if (valueB === 0) return direction === 'asc' ? -1 : 1;
                break;

            case 'graded':
                valueA = parseInt(rowA.dataset.sortGraded);
                valueB = parseInt(rowB.dataset.sortGraded);
                // Handle ungraded assignments (timestamp = 0) - they should appear last
                if (valueA === 0 && valueB === 0) return 0;
                if (valueA === 0) return direction === 'asc' ? 1 : -1;
                if (valueB === 0) return direction === 'asc' ? -1 : 1;
                break;

            case 'difference':
                valueA = parseInt(rowA.dataset.sortDifference);
                valueB = parseInt(rowB.dataset.sortDifference);
                break;

            case 'age':
                valueA = parseInt(rowA.dataset.sortAge);
                valueB = parseInt(rowB.dataset.sortAge);
                break;

            case 'student':
                valueA = rowA.dataset.sortStudent.toLowerCase();
                valueB = rowB.dataset.sortStudent.toLowerCase();
                // Sort by surname first, then first name
                const namePartsA = valueA.split(' ');
                const namePartsB = valueB.split(' ');
                const surnameA = namePartsA[namePartsA.length - 1];
                const surnameB = namePartsB[namePartsB.length - 1];

                if (surnameA !== surnameB) {
                    valueA = surnameA;
                    valueB = surnameB;
                } else {
                    valueA = namePartsA[0] || '';
                    valueB = namePartsB[0] || '';
                }
                break;

            case 'assignment':
                // Sort by subject name, then assignment name
                const subjectA = rowA.dataset.sortSubject.toLowerCase();
                const subjectB = rowB.dataset.sortSubject.toLowerCase();
                const assignmentA = rowA.dataset.assignmentName.toLowerCase();
                const assignmentB = rowB.dataset.assignmentName.toLowerCase();

                if (subjectA !== subjectB) {
                    valueA = subjectA;
                    valueB = subjectB;
                } else {
                    valueA = assignmentA;
                    valueB = assignmentB;
                }
                break;

            case 'grade':
                valueA = getGradeValue(rowA.dataset.sortGrade);
                valueB = getGradeValue(rowB.dataset.sortGrade);
                break;

            default:
                return 0;
        }

        // Compare values
        if (typeof valueA === 'string' && typeof valueB === 'string') {
            const result = valueA.localeCompare(valueB, 'et');
            return direction === 'asc' ? result : -result;
        } else {
            const result = valueA - valueB;
            return direction === 'asc' ? result : -result;
        }
    }

    function getGradeValue(grade) {
        // Custom grade sorting: ->MA,1,2,3,4,5,A
        // Ungraded assignments should appear last
        if (!grade || grade === '') return 999;

        const gradeOrder = {
            'MA': 0,
            '1': 1,
            '2': 2,
            '3': 3,
            '4': 4,
            '5': 5,
            'A': 6
        };

        return gradeOrder[grade] !== undefined ? gradeOrder[grade] : 999;
    }

    function updatePositionNumbers(rows, direction) {
        rows.forEach((row, index) => {
            const positionCell = row.querySelector('td:first-child');
            if (direction === 'asc') {
                positionCell.textContent = index + 1;
                row.dataset.sortPosition = index + 1;
            } else {
                positionCell.textContent = rows.length - index;
                row.dataset.sortPosition = rows.length - index;
            }
        });
    }

    function reinitializeTooltips() {
        // Dispose of existing tooltips to prevent memory leaks
        document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(element => {
            const existingTooltip = bootstrap.Tooltip.getInstance(element);
            if (existingTooltip) {
                existingTooltip.dispose();
            }
        });

        // Initialize new tooltips
        document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(element => {
            new bootstrap.Tooltip(element);
        });
    }
</script>
