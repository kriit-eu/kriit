<style>
    body {background: linear-gradient(135deg,#f5f7fa 0%,#e4e9f2 100%);min-height: 100vh;}
    #container,.table-responsive{background-color:transparent;}
    #subject-table{background-color:transparent;box-shadow:0 2px 4px rgba(0,0,0,.07);table-layout:fixed!important;border:1px solid #d8d8d8!important;border-collapse:collapse!important;width:100%!important;}
    #subject-table td{background-color:#fff;border-color:#d8d8d8!important;}
    #subject-table th{background-color:#f2f2f2;border-color:#d8d8d8!important;}
    .red-cell{background-color:rgb(255,180,176)!important;}
    .yellow-cell{background-color:#fff8b3!important;}
    .days-passed{font-size:.55em;}
    .student-summary-table{border-collapse:collapse!important;box-shadow:0 2px 4px rgba(0,0,0,.1)!important;}
    .student-summary-table th,.student-summary-table td{border:1px solid #dee2e6!important;padding:8px 12px!important;}
    .student-summary-table th{background-color:#f2f2f2!important;}
    .student-summary-table td{background-color:#fff!important;}
    .student-row.selected,.student-row.selected td{background-color:#0d6efd!important;color:#fff!important;}
    .text-center{text-align:center;}
    .inactive-student{opacity:.6;font-style:italic;}
    .deleted-student{opacity:.4;text-decoration:line-through;color:#888;}
    .narrow-name{font-size:.55em;line-height:1;white-space:nowrap;text-align:center;font-family:"Arial Narrow",Arial,sans-serif;font-stretch:condensed;letter-spacing:-.02em;display:flex;flex-direction:column;justify-content:center;align-items:center;height:100%;}
    .narrow-name .lastname{margin-top:1px;display:block;}
    #subject-table th.student-name-header{padding:1px 4px;font-weight:400;vertical-align:middle;height:36px;}
    .subject-spacer, .subject-spacer td, tr.subject-spacer, tr.subject-spacer td, #subject-table tr.subject-spacer td{height:20px;background-color:transparent!important;border:none!important;box-shadow:none!important;outline:none!important;}
    .student-name-header,#subject-table td.text-center:not(:first-child){width:40px!important;min-width:40px!important;max-width:40px!important;text-align:center;vertical-align:middle;padding:4px 2px;box-sizing:border-box;}
    #subject-table td:first-child,#subject-table th:first-child{width:60px;min-width:60px;max-width:60px;text-align:center;vertical-align:middle;}
    #subject-table td:nth-child(<?= $this->isStudent ? '1' : '2' ?>),#subject-table th:nth-child(<?= $this->isStudent ? '1' : '2' ?>){width:auto;min-width:160px;max-width:none;flex-grow:1;}
    .student-view #subject-table{width:100%!important;table-layout:fixed!important;border-collapse:collapse!important;}
    .student-view #subject-table tr{display:table-row;width:100%;}
    .student-view #subject-table th[colspan="2"]{text-align:left;padding:8px 12px;}
    .student-view #subject-table td{text-align:left!important;}
    .student-view #subject-table td:first-child{padding:8px 12px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;text-align:left;width:calc(100% - 120px)!important;}
    .student-view #subject-table td.text-center{text-align:center!important;width:120px!important;min-width:120px!important;max-width:120px!important;padding:4px 8px;box-sizing:border-box!important;}
    .teacher-view #subject-table{width:100%!important;}
    .entry-date{color:#664d03;font-size:.85em;background-color:#fff3cd;padding:2px 5px;border-radius:3px;margin-right:5px;text-decoration:none;}
    .due-date-badge{float:right;margin-left:8px;}
    .assignment-container{display:flex;justify-content:space-between;align-items:center;width:100%;position:relative;}
    .assignment-info{flex-grow:1;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;padding-right:10px;}
    #subject-table td a{display:inline;line-height:1.4;text-decoration:none!important;font-weight:500;}
    #subject-table td:nth-child(2){padding:8px 12px;width:auto;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;text-align:left;}
    #subject-table td a:hover,#subject-table th a:hover{text-decoration:underline;color:#0056b3;}
    #subject-table th a{color:inherit;text-decoration:none;}
    .id-badge{background-color:#e9ecef;padding:2px 6px;border-radius:3px;font-size:.85em;color:#495057;}
    #subject-table td[data-grade]{text-align:center!important;justify-content:center!important;align-items:center!important;display:table-cell!important;}
</style>

<?php if ($this->isTeacherOrAdmin): ?>
    <div class="col text-end mb-3 d-flex justify-content-end align-items-center">
        <?php if (!$this->isStudent): ?>
            <div class="form-check form-switch me-3">
                <input class="form-check-input" type="checkbox" id="showAllToggle" <?= $this->showAll ? 'checked' : '' ?>>
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
                <table class="table table-bordered student-summary-table" data-group="<?= htmlspecialchars($group['groupName']) ?>" style="width:auto;background-color:#fff;">
                    <thead>
                    <tr>
                        <th style="cursor:pointer;text-align:left;" onclick="sortStudentTableByElement(this,'name')"><b>Õpilane</b> <i class="fas fa-sort"></i></th>
                        <th style="cursor:pointer;text-align:center;" onclick="sortStudentTableByElement(this,'pending')"><b>Võlad</b> <i class="fas fa-sort"></i></th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($group['students'] as $student): ?>
                        <?php $pending = $group['pendingGrades'][$student['userId']] ?? 0; ?>
                        <tr class="<?= $student['meta']['css'] ?> student-row" data-student-id="<?= $student['userId'] ?>" data-student-name="<?= htmlspecialchars($student['userName']) ?>" style="cursor:pointer;" onclick="toggleStudentFilter(this)">
                            <td data-sort-value="<?= htmlspecialchars($student['userName']) ?>" style="background-color:#fff;">
                                <?= htmlspecialchars($student['userName']) . $student['meta']['status'] ?>
                            </td>
                            <td data-sort-value="<?= $pending ?>" class="text-center" style="background-color:#fff;color:<?= $pending>0?'#dc3545':'#28a745' ?>;font-weight:bold;">
                                <?= $pending ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>

</script>
                </table>
            </div>
        <?php endif; ?>

        <div class="table-responsive">
            <?php foreach ($group['subjects'] as $index=>$subject): ?>
                <?php if ($index>0): ?><div style="height:20px;width:100%;"></div><?php endif; ?>

                <table id="subject-table" class="table table-bordered">
                    <tr data-href="subjects/<?= $subject['subjectId'] ?>">
                        <?php if(!$this->isStudent): ?><th class="text-center"><b>ID</b></th><?php endif; ?>
                        <th <?= $this->isStudent?'colspan="2"':'' ?>>
                            <b>
                                <?php if(!empty($subject['subjectExternalId'])): ?>
                                    <a href="https://tahvel.edu.ee/#/journal/<?= $subject['subjectExternalId'] ?>/edit" target="_blank"><?= $subject['subjectName'] ?></a>
                                <?php else: ?>
                                    <?= $subject['subjectName'] ?>
                                <?php endif; ?>
                            </b>
                        </th>
                        <?php if(!$this->isStudent): ?>
                            <?php foreach ($group['students'] as $student): ?>
                                <th data-bs-toggle="tooltip" title="<?= $student['userName'] . $student['meta']['status'] ?>" class="student-name-header text-center <?= $student['meta']['css'] ?>">
                                    <div class="narrow-name">
                                        <?= $student['nameSplit']['first'] ?><span class="lastname"><?= $student['nameSplit']['last'] ?></span>
                                    </div>
                                </th>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tr>

                    <?php foreach ($subject['assignments'] as $assignment): ?>
                        <tr>
                            <?php if(!$this->isStudent): ?><td class="text-center"><span class="id-badge"><?= $assignment['assignmentId'] ?></span></td><?php endif; ?>
                            <td colspan="1">
                                <div class="assignment-container">
                                    <div class="assignment-info">
                                        <a href="assignments/<?= $assignment['assignmentId'] ?>?group=<?= urlencode($group['groupName']) ?>">
                                            <?php if(!empty($assignment['assignmentEntryDateFormatted'])): ?><span class="entry-date"><?= $assignment['assignmentEntryDateFormatted'] ?></span><?php endif; ?>
                                            <?= $assignment['assignmentName'] ?>
                                        </a>
                                    </div>
                                    <button class="btn btn-link p-0 ms-2 edit-assignment-btn" title="Muuda ülesanne" data-assignment='<?= htmlspecialchars(json_encode(array_merge($assignment, ["subjectExternalId" => $subject['subjectExternalId']])), ENT_QUOTES, 'UTF-8') ?>'>
                                        <i class="fa fa-pencil-alt"></i>
                                    </button>
                                    <?php if($assignment['showDueDate']): ?>
                                        <span class="badge <?= $assignment['finalBadgeClass'] ?> due-date-badge" data-days-remaining="<?= $assignment['daysRemaining'] ?>" data-is-student=<?= json_encode($this->isStudent) ?>>
                                            <?= $assignment['assignmentDueAt'] ? (new DateTime($assignment['assignmentDueAt']))->format('d.m.y') : 'Pole tähtaega' ?>
                                        </span>
                                    <?php endif; ?>
                                </div>
                            </td>
                            <?php if(!$this->isStudent): ?>
                                <?php foreach ($group['students'] as $student): ?>
                                    <?php
                                    $st = $assignment['assignmentStatuses'][$student['userId']] ?? ['class'=>'','grade'=>'','assignmentStatusName'=>'Esitamata','tooltipText'=>'','daysPassed'=>0];
                                    $tooltip = $student['meta']['css'] ? ($student['meta']['deleted'] ? 'Kustutatud õpilane' : 'Mitteaktiivne õpilane') : '';
                                    ?>
                                    <td class="<?= $st['class'].' text-center '.$student['meta']['css'] ?> <?= $st['class']==='red-cell'&&$st['daysPassed']>0?'red-cell-intensity':'' ?>" data-bs-toggle="tooltip" data-bs-placement="bottom" data-bs-html="true" title="<?= nl2br(htmlspecialchars(($tooltip?"$tooltip\n":'').$st['tooltipText'])) ?>" data-grade="<?= is_numeric($st['grade'])?intval($st['grade']):($st['grade']?:'') ?>" data-student-id="<?= $student['userId'] ?>" data-is-student="<?= json_encode($this->isStudent) ?>" data-days-passed="<?= $st['daysPassed'] ?? 0 ?>" data-url="assignments/<?= $assignment['assignmentId'] ?>?group=<?= urlencode($group['groupName']) ?>">
                                        <?php if($st['class']==='red-cell'&&$st['daysPassed']): ?>
                                            <span class="days-passed"><?= $st['daysPassed'] ?>p</span>
                                        <?php else: ?>
                                            <?= $st['grade'] ?>
                                        <?php endif; ?>
                                    </td>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <?php
                                $uid = $this->auth->userId;
                                $st = $assignment['assignmentStatuses'][$uid] ?? ['class'=>'','assignmentStatusName'=>'Esitamata','grade'=>'','tooltipText'=>''];
                                ?>
                                <td class="<?= $st['class'] ?> text-center" data-bs-toggle="tooltip" data-bs-placement="bottom" data-bs-html="true" title="<?= nl2br(htmlspecialchars($st['tooltipText'])) ?>" data-grade="<?= is_numeric($st['grade'])?intval($st['grade']):'' ?>" data-is-student="true" data-url="assignments/<?= $assignment['assignmentId'] ?>?group=<?= urlencode($group['groupName']) ?>" style="width:120px;min-width:120px;max-width:120px;">
                                    <?= $st['assignmentStatusName']==='Kontrollimisel'?'Kontrollimisel':($st['grade']?:$st['assignmentStatusName']) ?>
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
    <div class="modal fade" id="editAssignmentModal" tabindex="-1" aria-labelledby="editAssignmentModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editAssignmentModalLabel">Muuta ülesanne</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="editAssignmentForm">
                        <div class="mb-3">
                            <label for="assignmentName" class="form-label">Pealkiri</label>
                            <input type="text" class="form-control" id="assignmentName" name="assignmentName" value="" maxlength="100">
                            <div class="form-text" id="assignmentNameCounter">0 / 100</div>
                            <div class="invalid-feedback" id="assignmentNameError" style="display:none;">Pealkiri on liiga pikk maksimum tähemärkide pikkus 100</div>
                            <div id="assignmentOvBadges" class="mt-2"></div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Õppe-eesmärk (ÕV)</label>
                            <div id="assignmentLearningOutcomeBtns" class="btn-group w-100 flex-wrap" role="group" aria-label="Õppe-eesmärgid"></div>
                        </div>
                        <div class="mb-3">
                            <label for="assignmentInstructions" class="form-label">Instruktsioon</label>
                            <textarea class="form-control" id="assignmentInstructions" name="assignmentInstructions" rows="3"></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="assignmentDueAt" class="form-label">Tähtaeg</label>
                            <input type="date" class="form-control" id="assignmentDueAt" name="assignmentDueAt" value="">
                        </div>
                        <div class="mb-3 form-check">
                            <input type="checkbox" class="form-check-input" id="assignmentInvolvesOpenApi" name="assignmentInvolvesOpenApi">
                            <label class="form-check-label" for="assignmentInvolvesOpenApi">Ülesandel on OpenAPI</label>
                        </div>
                        <div class="mb-3">
                            <h5>Kriteeriumid</h5>
                            <div id="editCriteriaContainer"></div>
                            <button type="button" class="btn btn-primary mt-2" id="addCriterionButton">Lisa kriteerium</button>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary" onclick="saveEditedAssignment()">Salvesta</button>
                    <button type="button" class="btn btn-secondary" onclick="location.reload()" data-bs-dismiss="modal">Tühista</button>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>

<script>
    // Inject current user info for assignment save
    window.teacherId = <?= json_encode($this->auth->userId) ?>;
    window.teacherName = <?= json_encode($this->auth->userName) ?>;
    // Confirm JS is running
    console.log('subjects_index.php JS loaded');

    // Actual save logic for assignment edit modal
    function saveEditedAssignment() {
        const form = document.getElementById('editAssignmentForm');
        const assignmentId = window.currentEditingAssignmentId || null;
        let assignmentName = form.assignmentName.value.trim();
        // Build ÕV label string from selected buttons
        const btnsContainer = document.getElementById('assignmentLearningOutcomeBtns');
        const selectedBtns = Array.from(btnsContainer.children).filter(btn => btn.classList.contains('active'));
        const selectedOvLabels = selectedBtns.map(btn => 'ÕV' + (btn.dataset.nr || '?'));
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
        // ÕV selection (array of selected IDs)
        const assignmentLearningOutcomeId = selectedBtns.map(btn => btn.dataset.id);
        // Criteria (if present)
        // For now, just send empty arrays (extend as needed)
        const oldCriteria = [];
        const newCriteria = [];

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

        // Prepare payload
        // Get teacher info from global context if available
        let teacherName = window.teacherName || '';
        let teacherId = window.teacherId || '';
        // Try to get from DOM if not set
        if (!teacherName && window.currentUserName) teacherName = window.currentUserName;
        if (!teacherId && window.currentUserId) teacherId = window.currentUserId;
        // If still not set, fallback to empty string
        const payload = {
            assignmentId: modalAssignmentId,
            assignmentName,
            assignmentInstructions,
            assignmentDueAt,
            assignmentInvolvesOpenApi,
            assignmentLearningOutcomeId,
            oldCriteria,
            newCriteria,
            teacherName,
            teacherId
        };

        // AJAX POST to backend
        fetch('/assignments/ajax_editAssignment', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify(payload)
        })
        .then(async res => {
            let data;
            let status = res.status;
            try {
                data = await res.clone().json();
            } catch (e) {
                // Not valid JSON, get raw text from original response
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
                // Success: close modal and reload page (no message)
                const modal = bootstrap.Modal.getInstance(document.getElementById('editAssignmentModal'));
                if (modal) modal.hide();
                location.reload();
            } else {
                // Error: show feedback
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
            subjectLearningOutcomes[<?= json_encode($subject['subjectExternalId']) ?>] = <?= json_encode(array_map(function($o) {
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
        // Store the last selected ÕV label string for counting
        let lastOvLabels = '';
        function updateCounter() {
            // Build ÕV label string from selected buttons
            const btnsContainer = document.getElementById('assignmentLearningOutcomeBtns');
            const selectedBtns = Array.from(btnsContainer.children).filter(btn => btn.classList.contains('active'));
            const selectedOvLabels = selectedBtns.map(btn => 'ÕV' + (btn.dataset.nr || '?'));
            let ovLabelString = '';
            if (selectedOvLabels.length > 0) {
                ovLabelString = ' (' + selectedOvLabels.join(', ') + ')';
            }
            // Count the value plus ÕV labels (even if not shown)
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
        // Update counter immediately after setting value
        setTimeout(updateCounter, 0);
        if (typeof assignment === 'string') {
            assignment = JSON.parse(assignment);
        }
        // Set global assignmentId for save logic
        window.currentEditingAssignmentId = assignment.assignmentId;
        const assignmentNameInput = document.getElementById('assignmentName');
        // Always show only the base name (without ÕV labels)
        if (assignment.assignmentName) {
            assignmentNameInput.value = assignment.assignmentName.replace(/\s*\(ÕV[0-9]+(,\s*ÕV[0-9]+)*\)/, '').trim();
        } else {
            assignmentNameInput.value = '';
        }
        document.getElementById('assignmentInstructions').value = assignment.assignmentInstructions || '';
        document.getElementById('assignmentDueAt').value = assignment.assignmentDueAt ? (assignment.assignmentDueAt.length > 0 ? assignment.assignmentDueAt.split('T')[0] : '') : '';
        document.getElementById('assignmentInvolvesOpenApi').checked = assignment.assignmentInvolvesOpenApi ? true : false;
        // Populate ÕV button group dynamically using subjectExternalId
        var btnsContainer = document.getElementById('assignmentLearningOutcomeBtns');
        btnsContainer.innerHTML = '';
        var subjectExternalId = assignment.subjectExternalId;
        var outcomes = subjectLearningOutcomes[subjectExternalId] || [];
        let selectedOutcomes = assignment.assignmentLearningOutcomeId;
        if (!Array.isArray(selectedOutcomes)) {
            selectedOutcomes = selectedOutcomes ? [selectedOutcomes] : [];
        }
        // If no selectedOutcomes, try to parse from assignmentName
        if (!selectedOutcomes.length && assignment.assignmentName) {
            // Match ÕV labels in parentheses, e.g., (ÕV1, ÕV2)
            const match = assignment.assignmentName.match(/\(ÕV[0-9]+(,\s*ÕV[0-9]+)*\)/);
            if (match) {
                const labels = match[0].replace(/[()]/g, '').split(',').map(s => s.trim());
                // Map labels to outcome IDs by matching nr
                selectedOutcomes = outcomes.filter(outcome => labels.includes('ÕV' + ((parseInt(outcome.learningOutcomeOrderNr, 10) || 0) + 1))).map(outcome => outcome.id);
            }
        }
        outcomes.forEach(function(outcome) {
            var nr = (parseInt(outcome.learningOutcomeOrderNr, 10) || 0) + 1;
            var btn = document.createElement('button');
            btn.type = 'button';
            btn.className = 'btn btn-outline-primary m-1 ov-toggle-btn';
            btn.textContent = 'ÕV' + nr;
            btn.dataset.id = outcome.id;
            btn.dataset.nr = nr;
            btn.setAttribute('data-bs-toggle', 'tooltip');
            btn.setAttribute('title', outcome.nameEt);
            if (selectedOutcomes.includes(outcome.id)) {
                btn.classList.add('active');
            }
            btnsContainer.appendChild(btn);
        });
        // Enable Bootstrap tooltips and store instances
        Array.from(btnsContainer.children).forEach(function(btn) {
            const tooltip = new bootstrap.Tooltip(btn);
            // Hide tooltip on click
            btn.addEventListener('click', function() {
                tooltip.hide();
            });
            // Hide tooltip on mouseleave
            btn.addEventListener('mouseleave', function() {
                tooltip.hide();
            });
        });
        // Add event listener for toggling selection
        btnsContainer.addEventListener('click', function(e) {
            if (e.target && e.target.classList.contains('ov-toggle-btn')) {
                e.target.classList.toggle('active');
                updateOvSelection();
            }
        });
        function updateOvSelection() {
            // Do not show ÕV labels in the input field, just keep the base name
            let currentName = assignmentNameInput.value || '';
            assignmentNameInput.value = currentName.trim();
            updateCounter();
        }
        const criteriaContainer = document.getElementById('editCriteriaContainer');
        criteriaContainer.innerHTML = '';
        if (assignment.criteria && Array.isArray(assignment.criteria)) {
            assignment.criteria.forEach(criterion => {
                const row = document.createElement('div');
                row.className = 'criteria-row';
                row.innerHTML = `<div class=\"form-check\"><input class=\"form-check-input\" type=\"checkbox\" id=\"edit_criterion_${criterion.criteriaId}\" checked disabled><label class=\"form-check-label\" for=\"edit_criterion_${criterion.criteriaId}\">${criterion.criteriaName}</label></div>`;
                criteriaContainer.appendChild(row);
            });
        }
        const modal = new bootstrap.Modal(document.getElementById('editAssignmentModal'));
        modal.show();
        setTimeout(() => {
            updateCounter();
        }, 0);
    }
    window.openEditAssignmentModal = openEditAssignmentModal;

    document.addEventListener('DOMContentLoaded', function() {
        document.querySelectorAll('.edit-assignment-btn').forEach(function(btn) {
            btn.addEventListener('click', function() {
                var assignment = btn.getAttribute('data-assignment');
                openEditAssignmentModal(assignment);
            });
        });
    });

    (()=>{
        const sortStates = new Map();
        let selectedStudent = null;

        const $ = (sel, ctx=document) => ctx.querySelector(sel);
        const $$ = (sel, ctx=document) => Array.from(ctx.querySelectorAll(sel));

        const getSortValue = (row,col)=>{
            const cell = row.querySelector(col==='name'? 'td[data-sort-value]' : 'td[data-sort-value]:nth-child(2)');
            return col==='name' ? (cell?.dataset.sortValue||'').toLowerCase() : parseInt(cell?.dataset.sortValue||0,10);
        };

        window.sortStudentTableByElement = (el,col)=>{
            const table = el.closest('table');
            table && sortStudentTable(table.dataset.group,col);
        };

        function sortStudentTable(group,col){
            const table = $$('table.student-summary-table').find(t=>t.dataset.group===group);
            if(!table) return;
            if(!sortStates.has(group)) sortStates.set(group,{name:'none',pending:'none'});

            const state = sortStates.get(group);
            const dir   = state[col]==='asc'?'desc':'asc';
            sortStates.set(group,{name:'none',pending:'none',[col]:dir});

            const rows = $$('tbody tr',table);
            rows.sort((a,b)=>{
                const av=getSortValue(a,col), bv=getSortValue(b,col);
                return dir==='asc'? (av>bv?1:-1) : (av<bv?1:-1);
            }).forEach(r=>table.tBodies[0].appendChild(r));

            updateSortIcons(group);
        }

        function updateSortIcons(group){
            const table = $$('table.student-summary-table').find(t=>t.dataset.group===group);
            if(!table) return;
            const state = sortStates.get(group);
            const icons = $$('th i',table);
            icons.forEach((icon,i)=>{
                const key = i===0?'name':'pending';
                icon.className = state[key]==='asc'?'fas fa-sort-up': state[key]==='desc'?'fas fa-sort-down':'fas fa-sort';
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

        function filterAssignmentsByStudent(id){
            $$('#subject-table').forEach(table=>{
                const rows = $$('tr',table);
                const visibleSubjects = new Set();
                rows.forEach((r)=>{
                    if(r.querySelector('th')) return;
                    const cell = r.querySelector(`td[data-student-id="${id}"]`);
                    const show = hasProblems(cell);
                    r.style.display = show ? '' : 'none';
                    if(show){
                        const hdr = subjectHeaderBefore(r);
                        hdr && visibleSubjects.add(hdr.textContent.trim());
                    }
                });
                rows.forEach(r=>{
                    if(!r.querySelector('th')) return;
                    r.style.display = visibleSubjects.has(r.textContent.trim()) ? '' : 'none';
                });
                const spacer = table.previousElementSibling;
                table.style.display = rows.some(r=>r.style.display!=='none') ? '' : 'none';
                if(spacer && spacer.tagName==='DIV' && spacer.style.height==='20px') spacer.style.display = table.style.display;
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

        const initSorting = ()=>{
            $$('.student-summary-table').forEach(tbl=>{
                sortStates.set(tbl.dataset.group,{name:'none',pending:'none'});
                sortStudentTable(tbl.dataset.group,'pending');
            });
        };

        document.addEventListener('DOMContentLoaded',()=>{
            $$('[data-bs-toggle="tooltip"]').forEach(el=>new bootstrap.Tooltip(el));
            prepareClickableCells();
            prepareShowAllToggle();
            setRedCellIntensity();
            updateBadges();
            initSorting();
        });
    })();
</script>
