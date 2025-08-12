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

<?php 
$isStudent = false; // Grading page is for teachers/admins
include __DIR__ . '/../../templates/partials/grading_modal/grading_modal.php'; 
?>

<!-- Load the grading page specific JavaScript -->
<script src="<?= BASE_URL ?>views/grading/grading_index.js"></script>

<script>
    const userIsAdmin = <?= $this->auth->userIsAdmin ? 'true' : 'false' ?>;
</script>
