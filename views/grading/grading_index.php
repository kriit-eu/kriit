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

    /* Grading status colors for table rows */
    .grading-row.graded {
        background-color: rgba(40, 167, 69, 0.15) !important; /* Light green for graded */
    }

    .grading-row.ungraded {
        background-color: rgba(255, 182, 193, 0.3) !important; /* Pale pink for ungraded */
    }

    /* Ensure table cells maintain transparent background within colored rows */
    .grading-row.graded td,
    .grading-row.ungraded td {
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

    /* Teacher notes bubble styling */
    .teacher-notes-bubble {
        display: inline-flex;
        align-items: center;
        background-color: #fff9e6;
        color: #856404;
        border-radius: 12px;
        padding: 2px 8px;
        font-size: 0.75em;
        margin-left: 8px;
        vertical-align: middle;
        min-width: 24px;
        justify-content: center;
        border: 1px solid #ffd700;
    }

    .teacher-notes-bubble i {
        font-size: 0.8em;
        margin-right: 3px;
    }

    /* Hover effect for teacher notes bubble */
    .teacher-notes-bubble:hover {
        background-color: #fff3cd;
        color: #856404;
        transform: scale(1.05);
        transition: all 0.2s ease;
        border-color: #ffe066;
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

    /* Graded row styling - opacity removed to keep colors visible */
    .grading-row.graded {
        /* Background color is now handled by the grading status colors above */
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
        #grading-table th:first-child { /* "#" column */
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
            text-overflow: ellipsis; /* show "…" when needed */
        }
    }

    /* Image upload and preview styles */
    .message-image {
        max-width: 100%;
        height: auto;
        border-radius: 8px;
        margin: 10px 0;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    }

    #newMessageContent.image-paste-active {
        border-color: #007bff !important;
        box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25) !important;
        background-color: #f8f9ff !important;
    }

    .image-upload-hint {
        font-size: 0.875rem;
        color: #6c757d;
        margin-top: 4px;
    }

    .drag-drop-zone {
        position: relative;
        border: 2px dashed #dee2e6;
        border-radius: 8px;
        padding: 20px;
        text-align: center;
        transition: all 0.3s ease;
        background-color: #f8f9fa;
    }

    .drag-drop-zone.drag-over {
        border-color: #007bff;
        background-color: #f0f8ff;
        transform: scale(1.01);
    }

    .upload-item {
        background-color: #f8f9fa;
        border: 1px solid #dee2e6;
        border-radius: 6px;
        padding: 8px 12px;
        margin-bottom: 8px;
    }

    .upload-item.success {
        background-color: #d4edda;
        border-color: #c3e6cb;
        color: #155724;
    }

    .upload-item.error {
        background-color: #f8d7da;
        border-color: #f5c6cb;
        color: #721c24;
    }

    .file-info {
        font-size: 0.875rem;
        color: #6c757d;
    }

    /* Split editor styles */
    .editor-wrapper, .preview-wrapper {
        border: 1px solid #dee2e6;
        border-radius: 0.375rem;
        overflow: hidden;
    }

    .editor-header, .preview-header {
        background-color: #f8f9fa;
        padding: 8px 12px;
        border-bottom: 1px solid #dee2e6;
        font-weight: 500;
    }

    .editor-wrapper textarea {
        border: none;
        border-radius: 0;
        box-shadow: none;
        padding: 12px;
    }

    .editor-wrapper textarea:focus {
        border: none;
        box-shadow: none;
    }

    #messagePreview {
        border: none;
        border-radius: 0;
        padding: 12px;
        font-family: inherit;
        line-height: 1.5;
    }

    #messagePreview img {
        max-width: 100%;
        height: auto;
        border-radius: 6px;
        margin: 8px 0;
    }

    #messagePreview h1, #messagePreview h2, #messagePreview h3, #messagePreview h4 {
        margin-top: 1rem;
        margin-bottom: 0.5rem;
    }

    #messagePreview code {
        background-color: #f1f3f4;
        padding: 2px 4px;
        border-radius: 3px;
        font-size: 0.9em;
    }

    #messagePreview pre {
        background-color: #f8f9fa;
        padding: 1rem;
        border-radius: 6px;
        border-left: 4px solid #007bff;
        overflow-x: auto;
    }

    /* Dynamic resizing styles */
    #newMessageContent {
        transition: height 0.2s ease;
    }

    #messagePreview {
        transition: height 0.2s ease;
    }

    /* Mobile responsiveness */
    @media (max-width: 768px) {
        .editor-wrapper, .preview-wrapper {
            margin-bottom: 1rem;
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
                    <?php
                        // Determine grading status based on whether the submission has been graded
                        $gradingStatus = (!empty($submission['userGrade']) || !empty($submission['Hinnatud'])) ? 'graded' : 'ungraded';
                    ?>
                    <tr class="grading-row <?= $gradingStatus ?>"
                        data-assignment-id="<?= $submission['assignmentId'] ?>"
                        data-user-id="<?= $submission['userId'] ?>"
                        data-assignment-name="<?= htmlspecialchars($submission['Ülesanne']) ?>"
                        data-student-name="<?= htmlspecialchars($submission['Õpilane']) ?>"
                        data-solution-url="<?= htmlspecialchars($submission['solutionUrl'] ?? '') ?>"
                        data-assignment-instructions="<?= htmlspecialchars($submission['assignmentInstructions'] ?? '') ?>"
                        data-comments="<?= htmlspecialchars($submission['comments'] ?? '[]') ?>"
                        data-current-grade="<?= htmlspecialchars($submission['userGrade'] ?? '') ?>"
                        data-criteria="<?= htmlspecialchars($submission['criteria'] ?? '[]') ?>"
                         data-assignment-involves-openapi="<?= htmlspecialchars($submission['assignmentInvolvesOpenApi'] ?? '0') ?>"
                        data-sort-position="<?= $index + 1 ?>"
                        data-sort-submitted="<?= $submission['Esitatud'] ? strtotime($submission['Esitatud']) : 1 ?>"
                        data-sort-graded="<?= $submission['Hinnatud'] ? strtotime($submission['Hinnatud']) : 1 ?>"
                        data-sort-age="<?= $submission['Vanus'] ?: 0 ?>"
                        data-sort-difference="<?= $submission['Vahe'] ?: 0 ?>"
                        data-sort-student="<?= htmlspecialchars($submission['Õpilane']) ?>"
                        data-sort-subject="<?= htmlspecialchars($submission['Aine']) ?>"
                        data-sort-grade="<?= htmlspecialchars($submission['userGrade'] ?? '') ?>">
                        <td><?= $index + 1 ?></td>
                        <td data-bs-toggle="tooltip" data-bs-placement="top"
                            title="<?= htmlspecialchars($submission['Õpilane']) ?>"><?= $submission['Õpilane'] ?></td>
                        <td data-bs-toggle="tooltip" data-bs-placement="top"
                            title="<?= htmlspecialchars($submission['Aine'] . ' - ' . $submission['Ülesanne']) ?>">
                            <span class="id-badge"><?= $submission['assignmentId']?></span>
                            <a href="grading/assignments/<?= $submission['assignmentId'] ?>/students/<?= $submission['userId'] ?>"
                               onclick="event.preventDefault(); openGradingModal(this.closest('tr')); return false;"
                               class="assignment-name"
                               style="text-decoration: none; color: inherit;"><?= $submission['Ülesanne'] ?></a>
                            <?php if ($submission['commentCount'] > 0): ?>
                                <span class="comment-bubble" data-bs-toggle="tooltip" data-bs-placement="top" title="Kommentaarid: <?= $submission['commentCount'] ?>">
                                        <i class="fas fa-comment"></i>
                                        <span class="comment-count"><?= $submission['commentCount'] ?></span>
                                    </span>
                            <?php endif; ?>
                            <?php if (($this->auth->userIsTeacher || $this->auth->userIsAdmin) && $submission['teacherNotesCount'] > 0): ?>
                                <span class="teacher-notes-bubble" data-bs-toggle="tooltip" data-bs-placement="top" title="Õpetaja märkmed: <?= $submission['teacherNotesCount'] ?>">
                                        <i class="fas fa-sticky-note"></i>
                                        <span class="comment-count"><?= $submission['teacherNotesCount'] ?></span>
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
                            title="<?= $submission['Vahe'] ? 'Hindamine võttis ' . $submission['Vahe'] . ' päeva' : 'Sama päeva jooksul hinnatud' ?>"><?= $submission['Vahe'] !== null ? $submission['Vahe'] : '' ?></td>
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
                    <div id="openApiSection"><?php 
                        $isStudent = false; 
                        $assignment = ['assignmentInvolvesOpenApi' => false]; // Will be updated by JS
                        include 'views/modules/openapi_module.php'; 
                    ?></div>
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
                    
                    <!-- Split view: Editor and Preview -->
                    <div class="row">
                        <!-- Text Editor Column -->
                        <div class="col-md-6">
                            <div class="editor-wrapper">
                                <div class="editor-header">
                                    <small class="text-muted">
                                        <i class="fas fa-edit"></i> Redaktor
                                    </small>
                                </div>
                                <textarea class="form-control" id="newMessageContent" rows="8"
                                          placeholder="Kirjuta kommentaar õpilasele... (pildide kleepimiseks kasuta Ctrl+V)"
                                          style="resize: none; min-height: 200px; overflow: hidden;"></textarea>
                            </div>
                        </div>
                        
                        <!-- Preview Column -->
                        <div class="col-md-6">
                            <div class="preview-wrapper">
                                <div class="preview-header">
                                    <small class="text-muted">
                                        <i class="fas fa-eye"></i> Eelvaade
                                    </small>
                                </div>
                                <div id="messagePreview" class="form-control" 
                                     style="min-height: 200px; background-color: #f8f9fa; overflow-y: hidden; word-wrap: break-word;">
                                    <div class="text-muted text-center p-3">
                                        <i class="fas fa-eye-slash"></i><br>
                                        Eelvaade ilmub siia...
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Image Upload Progress -->
                    <div id="imageUploadProgress" class="mt-2 d-none">
                        <div class="card border-primary">
                            <div class="card-body p-3">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <h6 class="mb-0">Pildi üleslaadimine</h6>
                                    <button type="button" class="btn btn-sm btn-outline-danger" id="cancelUpload">
                                        <i class="fas fa-times"></i> Tühista
                                    </button>
                                </div>
                                <div class="progress mb-2" style="height: 8px;">
                                    <div class="progress-bar progress-bar-striped progress-bar-animated" 
                                         id="uploadProgressBar" role="progressbar" style="width: 0%"></div>
                                </div>
                                <div class="d-flex align-items-center">
                                    <div class="spinner-border spinner-border-sm me-2" role="status">
                                        <span class="visually-hidden">Laadimine...</span>
                                    </div>
                                    <small class="text-muted" id="uploadStatusText">Alustamine...</small>
                                </div>
                                <div id="uploadResults" class="mt-2"></div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Image Upload Zone -->
                    <div class="mt-2">
                        <div class="row">
                            <div class="col-md-8">
                                <div class="form-text">
                                    <small class="text-muted">
                                        💡 <strong>Näpunäide:</strong> Kopeeri ükskõik milline pilt ja kleebi see otse redaktorisse (Ctrl+V)! 
                                        Pildid lisatakse automaatselt Markdown-vormingus.
                                    </small>
                                </div>
                            </div>
                            <div class="col-md-4 text-end">
                                <button type="button" class="btn btn-outline-primary btn-sm" id="selectImagesBtn">
                                    <i class="fas fa-image"></i> Vali pildid
                                </button>
                                <input type="file" id="imageFileInput" multiple accept="image/*" style="display: none;">
                            </div>
                        </div>
                    </div>
                    
                    <div class="invalid-feedback" id="messageError"></div>
                </div>

                <!-- Teacher Private Notes -->
                <?php include __DIR__ . '/../modules/teacher_notes_module.php'; ?>
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
    const userIsAdmin = <?= $this->auth->userIsAdmin ? 'true' : 'false' ?>;
    
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

        // Initialize grading status styling for existing graded rows
        initializeGradingStatusStyling();

        // Handle the "Show graded" toggle
        const showGradedToggle = document.getElementById('showGradedToggle');
        if (showGradedToggle) {
            showGradedToggle.addEventListener('change', function () {
                const url = new URL(window.location.href);
                url.searchParams.set('showGraded', this.checked ? '1' : '0');
                window.location.href = url.toString();
            });
        }

        // Initialize image pasting functionality
        initializeImagePasting();
        
        // Initialize real-time preview
        initializePreview();
    });

    let currentAssignmentId = null;
    let currentUserId = null;
    let gradingModalInstance = null;
    let currentImageId = null; // Track uploaded image ID

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
        const assignmentInvolvesOpenApi = row.dataset.assignmentInvolvesOpenapi;

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
        const saveBtnText = document.getElementById('saveBtnText');
        const saveBtnSpinner = document.getElementById('saveBtnSpinner');

        saveBtn.disabled = true;
        // Reset button state to ensure clean state when opening modal
        saveBtnText.textContent = 'Salvesta';
        saveBtnSpinner.classList.add('d-none');

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

        // Update OpenAPI section visibility
        const openApiSection = document.getElementById('openApiSection');
        const openApiButton = document.getElementById('openApiButton');
        if (assignmentInvolvesOpenApi === '1' && openApiButton) {
            openApiButton.style.display = 'inline-block';
        } else if (openApiButton) {
            openApiButton.style.display = 'none';
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

        // Load teacher notes
        loadTeacherNotes(assignmentId, userId);

        // Clear new message form
        document.getElementById('newMessageContent').value = '';
        document.getElementById('messageError').textContent = '';
        
        // Clear image tracking
        currentImageId = null;

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

            // Reset save button state to ensure clean state for next modal
            const saveBtn = document.getElementById('saveBtn');
            const saveBtnText = document.getElementById('saveBtnText');
            const saveBtnSpinner = document.getElementById('saveBtnSpinner');

            if (saveBtn && saveBtnText && saveBtnSpinner) {
                saveBtn.disabled = true;
                saveBtnText.textContent = 'Salvesta';
                saveBtnSpinner.classList.add('d-none');
            }
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
        
        // Add image ID if present
        if (currentImageId) {
            formData.append('imageId', currentImageId);
        }

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
                    
                    // Clear image tracking
                    currentImageId = null;
                    
                    loadMessages(currentAssignmentId, currentUserId);

                    // Update the table row to reflect the new grade and timestamps
                    updateTableRowGrade(currentAssignmentId, currentUserId, selectedGrade);

                    // Update the "Hinnatud" and "Vahe" columns with the new data
                    updateTableRowTimestamps(currentAssignmentId, currentUserId, data.data.gradedAt, data.data.daysDifference);

                    // Add grade badge to table row
                    addGradeBadgeToTableRow(currentAssignmentId, currentUserId, selectedGrade);

                    // Reset button state before closing modal to ensure clean state for next modal
                    saveBtn.disabled = false;
                    saveBtnText.textContent = 'Salvesta';
                    saveBtnSpinner.classList.add('d-none');

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

                    // Keep the row visible after grading - it should only be hidden on page refresh
                    // when the "Show graded" toggle determines what gets loaded from the server
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

    function updateTableRowTimestamps(assignmentId, userId, gradedAt, daysDifference) {
        try {
            // Find the specific table row
            const targetRow = document.querySelector('.grading-row[data-assignment-id="' + assignmentId + '"][data-user-id="' + userId + '"]');

            if (!targetRow) {
                console.warn('Table row not found for updating timestamps:', {assignmentId, userId});
                return;
            }

            // Update "Hinnatud" column (6th column)
            const gradedCell = targetRow.querySelector('td:nth-child(6)');
            if (gradedCell && gradedAt) {
                const gradedDate = new Date(gradedAt);
                const formattedDate = gradedDate.toLocaleDateString('et-EE', {
                    day: '2-digit',
                    month: '2-digit',
                    year: '2-digit'
                });
                const formattedTime = gradedDate.toLocaleTimeString('et-EE', {
                    hour: '2-digit',
                    minute: '2-digit'
                });

                gradedCell.innerHTML = `<span class="id-badge"><strong>${formattedDate}</strong> ${formattedTime}</span>`;

                // Update tooltip
                const fullDate = gradedDate.toLocaleDateString('et-EE', {
                    day: '2-digit',
                    month: '2-digit',
                    year: 'numeric'
                }) + ' ' + formattedTime;
                gradedCell.setAttribute('title', `Hinnatud: ${fullDate}`);

                // Update data attribute for sorting
                targetRow.dataset.sortGraded = Math.floor(gradedDate.getTime() / 1000);
            }

            // Update "Vahe" column (7th column)
            const differenceCell = targetRow.querySelector('td:nth-child(7)');
            if (differenceCell) {
                differenceCell.textContent = daysDifference !== null ? daysDifference : '';

                // Update tooltip
                if (daysDifference) {
                    differenceCell.setAttribute('title', `Hindamine võttis ${daysDifference} päeva`);
                } else {
                    differenceCell.setAttribute('title', 'Sama päeva jooksul hinnatud');
                }

                // Update data attribute for sorting
                targetRow.dataset.sortDifference = daysDifference || 0;
            }

            // Reinitialize tooltips for the updated cells
            if (gradedCell) {
                // Dispose of old tooltip if it exists
                const existingTooltip = bootstrap.Tooltip.getInstance(gradedCell);
                if (existingTooltip) {
                    existingTooltip.dispose();
                }
                new bootstrap.Tooltip(gradedCell);
            }

            if (differenceCell) {
                // Dispose of old tooltip if it exists
                const existingTooltip = bootstrap.Tooltip.getInstance(differenceCell);
                if (existingTooltip) {
                    existingTooltip.dispose();
                }
                new bootstrap.Tooltip(differenceCell);
            }

        } catch (error) {
            console.error('Error updating table row timestamps:', error);
        }
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
            targetRow.classList.remove('ungraded');
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
                return response.json();
            })
            .then(data => {
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
                let imageHtml = '';
                if (message.imageId) {
                    imageHtml = `<div class="mt-2">${displayMessageImage(message.imageId)}</div>`;
                }
                messagesHtml += `
                    <div class="message-item">
                        <div class="d-flex justify-content-between">
                            <span class="message-author">${message.userName || 'Tundmatu'}</span>
                            <span class="message-time">${messageDate}</span>
                        </div>
                        <div class="message-content">${parseMarkdown(message.content)}</div>
                        ${imageHtml}
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

        // Images - handle before links to avoid conflicts
        html = html.replace(/!\[([^\]]*)\]\(([^)]+)\)/g, function(match, alt, src) {
            return '<img src="' + src + '" alt="' + alt + '" class="message-image img-fluid rounded" style="max-height: 300px; cursor: pointer;" onclick="window.open(this.src, \'_blank\')">';
        });

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
                // Handle unsubmitted assignments (timestamp = 1) - they should be considered "very old"
                if (valueA === 1 && valueB === 1) return 0;
                if (valueA === 1) return direction === 'asc' ? -1 : 1;
                if (valueB === 1) return direction === 'asc' ? 1 : -1;
                break;

            case 'graded':
                valueA = parseInt(rowA.dataset.sortGraded);
                valueB = parseInt(rowB.dataset.sortGraded);
                // Handle ungraded assignments (timestamp = 1) - they should be considered "very old"
                if (valueA === 1 && valueB === 1) return 0;
                if (valueA === 1) return direction === 'asc' ? -1 : 1;
                if (valueB === 1) return direction === 'asc' ? 1 : -1;
                break;

            case 'difference':
                valueA = parseInt(rowA.dataset.sortDifference);
                valueB = parseInt(rowB.dataset.sortDifference);
                // Handle null difference (0) - means graded same day, should be considered "fastest"
                // No special handling needed as 0 is naturally the smallest value
                break;

            case 'age':
                valueA = parseInt(rowA.dataset.sortAge);
                valueB = parseInt(rowB.dataset.sortAge);
                // Handle null age (0) - means submitted today, should be considered "newest"
                // No special handling needed as 0 is naturally the smallest value
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

    function initializeGradingStatusStyling() {
        // Check all grading rows and ensure they have the correct grading status classes
        const gradingRows = document.querySelectorAll('.grading-row');

        gradingRows.forEach(row => {
            const currentGrade = row.dataset.currentGrade;
            const gradedTimestamp = row.dataset.sortGraded;

            // Check if the row has been graded (has a grade or graded timestamp that's not 1)
            const isGraded = (currentGrade && currentGrade.trim() !== '') ||
                           (gradedTimestamp && gradedTimestamp !== '1');

            if (isGraded) {
                row.classList.remove('ungraded');
                row.classList.add('graded');
            } else {
                row.classList.remove('graded');
                row.classList.add('ungraded');
            }
        });
    }

    // Image pasting functionality
    function initializeImagePasting() {
        const textarea = document.getElementById('newMessageContent');
        const uploadProgress = document.getElementById('imageUploadProgress');
        const uploadProgressBar = document.getElementById('uploadProgressBar');
        const uploadStatusText = document.getElementById('uploadStatusText');
        const uploadResults = document.getElementById('uploadResults');
        const cancelUploadBtn = document.getElementById('cancelUpload');
        const selectImagesBtn = document.getElementById('selectImagesBtn');
        const imageFileInput = document.getElementById('imageFileInput');
        
        let currentUploads = [];
        let uploadCounter = 0;

        // Supported file types
        const supportedTypes = [
            'image/jpeg', 'image/jpg', 'image/png', 'image/gif', 
            'image/webp', 'image/avif', 'image/bmp', 'image/tiff'
        ];

        // File selection button
        selectImagesBtn.addEventListener('click', () => {
            imageFileInput.click();
        });

        // File input change handler
        imageFileInput.addEventListener('change', (e) => {
            const files = Array.from(e.target.files);
            if (files.length > 0) {
                handleMultipleFiles(files);
            }
            e.target.value = ''; // Reset input
        });

        // Handle paste events
        textarea.addEventListener('paste', function(e) {
            const items = (e.clipboardData || e.originalEvent.clipboardData).items;
            const imageFiles = [];
            
            for (let item of items) {
                if (item.type.indexOf('image') !== -1) {
                    imageFiles.push(item.getAsFile());
                }
            }
            
            if (imageFiles.length > 0) {
                e.preventDefault();
                handleMultipleFiles(imageFiles);
            }
        });

        // Enhanced drag and drop
        textarea.addEventListener('dragover', function(e) {
            e.preventDefault();
            e.stopPropagation();
            textarea.classList.add('image-paste-active');
        });

        textarea.addEventListener('dragenter', function(e) {
            e.preventDefault();
            e.stopPropagation();
            textarea.classList.add('image-paste-active');
        });

        textarea.addEventListener('dragleave', function(e) {
            e.preventDefault();
            e.stopPropagation();
            // Only remove class if really leaving the textarea
            if (!textarea.contains(e.relatedTarget)) {
                textarea.classList.remove('image-paste-active');
            }
        });

        textarea.addEventListener('drop', function(e) {
            e.preventDefault();
            e.stopPropagation();
            textarea.classList.remove('image-paste-active');
            
            const files = Array.from(e.dataTransfer.files).filter(file => 
                file.type.indexOf('image') !== -1
            );
            
            if (files.length > 0) {
                console.log(`Dropped ${files.length} image(s)`);
                handleMultipleFiles(files);
            }
        });

        // Cancel upload functionality
        cancelUploadBtn.addEventListener('click', () => {
            cancelAllUploads();
        });

        function validateFile(file) {
            const errors = [];
            
            // Check file type
            if (!supportedTypes.includes(file.type)) {
                errors.push(`Toetamata failitüüp: ${file.type}`);
            }
            
            // Check file size (10MB limit)
            if (file.size > 10 * 1024 * 1024) {
                const sizeMB = (file.size / (1024 * 1024)).toFixed(1);
                errors.push(`Fail on liiga suur: ${sizeMB}MB (max 10MB)`);
            }
            
            return errors;
        }

        function formatFileSize(bytes) {
            if (bytes === 0) return '0 B';
            const k = 1024;
            const sizes = ['B', 'KB', 'MB', 'GB'];
            const i = Math.floor(Math.log(bytes) / Math.log(k));
            return parseFloat((bytes / Math.pow(k, i)).toFixed(1)) + ' ' + sizes[i];
        }

        function handleMultipleFiles(files) {
            if (files.length === 0) return;
                       
            // Show upload progress container
            uploadProgress.classList.remove('d-none');
            uploadResults.innerHTML = '';
            uploadProgressBar.style.width = '0%';
            uploadStatusText.textContent = `Kontrollin ${files.length} faili...`;
            
            // Validate all files first
            const validFiles = [];
            const invalidFiles = [];
            
            files.forEach(file => {
                const errors = validateFile(file);
                if (errors.length === 0) {
                    validFiles.push(file);
                } else {
                    invalidFiles.push({file, errors});
                }
            });
            
            // Show validation results
            if (invalidFiles.length > 0) {
                invalidFiles.forEach(({file, errors}) => {
                    const errorDiv = document.createElement('div');
                    errorDiv.className = 'upload-item error';
                    errorDiv.innerHTML = `
                        <div class="d-flex justify-content-between">
                            <span><i class="fas fa-times"></i> ${file.name}</span>
                            <span class="file-info">${formatFileSize(file.size)}</span>
                        </div>
                        <div class="text-danger small mt-1">${errors.join(', ')}</div>
                    `;
                    uploadResults.appendChild(errorDiv);
                });
            }
            
            if (validFiles.length === 0) {
                uploadStatusText.textContent = 'Ühtegi kehtivat pilti ei leitud';
                setTimeout(() => {
                    uploadProgress.classList.add('d-none');
                }, 3000);
                return;
            }
            
            // Upload valid files
            uploadStatusText.textContent = `Laen üles ${validFiles.length} pilti...`;
            uploadFilesSequentially(validFiles);
        }

        async function uploadFilesSequentially(files) {
            const totalFiles = files.length;
            let completedFiles = 0;
            
            for (let i = 0; i < files.length; i++) {
                const file = files[i];
                const uploadId = ++uploadCounter;
                
                // Create upload item in results
                const uploadItem = document.createElement('div');
                uploadItem.className = 'upload-item';
                uploadItem.id = `upload-${uploadId}`;
                uploadItem.innerHTML = `
                    <div class="d-flex justify-content-between align-items-center">
                        <span><i class="fas fa-spinner fa-spin"></i> ${file.name}</span>
                        <span class="file-info">${formatFileSize(file.size)}</span>
                    </div>
                    <div class="progress mt-2" style="height: 4px;">
                        <div class="progress-bar" id="progress-${uploadId}" style="width: 0%"></div>
                    </div>
                `;
                uploadResults.appendChild(uploadItem);
                
                try {
                    const result = await uploadSingleFile(file, uploadId);
                    
                    // Update item to success state
                    uploadItem.className = 'upload-item success';
                    uploadItem.innerHTML = `
                        <div class="d-flex justify-content-between align-items-center">
                            <span><i class="fas fa-check"></i> ${file.name}</span>
                            <span class="file-info">${formatFileSize(result.processedSize || file.size)}</span>
                        </div>
                        ${result.compressionSavings ? `<div class="text-success small mt-1">
                            <i class="fas fa-compress-arrows-alt"></i> Kompressioon: ${result.compressionSavings}% väiksem
                        </div>` : ''}
                    `;
                    
                    // Insert markdown into textarea
                    insertImageMarkdown(result.imageId, file.name);
                    
                } catch (error) {
                    console.error('Upload failed:', error);
                    
                    // Update item to error state
                    uploadItem.className = 'upload-item error';
                    uploadItem.innerHTML = `
                        <div class="d-flex justify-content-between">
                            <span><i class="fas fa-times"></i> ${file.name}</span>
                            <span class="file-info">${formatFileSize(file.size)}</span>
                        </div>
                        <div class="text-danger small mt-1">${error.message}</div>
                    `;
                }
                
                completedFiles++;
                const overallProgress = (completedFiles / totalFiles) * 100;
                uploadProgressBar.style.width = overallProgress + '%';
                uploadStatusText.textContent = `${completedFiles}/${totalFiles} pilti valmis`;
            }
            
            // Hide progress after completion
            setTimeout(() => {
                uploadProgress.classList.add('d-none');
            }, 3000);
        }

        function uploadSingleFile(file, uploadId) {
            return new Promise((resolve, reject) => {
                const formData = new FormData();
                formData.append('image', file);
                
                const xhr = new XMLHttpRequest();
                
                // Track this upload for cancellation
                currentUploads.push(xhr);
                
                // Progress tracking
                xhr.upload.addEventListener('progress', (e) => {
                    if (e.lengthComputable) {
                        const progress = (e.loaded / e.total) * 100;
                        const progressBar = document.getElementById(`progress-${uploadId}`);
                        if (progressBar) {
                            progressBar.style.width = progress + '%';
                        }
                    }
                });
                
                xhr.onload = function() {
                    // Remove from tracking
                    currentUploads = currentUploads.filter(u => u !== xhr);
                    
                    if (xhr.status === 200) {
                        try {
                            const response = JSON.parse(xhr.responseText);
                            if (response.status === 200) {
                                // Calculate compression savings if applicable
                                const originalSize = file.size;
                                const processedSize = response.data.processedSize;
                                const savings = originalSize > processedSize ? 
                                    Math.round((1 - processedSize / originalSize) * 100) : 0;
                                
                                resolve({
                                    imageId: response.data.imageId,
                                    processedSize: processedSize,
                                    compressionSavings: savings > 5 ? savings : null // Only show if significant
                                });
                            } else {
                                reject(new Error(response.message || 'Upload failed'));
                            }
                        } catch (e) {
                            reject(new Error('Invalid server response'));
                        }
                    } else {
                        reject(new Error(`Server error: ${xhr.status}`));
                    }
                };
                
                xhr.onerror = function() {
                    currentUploads = currentUploads.filter(u => u !== xhr);
                    reject(new Error('Network error'));
                };
                
                xhr.onabort = function() {
                    currentUploads = currentUploads.filter(u => u !== xhr);
                    reject(new Error('Upload cancelled'));
                };
                
                xhr.open('POST', '<?= BASE_URL ?>images/upload');
                xhr.send(formData);
            });
        }

        function insertImageMarkdown(imageId, fileName) {
            const imageMarkdown = `![${fileName}](images/${imageId})`;
            
            // Get current cursor position and insert markdown
            const cursorPos = textarea.selectionStart;
            const textBefore = textarea.value.substring(0, cursorPos);
            const textAfter = textarea.value.substring(cursorPos);
            
            // Add newlines if needed for proper formatting
            const needsNewlineBefore = textBefore.length > 0 && !textBefore.endsWith('\n');
            const needsNewlineAfter = textAfter.length > 0 && !textAfter.startsWith('\n');
            
            const finalMarkdown = 
                (needsNewlineBefore ? '\n' : '') + 
                imageMarkdown + 
                (needsNewlineAfter ? '\n' : '');
            
            textarea.value = textBefore + finalMarkdown + textAfter;
            
            // Move cursor after the inserted text
            const newCursorPos = cursorPos + finalMarkdown.length;
            textarea.selectionStart = textarea.selectionEnd = newCursorPos;
            textarea.focus();
            
            // Trigger preview update
            textarea.dispatchEvent(new Event('input'));
        }

        function cancelAllUploads() {
            console.log(`Cancelling ${currentUploads.length} uploads`);
            currentUploads.forEach(xhr => {
                try {
                    xhr.abort();
                } catch (e) {
                    console.error('Error aborting upload:', e);
                }
            });
            currentUploads = [];
            
            uploadStatusText.textContent = 'Üleslaadimine tühistatud';
            uploadProgressBar.style.width = '0%';
            
            setTimeout(() => {
                uploadProgress.classList.add('d-none');
            }, 2000);
        }
    }

    // Function to display images in messages
    function displayMessageImage(imageId) {
        if (!imageId) return '';
        return `<img src="images/${imageId}" class="message-image" alt="Attached image">`;
    }

    // Auto-resize textarea to fit content (infinite expansion)
    function autoResizeTextarea(textarea) {
        // Reset height to auto to get the correct scrollHeight
        textarea.style.height = 'auto';
        
        // Set minimum height
        const minHeight = 200;
        
        // Calculate new height based on scroll height (no maximum limit)
        let newHeight = Math.max(textarea.scrollHeight, minHeight);
        
        // Always use hidden overflow since we expand to fit content
        textarea.style.overflowY = 'hidden';
        
        // Apply the new height
        textarea.style.height = newHeight + 'px';
        
        return newHeight;
    }
    
    // Global debounce mechanism for all resize operations
    let globalResizeTimeout = null;
    let isGloballyResizing = false;
    
    // Debounced resize function that coordinates all resize calls
    function debouncedResize(preview, source = 'unknown') {
        
        // If we're already in a resize operation, ignore this call
        if (isGloballyResizing) {
            return;
        }
        
        // Clear any pending resize operation
        if (globalResizeTimeout) {
            clearTimeout(globalResizeTimeout);
        }
        
        globalResizeTimeout = setTimeout(() => {
            isGloballyResizing = true;
            actualResizePreview(preview);
            
            // Reset flag after operation completes
            setTimeout(() => {
                isGloballyResizing = false;
            }, 200);
            
            globalResizeTimeout = null;
        }, 150); // Global debounce delay
    }
    
    // The actual resize implementation (renamed from autoResizePreview)
    function actualResizePreview(preview) {
        
        // Set minimum height only - no maximum limit
        const minHeight = 200;
        
        // Temporarily remove height constraint to measure content
        const originalHeight = preview.style.height;
        const originalOverflow = preview.style.overflowY;
        
        preview.style.height = 'auto';
        preview.style.overflowY = 'hidden';
        
        // Get the actual content height including images
        let contentHeight = preview.scrollHeight;
        
        // Apply minimum height constraint only
        let newHeight = Math.max(contentHeight, minHeight);
        
        // Always allow infinite expansion - no scrolling needed
        preview.style.overflowY = 'hidden';
        
        // Apply the new height
        preview.style.height = newHeight + 'px';
        
        return newHeight;
    }
    
    // Keep the old function name for compatibility but route through debounced version
    function autoResizePreview(preview) {
        debouncedResize(preview, 'autoResizePreview');
    }
    
    // Sync heights between textarea and preview
    function syncElementHeights(textarea, preview) {
        // Use global debouncing instead of local debouncing
        debouncedResize(preview, 'syncElementHeights');
    }
    
    // The actual sync logic without debouncing
    function performElementHeightSync(textarea, preview) {
        // Skip if globally resizing to prevent interference
        if (isGloballyResizing) {
            console.log('Skipping performElementHeightSync - global resize in progress');
            return;
        }
        
        // Get heights for both elements
        const textareaHeight = autoResizeTextarea(textarea);
        
        // Wait for images to load before calculating preview height
        const images = preview.querySelectorAll('img');
        if (images.length > 0) {
            let loadedImages = 0;
            const totalImages = images.length;
            
            const checkAllImagesLoaded = () => {
                if (loadedImages === totalImages) {
                    // All images loaded, now resize preview
                    debouncedResize(preview, 'performElementHeightSync-imageLoad');
                }
            };
            
            // Check each image and set up loading handlers
            images.forEach((img, index) => {
                if (img.complete && img.naturalWidth > 0) {
                    // Image is already loaded
                    loadedImages++;
                } else {
                    // Image is still loading, set up handlers
                    const handleImageLoad = () => {
                        loadedImages++;
                        checkAllImagesLoaded();
                        // Remove event listeners to prevent multiple calls
                        img.removeEventListener('load', handleImageLoad);
                        img.removeEventListener('error', handleImageLoad);
                    };
                    
                    img.addEventListener('load', handleImageLoad);
                    img.addEventListener('error', handleImageLoad);
                }
            });
            
            // If all images were already loaded, resize immediately
            if (loadedImages === totalImages) {
                debouncedResize(preview, 'performElementHeightSync-allLoaded');
            }
            
            // Removed fallback timeout that was causing infinite loops
            
        } else {
            // No images, just resize preview normally
            debouncedResize(preview, 'performElementHeightSync-noImages');
        }
    }

    // Global function to manually trigger resize (useful for debugging and external calls)
    window.manualResizeCommentBoxes = function() {
        console.log('Manual resize triggered');
        const textarea = document.getElementById('newMessageContent');
        const preview = document.getElementById('messagePreview');
        
        if (textarea && preview) {
            syncElementHeights(textarea, preview);
        } else {
            console.warn('Comment boxes not found for manual resize');
        }
    };

    // Real-time preview functionality
    function initializePreview() {
        const textarea = document.getElementById('newMessageContent');
        const preview = document.getElementById('messagePreview');
        
        // Initialize mutation observer for dynamic content changes
        initializePreviewObserver(textarea, preview);
        
        // Update preview on input
        function updatePreview() {
            const content = textarea.value.trim();
            if (content === '') {
                preview.innerHTML = `
                    <div class="text-muted text-center p-3">
                        <i class="fas fa-eye-slash"></i><br>
                        Eelvaade ilmub siia...
                    </div>
                `;
                // Reset to minimum height when empty
                preview.style.height = '200px';
                preview.style.overflowY = 'hidden';
            } else {
                preview.innerHTML = parseMarkdown(content);
            }
            
            // Auto-resize both elements after content update
            // Use a small delay to ensure DOM is fully updated
            setTimeout(() => {
                syncElementHeights(textarea, preview);
            }, 50);
        }
        
        // Update on every keystroke
        textarea.addEventListener('input', function() {
            updatePreview();
        });
        
        // Update on paste (with delays to handle image pasting)
        let pasteUpdateTimeout = null;
        textarea.addEventListener('paste', function() {
            // Clear any pending paste updates
            if (pasteUpdateTimeout) {
                clearTimeout(pasteUpdateTimeout);
            }
            
            // First update immediately for text content
            setTimeout(updatePreview, 10);
            
            // Single delayed update for images (reduced from 3 separate calls)
            pasteUpdateTimeout = setTimeout(() => {
                updatePreview();
                pasteUpdateTimeout = null;
            }, 800); // Single 800ms delay instead of multiple calls
        });
        
        // Handle manual resize of textarea
        textarea.addEventListener('mouseup', function() {
            syncElementHeights(textarea, preview);
        });
        
        // Initial update
        updatePreview();
    }

    // Monitor preview content changes for dynamic resizing
    function initializePreviewObserver(textarea, preview) {
        let resizeTimeout = null;
        let isResizing = false;
        
        // Create a mutation observer to watch for content changes
        const observer = new MutationObserver((mutations) => {
            // Skip if we're currently in a resize operation
            if (isResizing) {
                return;
            }
            
            let shouldResize = false;
            let hasNewImages = false;
            
            mutations.forEach((mutation) => {
                // Check if nodes were added/removed
                if (mutation.type === 'childList') {
                    // Check if any new images were added
                    mutation.addedNodes.forEach((node) => {
                        if (node.nodeType === Node.ELEMENT_NODE) {
                            if (node.tagName === 'IMG' || node.querySelector('img')) {
                                hasNewImages = true;
                            }
                        }
                    });
                    shouldResize = true;
                } else if (mutation.type === 'attributes') {
                    // Only trigger on src changes, not style changes to prevent loops
                    if (mutation.target.tagName === 'IMG' && mutation.attributeName === 'src') {
                        hasNewImages = true;
                        console.log('Image src changed');
                        shouldResize = true;
                    }
                    // Ignore style changes to prevent infinite loops
                }
            });
            
            if (shouldResize) {
                // Clear any pending resize
                if (resizeTimeout) {
                    clearTimeout(resizeTimeout);
                }
                
                // If new images were added, wait a bit longer for them to start loading
                const delay = hasNewImages ? 250 : 100;
                
                resizeTimeout = setTimeout(() => {
                    isResizing = true;
                    debouncedResize(preview, 'mutationObserver');
                    // Reset flag after a short delay
                    setTimeout(() => {
                        isResizing = false;
                    }, 200); // Increased to match global debouncing
                    resizeTimeout = null;
                }, delay);
            }
        });
        
        // Start observing the preview div - removed style from attributeFilter
        observer.observe(preview, {
            childList: true,
            subtree: true,
            attributes: true,
            attributeFilter: ['src'] // Only monitor src changes, not style changes
        });
        
        return observer;
    }
</script>