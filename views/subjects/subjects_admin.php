<script>
    // Visual numbering for criteria rows
    function updateCriteriaNumbers() {
        const rows = document.querySelectorAll('#editCriteriaContainer .criteria-row');
        rows.forEach((row, idx) => {
            const label = row.querySelector('.editable-criterion-label');
            if (label) {
                // Remove any existing number prefix
                label.textContent = label.textContent.replace(/^\d+\.\s*/, '');
                label.textContent = (idx + 1) + '. ' + label.textContent;
            }
        });
    }
</script>
<!-- Delete criterion confirmation modal -->
<div class="modal fade" id="deleteCriterionModal" tabindex="-1" aria-labelledby="deleteCriterionModalLabel"
     aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteCriterionModalLabel">Kriteeriumi kustutamine</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Sulge"></button>
            </div>
            <div class="modal-body">
                <div id="deleteCriterionModalText">Kas oled kindel, et soovid selle kriteeriumi kustutada? Seda ei saa
                    tagasi võtta.
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tühista</button>
                <button type="button" class="btn btn-danger" id="confirmDeleteCriterionBtn">Kustuta</button>
            </div>
        </div>
    </div>
</div>
<style>
    /* Criteria section: add border to each row except when editing */
    #editCriteriaContainer .criteria-row {
        border: 0.5px solid #dee2e6;
        border-radius: 0.375rem;
        background: #f8f9fa;
        color: #212529;
        width: 100%;
        box-sizing: border-box;
        padding: 0 0.25rem;
    }

    #editCriteriaContainer .criteria-row .edit-criterion-input {
        width: 100%;
        max-width: 100%;
        min-width: 0;
        display: block;
    }

    /* Make assignment edit modal even wider than Bootstrap modal-xl */
    .modal-xl {
        max-width: 1200px;
    }

    body {
        background: linear-gradient(135deg, #f5f7fa 0%, #e4e9f2 100%);
        min-height: 100vh;
    }

    #container, .table-responsive {
        background-color: transparent;
    }

    #subject-table {
        background-color: transparent;
        box-shadow: 0 2px 4px rgba(0, 0, 0, .07);
        table-layout: fixed !important;
        border: 1px solid #d8d8d8 !important;
        border-collapse: collapse !important;
        width: 100% !important;
    }

    #subject-table td {
        background-color: #fff;
        border-color: #d8d8d8 !important;
    }

    #subject-table th {
        background-color: #f2f2f2;
        border-color: #d8d8d8 !important;
    }

    .red-cell {
        background-color: rgb(255, 180, 176) !important;
    }

    .yellow-cell {
        background-color: #fff8b3 !important;
    }

    .days-passed {
        font-size: .55em;
    }

    .student-summary-table {
        border-collapse: collapse !important;
        box-shadow: 0 2px 4px rgba(0, 0, 0, .1) !important;
    }

    .student-summary-table th, .student-summary-table td {
        border: 1px solid #dee2e6 !important;
        padding: 8px 12px !important;
    }

    .student-summary-table th {
        background-color: #f2f2f2 !important;
    }

    .student-summary-table td {
        background-color: #fff !important;
    }

    .student-row.selected, .student-row.selected td {
        background-color: #0d6efd !important;
        color: #fff !important;
    }

    .text-center {
        text-align: center;
    }

    .inactive-student {
        opacity: .6;
        font-style: italic;
    }

    .deleted-student {
        opacity: .4;
        text-decoration: line-through;
        color: #888;
    }

    .narrow-name {
        font-size: .55em;
        line-height: 1;
        white-space: nowrap;
        text-align: center;
        font-family: "Arial Narrow", Arial, sans-serif;
        font-stretch: condensed;
        letter-spacing: -.02em;
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
        height: 100%;
    }

    .narrow-name .lastname {
        margin-top: 1px;
        display: block;
    }

    #subject-table th.student-name-header {
        padding: 1px 4px;
        font-weight: 400;
        vertical-align: middle;
        height: 36px;
    }

    .subject-spacer, .subject-spacer td, tr.subject-spacer, tr.subject-spacer td, #subject-table tr.subject-spacer td {
        height: 20px;
        background-color: transparent !important;
        border: none !important;
        box-shadow: none !important;
        outline: none !important;
    }

    .student-name-header, #subject-table td.text-center:not(:first-child) {
        width: 40px !important;
        min-width: 40px !important;
        max-width: 40px !important;
        text-align: center;
        vertical-align: middle;
        padding: 4px 2px;
        box-sizing: border-box;
    }

    #subject-table td:first-child, #subject-table th:first-child {
        width: 60px;
        min-width: 60px;
        max-width: 60px;
        text-align: center;
        vertical-align: middle;
    }

    #subject-table td:nth-child(<?= $this->isStudent ? '1' : '2' ?>), #subject-table th:nth-child(<?= $this->isStudent ? '1' : '2' ?>) {
        width: auto;
        min-width: 160px;
        max-width: none;
        flex-grow: 1;
    }

    .student-view #subject-table {
        width: 100% !important;
        table-layout: fixed !important;
        border-collapse: collapse !important;
    }

    .student-view #subject-table tr {
        display: table-row;
        width: 100%;
    }

    .student-view #subject-table th[colspan="2"] {
        text-align: left;
        padding: 8px 12px;
    }

    .student-view #subject-table td {
        text-align: left !important;
    }

    .student-view #subject-table td:first-child {
        padding: 8px 12px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        text-align: left;
        width: calc(100% - 120px) !important;
    }

    .student-view #subject-table td.text-center {
        text-align: center !important;
        width: 120px !important;
        min-width: 120px !important;
        max-width: 120px !important;
        padding: 4px 8px;
        box-sizing: border-box !important;
    }

    .teacher-view #subject-table {
        width: 100% !important;
    }

    .entry-date {
        color: #664d03;
        font-size: .85em;
        background-color: #fff3cd;
        padding: 2px 5px;
        border-radius: 3px;
        margin-right: 5px;
        text-decoration: none;
    }

    .due-date-badge {
        float: right;
        margin-left: 8px;
    }

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
        padding-right: 10px;
    }

    #subject-table td a {
        display: inline;
        line-height: 1.4;
        text-decoration: none !important;
        font-weight: 500;
    }

    #subject-table td:nth-child(2) {
        padding: 8px 12px;
        width: auto;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        text-align: left;
    }

    #subject-table td a:hover, #subject-table th a:hover {
        text-decoration: underline;
        color: #0056b3;
    }

    #subject-table th a {
        color: inherit;
        text-decoration: none;
    }

    .id-badge {
        background-color: #e9ecef;
        padding: 2px 6px;
        border-radius: 3px;
        font-size: .85em;
        color: #495057;
    }

    #subject-table td[data-grade] {
        text-align: center !important;
        justify-content: center !important;
        align-items: center !important;
        display: table-cell !important;
    }
</style>
<style>
    /* Ensure Bootstrap modal and backdrop are always on top */
    .modal-backdrop.show {
        z-index: 2050 !important;
    }

    .modal.show {
        z-index: 2100 !important;
    }

    /* Ensure the delete confirmation modal and its backdrop are always above the edit modal */
    #deleteCriterionModal.modal.show {
        z-index: 2200 !important;
    }

    #deleteCriterionModal + .modal-backdrop.show {
        z-index: 2150 !important;
    }

    /* Fallback for when Bootstrap inserts the backdrop before the modal */
    body > .modal-backdrop.delete-criterion-backdrop.show {
        z-index: 2150 !important;
    }
</style>
<style>
    /* Criteria trashcan button: gray by default, red bg and white icon on hover */
    .remove-criterion-btn {
        background: none;
        border: none;
        padding: 0.5rem 1.1rem;
        border-radius: 0.375rem;
        transition: background 0.15s;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 2.5rem;
        min-height: 2.2rem;
        overflow: visible;
        position: relative;
    }

    .remove-criterion-btn .fa-trash {
        color: #6c757d; /* Bootstrap secondary */
        transition: color 0.15s;
    }

    .remove-criterion-btn:hover, .remove-criterion-btn:focus {
        background: #dc3545 !important;
    }

    .remove-criterion-btn:hover .fa-trash, .remove-criterion-btn:focus .fa-trash {
        color: #fff;
    }
</style>

<?php if ($this->isTeacherOrAdmin): ?>
    <div class="col text-end mb-3 d-flex justify-content-end align-items-center">
        <?php if (!$this->isStudent): ?>
            <div class="form-check form-switch me-3">
                <input class="form-check-input" type="checkbox"
                       id="showAllToggle" <?= $this->showAll ? 'checked' : '' ?>>
                <label class="form-check-label" for="showAllToggle">Näita kõiki</label>
            </div>
        <?php endif; ?>
        <?php if ($this->auth->userIsAdmin): ?>
            <button class="btn btn-primary" onclick="location.href='admin/subjects'">Muuda</button>
        <?php endif; ?>
    </div>
<?php endif; ?>

<div class="row <?= $this->isStudent ? 'student-view' : 'teacher-view' ?>">
    <?php foreach ($this->groups as $group): ?>
        <h1><?= $group['groupName'] ?></h1>

    <?php if (!$this->isStudent && !empty($group['subjects'])): ?>
        <div class="mb-4">
            <h5>Õpilaste kokkuvõte</h5>
            <table class="table table-bordered student-summary-table"
                   data-group="<?= htmlspecialchars($group['groupName']) ?>" style="width:auto;background-color:#fff;">
                <thead>
                <tr>
                    <th style="cursor:pointer;text-align:left;" onclick="sortStudentTableByElement(this,'name')"><b>Õpilane</b>
                        <i class="fas fa-sort"></i></th>
                    <th style="cursor:pointer;text-align:center;" onclick="sortStudentTableByElement(this,'pending')">
                        <b>Võlad</b> <i class="fas fa-sort"></i></th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($group['students'] as $student): ?>
                    <?php $pending = $group['pendingGrades'][$student['userId']] ?? 0; ?>
                    <tr class="<?= $student['meta']['css'] ?> student-row" data-student-id="<?= $student['userId'] ?>"
                        data-student-name="<?= htmlspecialchars($student['userName']) ?>" style="cursor:pointer;"
                        onclick="toggleStudentFilter(this)">
                        <td data-sort-value="<?= htmlspecialchars($student['userName']) ?>"
                            style="background-color:#fff;">
                            <?= htmlspecialchars($student['userName']) . $student['meta']['status'] ?>
                        </td>
                        <td data-sort-value="<?= $pending ?>" class="text-center"
                            style="background-color:#fff;color:<?= $pending > 0 ? '#dc3545' : '#28a745' ?>;font-weight:bold;">
                            <?= $pending ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>


        <!-- Clean up Bootstrap color classes in criteria rows after modal render -->
        <script>
            function cleanCriteriaRowColors() {
                document.querySelectorAll('#editCriteriaContainer .criteria-row').forEach(row => {
                    row.querySelectorAll('.form-check-label, .editable-criterion-label, *').forEach(el => {
                        el.classList.remove('text-secondary', 'text-muted', 'text-light', 'text-dark');
                        el.style.color = '#212529';
                        el.style.fontWeight = 'bold';
                    });
                });
            }

            if (window.openEditAssignmentModal) {
                const origOpenEditAssignmentModal = window.openEditAssignmentModal;
                window.openEditAssignmentModal = function () {
                    origOpenEditAssignmentModal.apply(this, arguments);
                    setTimeout(cleanCriteriaRowColors, 100);
                };
            }
        </script>


        <div class="table-responsive">
            <?php foreach ($group['subjects'] as $index => $subject): ?>
                <?php if ($index > 0): ?>
                    <div style="height:20px;width:100%;"></div><?php endif; ?>

                <table id="subject-table" class="table table-bordered">
                    <tr data-href="subjects/<?= $subject['subjectId'] ?>">
                        <?php if (!$this->isStudent): ?>
                            <th class="text-center"><b>ID</b></th><?php endif; ?>
                        <th <?= $this->isStudent ? 'colspan="2"' : '' ?>
                            <b>
                                <?php if (!empty($subject['subjectExternalId'])): ?>
                                    <a href="https://tahvel.edu.ee/#/journal/<?= $subject['subjectExternalId'] ?>/edit"
                                       target="_blank"><?= $subject['subjectName'] ?></a>
                                <?php else: ?>
                                    <?= $subject['subjectName'] ?>
                                <?php endif; ?>
                                <?php
                                // Calculate unique learning outcomes used in assignments
                                    $usedOv = [];
                                    // Build a map: ÕV number (1-based) => learningOutcomeId
                                    $ovNrToId = [];
                                    if (!empty($subject['learningOutcomes']) && is_array($subject['learningOutcomes'])) {
                                        foreach ($subject['learningOutcomes'] as $lo) {
                                            $nr = isset($lo['learningOutcomeOrderNr']) ? intval($lo['learningOutcomeOrderNr']) + 1 : null;
                                            if ($nr) $ovNrToId[$nr] = $lo['id'];
                                        }
                                    }
                                    if (!empty($subject['assignments'])) {
                                        foreach ($subject['assignments'] as $a) {
                                            if (!empty($a['assignmentName'])) {
                                                if (preg_match('/\(\s*ÕV(\d+(?:,\s*ÕV\d+)*)\s*\)/u', $a['assignmentName'], $m)) {
                                                    $ovStr = $m[1];
                                                    $ovNums = preg_split('/,\s*ÕV/', $ovStr);
                                                    foreach ($ovNums as $num) {
                                                        $num = intval($num);
                                                        if ($num && isset($ovNrToId[$num])) {
                                                            $usedOv[$ovNrToId[$num]] = true;
                                                        }
                                                    }
                                                }
                                            }
                                        }
                                    }
                                $usedCount = count($usedOv);
                                $totalOv = isset($subject['learningOutcomes']) && is_array($subject['learningOutcomes']) ? count($subject['learningOutcomes']) : 0;
                                if ($totalOv > 0) {
                                    $badgeColor = ($usedCount === $totalOv) ? 'bg-success text-white' : 'bg-danger text-white';
                                ?>
                                <span class="badge ms-2 <?= $badgeColor ?>" title="Kasutatud õpiväljundid / kõik õpiväljundid">
                                    <?= $usedCount ?>/<?= $totalOv ?>
                                </span>
                                <?php }
                            ?>
                            </b>
                        </th>
                        <?php if (!$this->isStudent): ?>
                            <?php foreach ($group['students'] as $student): ?>
                                <th data-bs-toggle="tooltip"
                                    title="<?= $student['userName'] . $student['meta']['status'] ?>"
                                    class="student-name-header text-center <?= $student['meta']['css'] ?>">
                                    <div class="narrow-name">
                                        <?= $student['nameSplit']['first'] ?><span
                                                class="lastname"><?= $student['nameSplit']['last'] ?></span>
                                    </div>
                                </th>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tr>

                    <?php foreach ($subject['assignments'] as $assignment): ?>
                        <tr>
                            <?php if (!$this->isStudent): ?>
                                <td class="text-center"><span class="id-badge"><?= $assignment['assignmentId'] ?></span>
                                </td><?php endif; ?>
                            <td colspan="1">
                                <div class="assignment-container">
                                    <div class="assignment-info">
                                        <a href="assignments/<?= $assignment['assignmentId'] ?>?group=<?= urlencode($group['groupName']) ?>">
                                            <?php if (!empty($assignment['assignmentEntryDateFormatted'])): ?><span
                                                    class="entry-date"><?= $assignment['assignmentEntryDateFormatted'] ?></span><?php endif; ?>
                                            <?= $assignment['assignmentName'] ?>
                                        </a>
                                    </div>
                                    <button class="btn btn-link p-0 ms-2 edit-assignment-btn" title="Muuda ülesanne"
                                            data-assignment='<?= htmlspecialchars(json_encode(array_merge($assignment, ["subjectExternalId" => $subject['subjectExternalId']])), ENT_QUOTES, 'UTF-8') ?>'>
                                        <i class="fa fa-pencil-alt"></i>
                                    </button>
                                    <?php if ($assignment['showDueDate']): ?>
                                        <span class="badge <?= $assignment['finalBadgeClass'] ?> due-date-badge"
                                              data-days-remaining="<?= $assignment['daysRemaining'] ?>"
                                              data-is-student=<?= json_encode($this->isStudent) ?>>
                                            <?= $assignment['assignmentDueAt'] ? (new DateTime($assignment['assignmentDueAt']))->format('d.m.y') : 'Pole tähtaega' ?>
                                        </span>
                                    <?php endif; ?>
                                </div>
                            </td>
                            <?php if (!$this->isStudent): ?>
                                <?php foreach ($group['students'] as $student): ?>
                                    <?php
                                    $st = $assignment['assignmentStatuses'][$student['userId']] ?? ['class' => '', 'grade' => '', 'assignmentStatusName' => 'Esitamata', 'tooltipText' => '', 'daysPassed' => 0];
                                    $tooltip = $student['meta']['css'] ? ($student['meta']['deleted'] ? 'Kustutatud õpilane' : 'Mitteaktiivne õpilane') : '';
                                    ?>
                                    <td class="<?= $st['class'] . ' text-center ' . $student['meta']['css'] ?> <?= $st['class'] === 'red-cell' && $st['daysPassed'] > 0 ? 'red-cell-intensity' : '' ?>"
                                        data-bs-toggle="tooltip" data-bs-placement="bottom" data-bs-html="true"
                                        title="<?= nl2br(htmlspecialchars(($tooltip ? "$tooltip\n" : '') . $st['tooltipText'])) ?>"
                                        data-grade="<?= is_numeric($st['grade']) ? intval($st['grade']) : ($st['grade'] ?: '') ?>"
                                        data-student-id="<?= $student['userId'] ?>"
                                        data-is-student="<?= json_encode($this->isStudent) ?>"
                                        data-days-passed="<?= $st['daysPassed'] ?? 0 ?>"
                                        data-url="assignments/<?= $assignment['assignmentId'] ?>?group=<?= urlencode($group['groupName']) ?>">
                                        <?php if ($st['class'] === 'red-cell' && $st['daysPassed']): ?>
                                            <span class="days-passed"><?= $st['daysPassed'] ?>p</span>
                                        <?php else: ?>
                                            <?= $st['grade'] ?>
                                        <?php endif; ?>
                                    </td>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <?php
                                $uid = $this->auth->userId;
                                $st = $assignment['assignmentStatuses'][$uid] ?? ['class' => '', 'assignmentStatusName' => 'Esitamata', 'grade' => '', 'tooltipText' => ''];
                                ?>
                                <td class="<?= $st['class'] ?> text-center" data-bs-toggle="tooltip"
                                    data-bs-placement="bottom" data-bs-html="true"
                                    title="<?= nl2br(htmlspecialchars($st['tooltipText'])) ?>"
                                    data-grade="<?= is_numeric($st['grade']) ? intval($st['grade']) : '' ?>"
                                    data-is-student="true"
                                    data-url="assignments/<?= $assignment['assignmentId'] ?>?group=<?= urlencode($group['groupName']) ?>"
                                    style="width:120px;min-width:120px;max-width:120px;">
                                    <?= $st['assignmentStatusName'] === 'Kontrollimisel' ? 'Kontrollimisel' : ($st['grade'] ?: $st['assignmentStatusName']) ?>
                                </td>
                            <?php endif; ?>
                        </tr>
                    <?php endforeach; ?>
                </table>
            <?php endforeach; ?>
        </div>
    <?php endforeach; ?>
</div>

<?php if (!$this->isStudent): ?>
    <div class="modal fade" id="editAssignmentModal" tabindex="-1" aria-labelledby="editAssignmentModalLabel"
         aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editAssignmentModalLabel">Muuta ülesanne</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="editAssignmentForm">
                        <div class="mb-3">
                            <label for="assignmentName" class="form-label fw-bold">Pealkiri</label>
                            <input type="text" class="form-control" id="assignmentName" name="assignmentName" value=""
                                   maxlength="100">
                            <div class="form-text" id="assignmentNameCounter">0 / 100</div>
                            <div class="invalid-feedback" id="assignmentNameError" style="display:none;">Pealkiri on
                                liiga pikk maksimum tähemärkide pikkus 100
                            </div>
                            <div id="assignmentOvBadges" class="mt-2"></div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Ülesandega seotud õpiväljundid (vajalik automaatseks
                                hindamiseks)</label>
                            <div class="combobox-wrapper" id="assignmentLearningOutcomeCombobox"></div>
                            <style>
                                /* ÕV custom combobox styles from ÕV-selectbox.html */
                                .combobox-wrapper .combobox-item {
                                    user-select: none;
                                    margin-bottom: 1px;
                                    cursor: pointer;
                                }

                                .combobox-wrapper .combobox-item:last-child {
                                    margin-bottom: 0;
                                }

                                .combobox-wrapper .combobox-item:hover {
                                    background-color: rgba(25, 135, 84, 0.1);
                                }

                                .combobox-wrapper .combobox-checkbox {
                                    position: absolute;
                                    opacity: 0;
                                }

                                .combobox-wrapper .combobox-checkbox-visual {
                                    width: 1.25rem;
                                    height: 1.25rem;
                                    transition: all 0.2s;
                                }

                                .combobox-wrapper .combobox-checkbox:checked ~ .combobox-checkbox-visual {
                                    background: var(--bs-success) !important;
                                    border-color: var(--bs-success) !important;
                                }

                                .combobox-wrapper .combobox-item:hover .combobox-checkbox-visual {
                                    border-color: var(--bs-success);
                                    box-shadow: 0 0 0 0.2rem rgba(25, 135, 84, 0.15);
                                }

                                .combobox-wrapper .combobox-checkbox:focus ~ .combobox-checkbox-visual {
                                    box-shadow: 0 0 0 0.25rem rgba(25, 135, 84, 0.25);
                                }

                                .combobox-wrapper .combobox-checkmark {
                                    opacity: 0;
                                    transform: scale(0.8);
                                    transition: all 0.2s;
                                }

                                .combobox-wrapper .combobox-checkmark path {
                                    stroke-dasharray: 15;
                                    stroke-dashoffset: 15;
                                    transition: stroke-dashoffset 0.3s;
                                }

                                .combobox-wrapper .combobox-checkbox:checked ~ .combobox-checkbox-visual .combobox-checkmark {
                                    opacity: 1;
                                    transform: scale(1);
                                }

                                .combobox-wrapper .combobox-checkbox:checked ~ .combobox-checkbox-visual .combobox-checkmark path {
                                    stroke-dashoffset: 0;
                                }

                                .combobox-wrapper .combobox-label {
                                    hyphens: auto;
                                    -webkit-hyphens: auto;
                                    overflow-wrap: break-word;
                                    hyphenate-limit-chars: 6 3 3;
                                    cursor: pointer;
                                }
                            </style>
                        </div>
                        <div class="mb-3">
                            <?php
                            $editorId = 'assignmentInstructionsEditor';
                            $previewId = 'assignmentInstructionsPreview';
                            $fieldName = 'assignmentInstructions';
                            $labelText = 'Instruktsioon';
                            $initialValue = '';
                            include __DIR__ . '/../../templates/partials/markdown_editor.php';
                            ?>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="assignmentEntryDate" class="form-label fw-bold">Sisestamise kuupäev</label>
                                <input type="date" class="form-control" id="assignmentEntryDate"
                                       name="assignmentEntryDate" value="">
                            </div>
                            <div class="col-md-6">
                                <label for="assignmentDueAt" class="form-label fw-bold">Tähtaeg</label>
                                <input type="date" class="form-control" id="assignmentDueAt" name="assignmentDueAt"
                                       value="">
                            </div>
                        </div>
                        <div class="mb-3 form-check">
                            <input type="checkbox" class="form-check-input" id="assignmentInvolvesOpenApi"
                                   name="assignmentInvolvesOpenApi">
                            <label class="form-check-label" for="assignmentInvolvesOpenApi">Ülesandel on OpenAPI</label>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Kriteeriumid</label>
                            <div id="editCriteriaContainer"
                                 style="display: flex; flex-direction: column; gap: 0.5rem; width: 100%;"></div>
                            <div id="addCriterionInlineContainer" style="width: 100%;">
                                <input type="text" class="form-control mt-2" id="newCriterionInput"
                                       placeholder="Lisa uus kriteerium..." autocomplete="off">
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary" onclick="saveEditedAssignment()">Salvesta</button>
                    <button type="button" class="btn btn-secondary" onclick="location.reload()" data-bs-dismiss="modal">
                        Tühista
                    </button>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>

<script src="/assets/js/markdown_editor.js"></script>
<script>

    // Custom Bootstrap modal confirmation for deleting a criterion (define first so all code can use it)
    window.showDeleteCriterionModal = function ({row, onDelete}) {
        // Ensure the confirmation modal's backdrop is always above the edit modal's backdrop
        const modalEl = document.getElementById('deleteCriterionModal');
        if (!modalEl) {
            alert('Delete modal not found in DOM!');
            return;
        }
        const confirmBtn = document.getElementById('confirmDeleteCriterionBtn');
        // Remove any previous click handler
        confirmBtn.onclick = null;
        // Set up new click handler
        confirmBtn.onclick = function () {
            if (typeof onDelete === 'function') onDelete();
            const modal = bootstrap.Modal.getInstance(modalEl);
            if (modal) modal.hide();
        };
        // Show modal
        if (typeof bootstrap === 'undefined' || !bootstrap.Modal) {
            alert('Bootstrap JS is not loaded!');
            return;
        }
        const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
        if (!modal) {
            alert('Could not create Bootstrap modal instance!');
            return;
        }
        modal.show();
        // After modal is shown, bump the backdrop z-index if needed
        setTimeout(function () {
            // Find all visible backdrops
            var backdrops = Array.from(document.querySelectorAll('.modal-backdrop.show'));
            if (backdrops.length > 1) {
                // Assume the last one is for the confirmation modal
                var confBackdrop = backdrops[backdrops.length - 1];
                confBackdrop.style.zIndex = '2150';
                confBackdrop.classList.add('delete-criterion-backdrop');
            }
        }, 10);
    };

    // Inject current user info for assignment save
    window.teacherId = <?= json_encode($this->auth->userId) ?>;
    window.teacherName = <?= json_encode($this->auth->userName) ?>;

    // Actual save logic for assignment edit modal
    function saveEditedAssignment() {
        const form = document.getElementById('editAssignmentForm');
        const assignmentId = window.currentEditingAssignmentId || null;
        let assignmentName = form.assignmentName.value.trim();
        // Build ÕV label string from selected buttons
        const combobox = document.getElementById('assignmentLearningOutcomeCombobox');
        const checkedBoxes = combobox ? Array.from(combobox.querySelectorAll('.combobox-checkbox:checked')) : [];
        const selectedOvLabels = checkedBoxes.map(cb => 'ÕV' + (cb.dataset.nr || '?'));
        // Always remove any existing ÕV label group in parentheses
        assignmentName = assignmentName.replace(/\s*\(ÕV[0-9]+(,\s*ÕV[0-9]+)*\)/, '').trim();
        // Only append ÕV labels for saving, not for display
        if (selectedOvLabels.length > 0) {
            assignmentName = assignmentName + ' (' + selectedOvLabels.join(', ') + ')';
        } else {
            assignmentName = assignmentName.trim();
        }
        const assignmentInstructions = form.assignmentInstructions.value.trim();
        const assignmentDueAt = form.assignmentDueAt.value;
        const assignmentInvolvesOpenApi = form.assignmentInvolvesOpenApi.checked ? 1 : 0;
        const assignmentEntryDate = form.assignmentEntryDate.value;
        // ÕV selection (array of selected IDs)
        const assignmentLearningOutcomeId = checkedBoxes.map(cb => cb.value);
        // Criteria (if present)
        // Collect new criteria added in modal
        // Collect all existing criteria IDs still present in the modal (not deleted)
        let oldCriteria = [];
        document.querySelectorAll('#editCriteriaContainer .criteria-row').forEach(row => {
            const id = row.dataset.criterionId;
            // Only push if it's an existing criterion (not new)
            if (id && !id.startsWith('new_')) {
                oldCriteria.push(id);
            }
        });
        const newCriteria = Array.isArray(window.newCriteria) ? window.newCriteria : [];

        // Basic validation
        if (!assignmentName) {
            alert('Pealkiri on kohustuslik!');
            return;
        }
        if (assignmentName.length + (window.lastOvLabels ? window.lastOvLabels.length : 0) > 100) {
            alert('Pealkiri koos ÕV siltidega on liiga pikk!');
            return;
        }

        // Find assignmentId from modal context (fallback to hidden field or global)
        let modalAssignmentId = assignmentId;
        if (!modalAssignmentId) {
            // Try to get from a hidden field or data attribute if present
            const idField = form.querySelector('[name="assignmentId"]');
            if (idField) modalAssignmentId = idField.value;
        }
        if (!modalAssignmentId) {
            alert('Tuvastamatu ülesande ID!');
            return;
        }

        // Prepare form data as URLSearchParams (like grading modal)
        let teacherName = window.teacherName || '';
        let teacherId = window.teacherId || '';
        if (!teacherName && window.currentUserName) teacherName = window.currentUserName;
        if (!teacherId && window.currentUserId) teacherId = window.currentUserId;
        const formData = new URLSearchParams();
        formData.append('assignmentId', modalAssignmentId);
        formData.append('assignmentName', assignmentName);
        formData.append('assignmentInstructions', assignmentInstructions);
        formData.append('assignmentDueAt', assignmentDueAt);
        formData.append('assignmentInvolvesOpenApi', assignmentInvolvesOpenApi);
        formData.append('assignmentEntryDate', assignmentEntryDate);
        assignmentLearningOutcomeId.forEach((id, idx) => {
            formData.append(`assignmentLearningOutcomeId[${idx}]`, id);
        });
        // Add oldCriteria to keep
        oldCriteria.forEach((id, idx) => {
            formData.append(`oldCriteria[${id}]`, true);
        });
        // Add edited criteria names
        if (window.editedCriteria) {
            Object.entries(window.editedCriteria).forEach(([id, name]) => {
                formData.append(`editedCriteria[${id}]`, name);
            });
        }
        // Add newCriteria from window.newAddedCriteria (inline add)
        if (window.newAddedCriteria && window.newAddedCriteria.length > 0) {
            window.newAddedCriteria.forEach((name, idx) => {
                formData.append(`newCriteria[${idx}][criteriaName]`, name);
            });
        }
        // Add newCriteria from modal (legacy)
        newCriteria.forEach((crit, idx) => {
            if (crit.criteriaName) {
                formData.append(`newCriteria[${window.newAddedCriteria ? window.newAddedCriteria.length + idx : idx}][criteriaName]`, crit.criteriaName);
            }
            if (crit.criteriaId) {
                formData.append(`newCriteria[${window.newAddedCriteria ? window.newAddedCriteria.length + idx : idx}][criteriaId]`, crit.criteriaId);
            }
        });
        formData.append('teacherName', teacherName);
        formData.append('teacherId', teacherId);

        // AJAX POST to backend
        fetch('/assignments/ajax_editAssignment', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: formData.toString()
        })
            .then(async res => {
                let data;
                let status = res.status;
                try {
                    data = await res.clone().json();
                } catch (e) {
                    const raw = await res.text();
                    console.error('Non-JSON response:', raw);
                    alert('Võrgu viga: server tagastas vigase vastuse. Vaata konsooli!');
                    throw new Error('Non-JSON response');
                }
                return {status, data};
            })
            .then(({status, data}) => {
                console.log('Assignment save response:', status, data);
                if (status === 200 || status === 201) {
                    const modal = bootstrap.Modal.getInstance(document.getElementById('editAssignmentModal'));
                    if (modal) modal.hide();
                    window.newAddedCriteria = [];
                    location.reload();
                } else {
                    alert('Salvestamine ebaõnnestus: ' + (data && data.message ? data.message : 'Tundmatu viga'));
                }
            })
            .catch(err => {
                alert('Võrgu viga: ' + err);
            });
    }

    // Build a JS object mapping subjectExternalId to learning outcomes
    var subjectLearningOutcomes = {};
    <?php foreach ($this->groups as $group): ?>
    <?php foreach ($group['subjects'] as $subject): ?>
    subjectLearningOutcomes[<?= json_encode($subject['subjectExternalId']) ?>] = <?= json_encode(array_map(function ($o) {
        return [
            'id' => $o['id'],
            'nameEt' => $o['nameEt'],
            'learningOutcomeOrderNr' => isset($o['learningOutcomeOrderNr']) ? $o['learningOutcomeOrderNr'] : (isset($o["learningOutcomeOrderNr"]) ? $o["learningOutcomeOrderNr"] : null)
        ];
    }, $subject['learningOutcomes'])) ?>;
    <?php endforeach; ?>
    <?php endforeach; ?>

    // Make edit modal globally available
    function openEditAssignmentModal(assignment) {
        // Character counter and validation for assignment name
        const nameInput = document.getElementById('assignmentName');
        const counter = document.getElementById('assignmentNameCounter');
        const errorDiv = document.getElementById('assignmentNameError');
        let lastOvLabels = '';

        function updateCounter() {
            const combobox = document.getElementById('assignmentLearningOutcomeCombobox');
            const checkedBoxes = combobox ? Array.from(combobox.querySelectorAll('.combobox-checkbox:checked')) : [];
            const selectedOvLabels = checkedBoxes.map(cb => 'ÕV' + (cb.dataset.nr || '?'));
            let ovLabelString = '';
            if (selectedOvLabels.length > 0) {
                ovLabelString = ' (' + selectedOvLabels.join(', ') + ')';
            }
            const len = nameInput.value.length + ovLabelString.length;
            counter.textContent = len + ' / 100';
            if (len > 100) {
                nameInput.classList.add('is-invalid');
                errorDiv.style.display = '';
            } else {
                nameInput.classList.remove('is-invalid');
                errorDiv.style.display = 'none';
            }
        }

        nameInput.removeEventListener('input', updateCounter);
        nameInput.addEventListener('input', updateCounter);
        // Also update counter when combobox changes
        const combobox = document.getElementById('assignmentLearningOutcomeCombobox');
        if (combobox) {
            combobox.removeEventListener('change', updateCounter);
            combobox.addEventListener('change', updateCounter);
            combobox.removeEventListener('click', updateCounter);
            combobox.addEventListener('click', updateCounter);
        }
        setTimeout(updateCounter, 0);
        if (typeof assignment === 'string') {
            assignment = JSON.parse(assignment);
        }
        // Set global assignmentId for save logic
        window.currentEditingAssignmentId = assignment.assignmentId;
        // Fetch latest criteria from backend before showing modal
        fetch(`/assignments/ajax_getAssignmentCriteria?assignmentId=${assignment.assignmentId}`)
            .then(res => res.json())
            .then(data => {
                // Now update modal fields as before
                const assignmentNameInput = document.getElementById('assignmentName');
                if (assignment.assignmentName) {
                    assignmentNameInput.value = assignment.assignmentName.replace(/\s*\(ÕV[0-9]+(,\s*ÕV[0-9]+)*\)/, '').trim();
                } else {
                    assignmentNameInput.value = '';
                }
                var instructionsEditor = document.getElementById('assignmentInstructionsEditor');
                console.log('DEBUG: assignment.assignmentInstructions =', assignment.assignmentInstructions);
                instructionsEditor.value = assignment.assignmentInstructions || '';
                // Trigger input event to update preview and auto-expand
                instructionsEditor.dispatchEvent(new Event('input', {bubbles: true}));
                document.getElementById('assignmentDueAt').value = assignment.assignmentDueAt ? (assignment.assignmentDueAt.length > 0 ? assignment.assignmentDueAt.split('T')[0] : '') : '';
                document.getElementById('assignmentEntryDate').value = assignment.assignmentEntryDate ? (assignment.assignmentEntryDate.length > 0 ? assignment.assignmentEntryDate.split('T')[0] : '') : '';
                document.getElementById('assignmentInvolvesOpenApi').checked = assignment.assignmentInvolvesOpenApi ? true : false;
                var combobox = document.getElementById('assignmentLearningOutcomeCombobox');
                var subjectExternalId = assignment.subjectExternalId;
                var outcomes = subjectLearningOutcomes[subjectExternalId] || [];
                let selectedOutcomes = assignment.assignmentLearningOutcomeId;
                if (!Array.isArray(selectedOutcomes)) {
                    selectedOutcomes = selectedOutcomes ? [selectedOutcomes] : [];
                }
                if (!selectedOutcomes.length && assignment.assignmentName) {
                    const match = assignment.assignmentName.match(/\(ÕV[0-9]+(,\s*ÕV[0-9]+)*\)/);
                    if (match) {
                        const labels = match[0].replace(/[()]/g, '').split(',').map(s => s.trim());
                        selectedOutcomes = outcomes.filter(outcome => labels.includes('ÕV' + ((parseInt(outcome.learningOutcomeOrderNr, 10) || 0) + 1))).map(outcome => outcome.id);
                    }
                }
                // Render custom checkbox list
                combobox.innerHTML = outcomes.map(function (outcome, i) {
                    var nr = (parseInt(outcome.learningOutcomeOrderNr, 10) || 0) + 1;
                    var checked = selectedOutcomes.includes(outcome.id);
                    // Remove leading number and punctuation from outcome.nameEt
                    var cleanName = outcome.nameEt ? outcome.nameEt.replace(/^\s*\d+[).\-:]?\s*/, '') : '';
                    var labelClass = checked ? 'text-dark fw-medium' : 'text-secondary';
                    // Add data-checkbox-id to visual for event binding
                    return `
            <div class="list-group-item list-group-item-action combobox-item border-0 py-2 px-3 d-flex align-items-start">
              <input class="combobox-checkbox" type="checkbox" id="combobox-cb${i}" value="${outcome.id}" data-nr="${nr}" name="nameEt" ${checked ? 'checked' : ''}>
              <div class="combobox-checkbox-visual d-flex align-items-center justify-content-center bg-white border border-2 rounded me-2 mt-1 flex-shrink-0" data-checkbox-id="combobox-cb${i}" style="cursor:pointer;">
                <svg class="combobox-checkmark" viewBox="0 0 12 12" width="12" height="12">
                  <path fill="none" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" d="M2.5 6l3 3 4.5-6"/>
                </svg>
              </div>
              <label class="combobox-label flex-grow-1 ${labelClass} lh-sm pt-1" for="combobox-cb${i}" lang="et" style="cursor:pointer;">ÕV${nr} – ${cleanName}</label>
            </div>
            `;
                }).join('');
                // Add event listeners for change/click to update counter
                combobox.querySelectorAll('.combobox-checkbox').forEach(cb => {
                    cb.addEventListener('change', function () {
                        updateCounter();
                        var label = combobox.querySelector('label[for="' + cb.id + '"]');
                        if (label) {
                            if (cb.checked) {
                                label.classList.remove('text-secondary');
                                label.classList.add('text-dark', 'fw-medium');
                            } else {
                                label.classList.remove('text-dark', 'fw-medium');
                                label.classList.add('text-secondary');
                            }
                        }
                    });
                    cb.addEventListener('click', updateCounter);
                });
                // Make the custom checkbox visual itself toggle the checkbox
                combobox.querySelectorAll('.combobox-checkbox-visual').forEach(visual => {
                    visual.addEventListener('click', function (e) {
                        const checkboxId = visual.getAttribute('data-checkbox-id');
                        const cb = combobox.querySelector('#' + CSS.escape(checkboxId));
                        if (cb) {
                            cb.checked = !cb.checked;
                            cb.dispatchEvent(new Event('change', {bubbles: true}));
                        }
                        e.preventDefault();
                        e.stopPropagation();
                    });
                });
                // Make the entire combobox-item row clickable to toggle the checkbox
                combobox.querySelectorAll('.combobox-item').forEach(item => {
                    item.addEventListener('click', function (e) {
                        // Prevent double toggle if click is on label, checkbox, or checkbox visual
                        if (
                            e.target.classList.contains('combobox-checkbox-visual') ||
                            e.target.classList.contains('combobox-checkmark') ||
                            e.target.classList.contains('combobox-label') ||
                            e.target.classList.contains('combobox-checkbox')
                        ) {
                            return;
                        }
                        const cb = item.querySelector('.combobox-checkbox');
                        if (cb) {
                            cb.checked = !cb.checked;
                            cb.dispatchEvent(new Event('change', {bubbles: true}));
                        }
                    });
                });
                const criteriaContainer = document.getElementById('editCriteriaContainer');
                criteriaContainer.innerHTML = '';
                if (data && data.data && Array.isArray(data.data.criteria)) {
                    data.data.criteria.forEach(criterion => {
                        const row = document.createElement('div');
                        row.className = 'criteria-row d-flex align-items-center justify-content-between w-100';
                        row.dataset.criterionId = criterion.criterionId;
                        row.innerHTML = `<div class=\"d-inline-block\"><label class=\"editable-criterion-label\" style=\"cursor:pointer;color:#212529 !important;\">${criterion.criterionName}</label></div> <button type=\"button\" class=\"remove-criterion-btn p-0 ms-2\" title=\"Eemalda kriteerium\" style=\"background:none;border:none;\"><i class=\"fa fa-trash\"></i></button>`;
                        // Add remove handler
                        row.querySelector('.remove-criterion-btn').onclick = function () {
                            showDeleteCriterionModal({
                                row,
                                onDelete: function () {
                                    row.remove();
                                    if (!window.oldCriteria) window.oldCriteria = {};
                                    window.oldCriteria[criterion.criterionId] = false;
                                    updateCriteriaNumbers();
                                }
                            });
                        };
                        // Add click-to-edit handler for label
                        const label = row.querySelector('.editable-criterion-label');
                        // Remove Bootstrap color classes and force color
                        if (label) {
                            label.classList.remove('text-secondary', 'text-muted', 'text-light', 'text-dark');
                            label.style.color = '#212529';
                            label.style.setProperty('color', '#212529', 'important');
                        }
                        label.addEventListener('click', function (e) {
                            e.stopPropagation();
                            // Prevent multiple inputs
                            if (row.querySelector('input.edit-criterion-input')) return;
                            // Remove number prefix for editing
                            const oldName = label.textContent.replace(/^\d+\.\s*/, '');
                            // Create input
                            const input = document.createElement('input');
                            input.type = 'text';
                            input.value = oldName;
                            input.className = 'form-control form-control-sm edit-criterion-input flex-grow-1';
                            input.style.width = '100%';
                            // Replace the label's parent (.d-inline-block) with the input
                            const labelParent = label.parentNode;
                            labelParent.replaceWith(input);
                            input.focus();

                            // Save logic
                            function saveEdit() {
                                const newName = input.value.trim();
                                if (newName && newName !== oldName) {
                                    label.textContent = newName;
                                    if (!window.editedCriteria) window.editedCriteria = {};
                                    window.editedCriteria[criterion.criterionId] = newName;
                                } else {
                                    label.textContent = oldName;
                                }
                                // Restore label's parent
                                input.replaceWith(labelParent);
                                label.style.display = '';
                                updateCriteriaNumbers();
                            }

                            input.addEventListener('blur', saveEdit);
                            input.addEventListener('keydown', function (ev) {
                                if (ev.key === 'Enter') {
                                    saveEdit();
                                } else if (ev.key === 'Escape') {
                                    input.replaceWith(labelParent);
                                    label.style.display = '';
                                    updateCriteriaNumbers();
                                }
                            });
                        });
                        criteriaContainer.appendChild(row);
                    });
                    updateCriteriaNumbers();
                }
                const modal = new bootstrap.Modal(document.getElementById('editAssignmentModal'));
                modal.show();
                // When modal is fully shown, trigger autoExpand for the markdown editor
                const modalEl = document.getElementById('editAssignmentModal');
                modalEl.addEventListener('shown.bs.modal', function handler() {
                    modalEl.removeEventListener('shown.bs.modal', handler);
                    var textarea = document.getElementById('assignmentInstructionsEditor');
                    var preview = document.getElementById('assignmentInstructionsPreview');
                    if (textarea && preview) {
                        textarea.style.height = 'auto';
                        textarea.style.height = (textarea.scrollHeight + 2) + 'px';
                        preview.style.height = 'auto';
                        if (preview.innerHTML.trim() !== '') {
                            preview.style.height = (preview.scrollHeight + 2) + 'px';
                        } else {
                            preview.style.height = '200px';
                        }
                    }
                });
                setTimeout(() => {
                    updateCounter();
                    // Attach event handler for inline criterion add
                    var input = document.getElementById('newCriterionInput');
                    if (input) {
                        // Remove previous listeners
                        input.onkeydown = null;
                        input.onblur = null;
                        input.addEventListener('keydown', function (e) {
                            if (e.key === 'Enter') {
                                var assignmentId = window.currentEditingAssignmentId;
                                addCriterionInline(input.value, assignmentId);
                            }
                        });
                        input.addEventListener('blur', function () {
                            if (input.value.trim()) {
                                var assignmentId = window.currentEditingAssignmentId;
                                addCriterionInline(input.value, assignmentId);
                            }
                        });
                    }
                }, 0);
            });
    }

    window.openEditAssignmentModal = openEditAssignmentModal;


    // Inline add criterion logic (global scope)
    // Collect new criteria in array and update UI only
    window.newAddedCriteria = window.newAddedCriteria || [];

    function addCriterionInline(name) {
        var criteriaContainer = document.getElementById('editCriteriaContainer');
        if (!name || !name.trim()) return;
        name = name.trim();
        // Prevent duplicates
        var existing = Array.from(criteriaContainer.querySelectorAll('.form-check-label')).map(l => l.textContent.trim());
        if (existing.includes(name) || window.newAddedCriteria.includes(name)) {
            alert('Selline kriteerium on juba olemas!');
            return;
        }
        window.newAddedCriteria.push(name);
        var row = document.createElement('div');
        row.className = 'criteria-row d-flex align-items-center justify-content-between w-100';
        row.innerHTML = `<div class="d-inline-block"><label class="editable-criterion-label" style="color:#212529 !important;">${name}</label></div> <button type="button" class="remove-criterion-btn p-0 ms-2" title="Eemalda kriteerium" style="background:none;border:none;"><i class="fa fa-trash"></i></button>`;
        // Remove Bootstrap color classes and force color for new inline criteria
        const label = row.querySelector('.editable-criterion-label');
        if (label) {
            label.classList.remove('text-secondary', 'text-muted', 'text-light', 'text-dark');
            label.style.color = '#212529';
            label.style.setProperty('color', '#212529', 'important');
            // Attach click-to-edit handler (same as backend)
            label.addEventListener('click', function (e) {
                e.stopPropagation();
                // Prevent multiple inputs
                if (row.querySelector('input.edit-criterion-input')) return;
                // Remove number prefix for editing
                const oldName = label.textContent.replace(/^\d+\.\s*/, '');
                // Create input
                const input = document.createElement('input');
                input.type = 'text';
                input.value = oldName;
                input.className = 'form-control form-control-sm edit-criterion-input flex-grow-1';
                input.style.width = '100%';
                // Replace the label's parent (.d-inline-block) with the input
                const labelParent = label.parentNode;
                labelParent.replaceWith(input);
                input.focus();

                // Save logic
                function saveEdit() {
                    const newName = input.value.trim();
                    if (newName && newName !== oldName) {
                        label.textContent = newName;
                        if (window.newAddedCriteria) {
                            const idx = window.newAddedCriteria.indexOf(oldName);
                            if (idx !== -1) window.newAddedCriteria[idx] = newName;
                        }
                    } else {
                        label.textContent = oldName;
                    }
                    // Restore label's parent
                    input.replaceWith(labelParent);
                    label.style.display = '';
                    updateCriteriaNumbers();
                }

                input.addEventListener('blur', saveEdit);
                input.addEventListener('keydown', function (ev) {
                    if (ev.key === 'Enter') {
                        saveEdit();
                    } else if (ev.key === 'Escape') {
                        input.replaceWith(labelParent);
                        label.style.display = '';
                        updateCriteriaNumbers();
                    }
                });
            });
        }
        row.querySelector('.remove-criterion-btn').onclick = function () {
            showDeleteCriterionModal({
                row,
                onDelete: function () {
                    row.remove();
                    window.newAddedCriteria = window.newAddedCriteria.filter(n => n !== name);
                    updateCriteriaNumbers();
                }
            });
        };

        criteriaContainer.appendChild(row);
        updateCriteriaNumbers();
        var input = document.getElementById('newCriterionInput');
        input.value = '';
        input.focus();
    }

    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('.edit-assignment-btn').forEach(function (btn) {
            btn.addEventListener('click', function () {
                var assignment = btn.getAttribute('data-assignment');
                openEditAssignmentModal(assignment);
            });
        });
    });

    (() => {
        const sortStates = new Map();
        let selectedStudent = null;

        const $ = (sel, ctx = document) => ctx.querySelector(sel);
        const $$ = (sel, ctx = document) => Array.from(ctx.querySelectorAll(sel));

        const getSortValue = (row, col) => {
            const cell = row.querySelector(col === 'name' ? 'td[data-sort-value]' : 'td[data-sort-value]:nth-child(2)');
            return col === 'name' ? (cell?.dataset.sortValue || '').toLowerCase() : parseInt(cell?.dataset.sortValue || 0, 10);
        };

        window.sortStudentTableByElement = (el, col) => {
            const table = el.closest('table');
            table && sortStudentTable(table.dataset.group, col);
        };

        function sortStudentTable(group, col) {
            const table = $$('table.student-summary-table').find(t => t.dataset.group === group);
            if (!table) return;
            if (!sortStates.has(group)) sortStates.set(group, {name: 'none', pending: 'none'});

            const state = sortStates.get(group);
            const dir = state[col] === 'asc' ? 'desc' : 'asc';
            sortStates.set(group, {name: 'none', pending: 'none', [col]: dir});

            const rows = $$('tbody tr', table);
            rows.sort((a, b) => {
                const av = getSortValue(a, col), bv = getSortValue(b, col);
                return dir === 'asc' ? (av > bv ? 1 : -1) : (av < bv ? 1 : -1);
            }).forEach(r => table.tBodies[0].appendChild(r));

            updateSortIcons(group);
        }

        function updateSortIcons(group) {
            const table = $$('table.student-summary-table').find(t => t.dataset.group === group);
            if (!table) return;
            const state = sortStates.get(group);
            const icons = $$('th i', table);
            icons.forEach((icon, i) => {
                const key = i === 0 ? 'name' : 'pending';
                icon.className = state[key] === 'asc' ? 'fas fa-sort-up' : state[key] === 'desc' ? 'fas fa-sort-down' : 'fas fa-sort';
            });
        }

        window.toggleStudentFilter = row => {
            const id = row.dataset.studentId;
            $$('.student-row').forEach(r => r.classList.toggle('selected', r === row && selectedStudent !== id));
            if (selectedStudent === id) {
                selectedStudent = null;
                showAllAssignments();
            } else {
                selectedStudent = id;
                filterAssignmentsByStudent(id);
            }
        };

        const hasProblems = cell => {
            if (!cell) return false;
            const grade = cell.dataset.grade;
            return cell.classList.contains('red-cell') || ['1', '2', 'MA'].includes(grade);
        };

        const subjectHeaderBefore = row => {
            let r = row.previousElementSibling;
            while (r && !r.querySelector('th')) {
                r = r.previousElementSibling;
            }
            return r;
        };

        function filterAssignmentsByStudent(id) {
            $$('#subject-table').forEach(table => {
                const rows = $$('tr', table);
                const visibleSubjects = new Set();
                rows.forEach((r) => {
                    if (r.querySelector('th')) return;
                    const cell = r.querySelector(`td[data-student-id="${id}"]`);
                    const show = hasProblems(cell);
                    r.style.display = show ? '' : 'none';
                    if (show) {
                        const hdr = subjectHeaderBefore(r);
                        hdr && visibleSubjects.add(hdr.textContent.trim());
                    }
                });
                rows.forEach(r => {
                    if (!r.querySelector('th')) return;
                    r.style.display = visibleSubjects.has(r.textContent.trim()) ? '' : 'none';
                });
                const spacer = table.previousElementSibling;
                table.style.display = rows.some(r => r.style.display !== 'none') ? '' : 'none';
                if (spacer && spacer.tagName === 'DIV' && spacer.style.height === '20px') spacer.style.display = table.style.display;
            });
        }

        const showAllAssignments = () => {
            $$('#subject-table').forEach(table => {
                table.style.display = '';
                const spacer = table.previousElementSibling;
                if (spacer && spacer.tagName === 'DIV') {
                    spacer.style.display = '';
                }
                $$('tr', table).forEach(row => row.style.display = '');
            });
        };

        const setRedCellIntensity = () => {
            $$('.red-cell[data-days-passed]').forEach(cell => {
                const daysPassed = parseInt(cell.dataset.daysPassed, 10);
                if (isNaN(daysPassed)) return;

                const factor = Math.min(daysPassed, 10) / 10;
                const red = Math.round(255);
                const green = Math.round(180 - 180 * factor);
                const blue = Math.round(176 - 176 * factor);

                cell.style.backgroundColor = `rgb(${red}, ${green}, ${blue})`;
                cell.style.color = factor > 0.5 ? 'white' : 'black';
            });
        };

        const updateBadges = () => {
            $$('td[data-grade]').forEach(cell => {
                const grade = parseInt(cell.dataset.grade, 10);
                const isStudent = JSON.parse(cell.dataset.isStudent);
                const badge = cell.closest('tr')?.querySelector('span[data-days-remaining]');

                if (isStudent && !isNaN(grade) && grade >= 3 && badge && !badge.className.includes('bg-light')) {
                    badge.className = badge.className.replace(/badge [^ ]+/, 'badge bg-light text-dark');
                }
            });
        };

        const prepareClickableCells = () => {
            $$('td[data-url]').forEach(cell => {
                cell.onclick = () => location.href = cell.dataset.url;
            });
        };

        const prepareShowAllToggle = () => {
            const toggle = $('#showAllToggle');
            if (!toggle) return;

            toggle.onchange = () => {
                const url = new URL(location.href);
                url.searchParams.set('showAll', toggle.checked ? '1' : '0');
                location.href = url;
            };
        };

        const initSorting = () => {
            $$('.student-summary-table').forEach(tbl => {
                sortStates.set(tbl.dataset.group, {name: 'none', pending: 'none'});
                sortStudentTable(tbl.dataset.group, 'pending');
            });
        };

        document.addEventListener('DOMContentLoaded', () => {
            $$('[data-bs-toggle="tooltip"]').forEach(el => new bootstrap.Tooltip(el));
            prepareClickableCells();
            prepareShowAllToggle();
            setRedCellIntensity();
            updateBadges();
            initSorting();
        });
    })();
</script>
