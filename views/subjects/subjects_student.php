<style>
    /* Make assignment edit modal even wider than Bootstrap modal-xl */
    .modal-xl {
        max-width: 1200px;
    }
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




<div class="row student-view">
    <div class="mb-3 d-flex justify-content-end">
        <div class="form-check form-switch">
            <input class="form-check-input" type="checkbox" id="filterUnfinishedFailingToggle">
            <label class="form-check-label" for="filterUnfinishedFailingToggle">Esitamata või täiendamist vajavad ülesanded</label>
        </div>
    </div>
    <?php foreach ($this->groups as $group): ?>
        <h1><?= $group['groupName'] ?></h1>

        <div class="table-responsive">
            <?php foreach ($group['subjects'] as $index=>$subject): ?>
                <?php if ($index>0): ?><div style="height:20px;width:100%;"></div><?php endif; ?>

                <table id="subject-table" class="table table-bordered">
                    <tr data-href="subjects/<?= $subject['subjectId'] ?>">
                        <th colspan="2">
                            <b>
                                <?php if(!empty($subject['subjectExternalId'])): ?>
                                    <a href="https://tahvel.edu.ee/#/journal/<?= $subject['subjectExternalId'] ?>/edit" target="_blank"><?= $subject['subjectName'] ?></a>
                                <?php else: ?>
                                    <?= $subject['subjectName'] ?>
                                <?php endif; ?>
                            </b>
                        </th>

                    </tr>

                    <?php foreach ($subject['assignments'] as $assignment): ?>
                        <?php
                        // Compute a flag for unfinished or failing for current student
                        $uid = $this->auth->userId;
                        $st = $assignment['assignmentStatuses'][$uid] ?? ['class'=>'','assignmentStatusName'=>'Esitamata','grade'=>'','tooltipText'=>''];
                        $gradeVal = $st['grade'];
                        $isFailing = ($gradeVal === 'MA') || (is_numeric($gradeVal) && intval($gradeVal) < 3);
                        $isUnfinished = ($st['assignmentStatusName'] ?? '') === 'Esitamata' || empty($st['assignmentStatusName']);
                        $unfinishedOrFailing = $isFailing || $isUnfinished;
                        ?>
                        <tr data-unfinished-failing="<?= $unfinishedOrFailing ? '1' : '0' ?>">
                            <td colspan="1">
                                <div class="assignment-container">
                                    <div class="assignment-info">
                                        <a href="assignments/<?= $assignment['assignmentId'] ?>?group=<?= urlencode($group['groupName']) ?>">
                                            <?php if(!empty($assignment['assignmentEntryDateFormatted'])): ?><span class="entry-date"><?= $assignment['assignmentEntryDateFormatted'] ?></span><?php endif; ?>
                                            <?= $assignment['assignmentName'] ?>
                                        </a>
                                    </div>
                                    <!-- No edit button for students -->
                                    <?php if($assignment['showDueDate']): ?>
                                        <span class="badge <?= $assignment['finalBadgeClass'] ?> due-date-badge" data-days-remaining="<?= $assignment['daysRemaining'] ?>" data-is-student=<?= json_encode($this->isStudent) ?>>
                                            <?= $assignment['assignmentDueAt'] ? (new DateTime($assignment['assignmentDueAt']))->format('d.m.y') : 'Pole tähtaega' ?>
                                        </span>
                                    <?php endif; ?>
                                </div>
                            </td>
                            <?php /* $st already computed above for filter flag */ ?>
                            <td class="<?= $st['class'] ?> text-center" data-bs-toggle="tooltip" data-bs-placement="bottom" data-bs-html="true" title="<?= nl2br(htmlspecialchars($st['tooltipText'])) ?>" data-grade="<?= is_numeric($st['grade'])?intval($st['grade']):'' ?>" data-is-student="true" data-url="assignments/<?= $assignment['assignmentId'] ?>?group=<?= urlencode($group['groupName']) ?>" style="width:120px;min-width:120px;max-width:120px;">
                                <?= $st['assignmentStatusName']==='Kontrollimisel'?'Kontrollimisel':($st['grade']?:$st['assignmentStatusName']) ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </table>
            <?php endforeach; ?>
        </div>
    <?php endforeach; ?>
</div>



<script src="/assets/js/markdown_editor.js"></script>
<script>
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
                document.getElementById('assignmentInstructionsEditor').value = assignment.assignmentInstructions || '';
                document.getElementById('assignmentDueAt').value = assignment.assignmentDueAt ? (assignment.assignmentDueAt.length > 0 ? assignment.assignmentDueAt.split('T')[0] : '') : '';
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
        combobox.innerHTML = outcomes.map(function(outcome, i) {
            var nr = (parseInt(outcome.learningOutcomeOrderNr, 10) || 0) + 1;
            var checked = selectedOutcomes.includes(outcome.id);
            // Remove leading number and punctuation from outcome.nameEt
            var cleanName = outcome.nameEt ? outcome.nameEt.replace(/^\s*\d+[).\-:]?\s*/, '') : '';
            var labelClass = checked ? 'text-dark fw-medium' : 'text-secondary';
            return `
            <div class="list-group-item list-group-item-action combobox-item border-0 py-2 px-3 d-flex align-items-start">
              <input class="combobox-checkbox" type="checkbox" id="combobox-cb${i}" value="${outcome.id}" data-nr="${nr}" name="nameEt" ${checked ? 'checked' : ''}>
              <div class="combobox-checkbox-visual d-flex align-items-center justify-content-center bg-white border border-2 rounded me-2 mt-1 flex-shrink-0">
                <svg class="combobox-checkmark" viewBox="0 0 12 12" width="12" height="12">
                  <path fill="none" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" d="M2.5 6l3 3 4.5-6"/>
                </svg>
              </div>
              <label class="combobox-label flex-grow-1 ${labelClass} lh-sm pt-1" for="combobox-cb${i}" lang="et">ÕV${nr} – ${cleanName}</label>
            </div>
            `;
        }).join('');
        // Add event listeners for change/click to update counter
        combobox.querySelectorAll('.combobox-checkbox').forEach(cb => {
            cb.addEventListener('change', function() {
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
                const criteriaContainer = document.getElementById('editCriteriaContainer');
                criteriaContainer.innerHTML = '';
                if (data && data.data && Array.isArray(data.data.criteria)) {
                    data.data.criteria.forEach(criterion => {
                        const row = document.createElement('div');
                        row.className = 'criteria-row';
                        row.dataset.criterionId = criterion.criterionId;
                        row.innerHTML = `<div class=\"form-check d-inline-block\"><input class=\"form-check-input\" type=\"checkbox\" id=\"edit_criterion_${criterion.criterionId}\" checked disabled><label class=\"form-check-label editable-criterion-label\" for=\"edit_criterion_${criterion.criterionId}\" style=\"cursor:pointer;\">${criterion.criterionName}</label></div> <button type=\"button\" class=\"btn btn-danger btn-sm ms-2 remove-criterion-btn\" title=\"Eemalda kriteerium\">X</button>`;
                        // Add remove handler
                        row.querySelector('.remove-criterion-btn').onclick = function() {
                            row.remove();
                            if (!window.oldCriteria) window.oldCriteria = {};
                            window.oldCriteria[criterion.criterionId] = false;
                        };
                        // Add click-to-edit handler for label
                        const label = row.querySelector('.editable-criterion-label');
                        label.addEventListener('click', function(e) {
                            e.stopPropagation();
                            // Prevent multiple inputs
                            if (row.querySelector('input.edit-criterion-input')) return;
                            const oldName = label.textContent;
                            // Create input
                            const input = document.createElement('input');
                            input.type = 'text';
                            input.value = oldName;
                            input.className = 'form-control form-control-sm edit-criterion-input';
                            input.style.maxWidth = '300px';
                            label.style.display = 'none';
                            label.parentNode.appendChild(input);
                            input.focus();
                            // Save logic
                            function saveEdit() {
                                const newName = input.value.trim();
                                if (newName && newName !== oldName) {
                                    label.textContent = newName;
                                    // Track edited criteria for backend
                                    if (!window.editedCriteria) window.editedCriteria = {};
                                    window.editedCriteria[criterion.criterionId] = newName;
                                }
                                input.remove();
                                label.style.display = '';
                            }
                            input.addEventListener('blur', saveEdit);
                            input.addEventListener('keydown', function(ev) {
                                if (ev.key === 'Enter') {
                                    saveEdit();
                                } else if (ev.key === 'Escape') {
                                    input.remove();
                                    label.style.display = '';
                                }
                            });
                        });
                        criteriaContainer.appendChild(row);
                    });
                }
                const modal = new bootstrap.Modal(document.getElementById('editAssignmentModal'));
                modal.show();
                setTimeout(() => {
                    updateCounter();
                    // Attach event handler for inline criterion add
                    var input = document.getElementById('newCriterionInput');
                    if (input) {
                        // Remove previous listeners
                        input.onkeydown = null;
                        input.onblur = null;
                        input.addEventListener('keydown', function(e) {
                            if (e.key === 'Enter') {
                                var assignmentId = window.currentEditingAssignmentId;
                                addCriterionInline(input.value, assignmentId);
                            }
                        });
                        input.addEventListener('blur', function() {
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
        row.className = 'criteria-row';
        row.innerHTML = `<div class="form-check d-inline-block"><input class="form-check-input" type="checkbox" checked disabled><label class="form-check-label">${name}</label></div> <button type="button" class="btn btn-danger btn-sm ms-2 remove-criterion-btn" title="Eemalda kriteerium">X</button>`;
        row.querySelector('.remove-criterion-btn').onclick = function() {
            row.remove();
            window.newAddedCriteria = window.newAddedCriteria.filter(n => n !== name);
        };
        criteriaContainer.appendChild(row);
        var input = document.getElementById('newCriterionInput');
        input.value = '';
        input.focus();
    }

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

        const prepareUnfinishedFailingToggle = () => {
            const toggle = document.getElementById('filterUnfinishedFailingToggle');
            if (!toggle) return;

            function applyFilter() {
                const enabled = toggle.checked;
                // For each subject table, show/hide assignment rows
                $$('#subject-table').forEach(table => {
                    let anyVisible = false;
                    $$('tr', table).forEach(row => {
                        // header rows (contain <th>) should be handled after
                        if (row.querySelector('th')) return;
                        const val = row.dataset.unfinishedFailing === '1';
                        if (!enabled) {
                            row.style.display = '';
                            anyVisible = true;
                        } else {
                            if (val) {
                                row.style.display = '';
                                anyVisible = true;
                            } else {
                                row.style.display = 'none';
                            }
                        }
                    });
                    // show or hide header row based on anyVisible
                    $$('tr', table).forEach(row => {
                        if (row.querySelector('th')) {
                            row.style.display = anyVisible ? '' : 'none';
                        }
                    });
                    const spacer = table.previousElementSibling;
                    if (spacer && spacer.tagName === 'DIV') spacer.style.display = anyVisible ? '' : 'none';
                    table.style.display = anyVisible ? '' : 'none';
                });
            }

            toggle.addEventListener('change', applyFilter);
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
            prepareUnfinishedFailingToggle();
            setRedCellIntensity();
            updateBadges();
            initSorting();
        });
    })();
</script>
