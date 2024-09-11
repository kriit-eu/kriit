<style>

    #assignments-table th {
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

    .context-menu {
        display: none;
        position: absolute;
        z-index: 1000;
        width: 220px;
        background-color: white;
        border: 1px solid #ccc;
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
        padding: 10px;
    }

    .context-menu .grades,
    .context-menu .criteria {
        display: inline-block;
        vertical-align: top;
    }

    .context-menu .grades {
        width: 45%;
    }

    .context-menu .criteria {
        width: 50%;
    }

    .context-menu ul {
        list-style: none;
        padding: 0;
        margin: 0;
    }

    .context-menu ul li {
        padding: 8px 10px;
        cursor: pointer;
    }

    .context-menu ul li:hover {
        background-color: #f0f0f0;
    }

    .context-menu .form-check {
        margin: 5px 0;
    }

    .student-criteria-section h5 {
        margin-bottom: 10px;
    }

    .form-check {
        margin-bottom: 5px;
    }

</style>
<div>
    <div class="mb-3">
        <h2 class="mb-4"><?= $assignment['assignmentName'] ?></h2>
        <p class="mb-0"><?= $assignment['assignmentInstructions'] ?></p>
        <p class="mt-4 fw-bold">Tähtaeg: <?= $assignment['assignmentDueAt'] ?></p>

        <?php if ($isStudent): ?>
            <div class="d-flex justify-content-end mt-3">
                <button class="btn btn-primary" onclick="openStudentModal(true, <?= $this->auth->userId ?>)">Esita
                </button>
            </div>
        <?php else: ?>
            <div class="d-flex justify-content-end mt-3">
                <button class="btn btn-secondary" onclick="editAssignment()">Muuda</button>
            </div>
        <?php endif; ?>

    </div>


    <div class="p-3 mb-5 mt-5 border rounded bg-light" style="width: 30%;">
        <h5 class="mb-3">Kriteeriumid</h5>
        <form id="studentCriteriaForm">
            <?php foreach ($assignment['criteria'] as $criterion): ?>
                <?php
                $isCompleted = true;
                $studentId = $this->auth->userId;

                if ($isStudent && isset($assignment['students'][$studentId]['userDoneCriteria'][$criterion['criteriaId']])) {
                    $isCompleted = $assignment['students'][$studentId]['userDoneCriteria'][$criterion['criteriaId']]['completed'];
                }
                ?>
                <div class="form-check">
                    <input class="form-check-input" id="criterion_<?= $criterion['criteriaId'] ?>" type="checkbox"
                           name="criteria[<?= $criterion['criteriaId'] ?>]"
                           value="1" <?= $isCompleted ? 'checked' : '' ?>
                            <?= $isStudent ? '' : 'disabled' ?>>
                    <label class="form-check-label" for="criterion_<?= $criterion['criteriaId'] ?>">
                        <?= $criterion['criteriaName'] ?>
                    </label>
                </div>
            <?php endforeach; ?>
            <?php if ($isStudent): ?>
                <button type="button" class="btn btn-primary mt-3" onclick="saveStudentCriteria()">Salvesta</button>
            <?php endif; ?>
        </form>
    </div>

    <div class="modal fade" id="studentModal" tabindex="-1" aria-labelledby="studentModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="studentName"></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="solutionLink" class="form-label fw-bold">Lahenduse link</label>
                        <div id="solutionInputContainer">
                            <input type="text" id="solutionInput" class="form-control" placeholder="Sisesta link siia...">
                            <small id="solutionInputFeedback"></small>
                        </div>

                        <p id="solutionLinkContainer">
                            <a href="#" id="solutionLink" target="_blank" rel="noopener noreferrer">No link provided</a>
                        </p>
                    </div>
                    <div id="gradeSection" class="mb-3" style="display: none;">
                        <label class="form-label fw-bold">Hinne</label>
                        <div id="gradeRadioGroup" class="d-flex justify-content-around">
                            <div class="text-center">
                                <input class="form-check-input" type="radio" name="grade" id="grade5" value="5">
                                <label class="form-check-label d-block" for="grade5">5</label>
                            </div>
                            <div class="text-center">
                                <input class="form-check-input" type="radio" name="grade" id="grade4" value="4">
                                <label class="form-check-label d-block" for="grade4">4</label>
                            </div>
                            <div class="text-center">
                                <input class="form-check-input" type="radio" name="grade" id="grade3" value="3">
                                <label class="form-check-label d-block" for="grade3">3</label>
                            </div>
                            <div class="text-center">
                                <input class="form-check-input" type="radio" name="grade" id="grade2" value="2">
                                <label class="form-check-label d-block" for="grade2">2</label>
                            </div>
                            <div class="text-center">
                                <input class="form-check-input" type="radio" name="grade" id="grade1" value="1">
                                <label class="form-check-label d-block" for="grade1">1</label>
                            </div>
                            <div class="text-center">
                                <input class="form-check-input" type="radio" name="grade" id="gradeA" value="A">
                                <label class="form-check-label d-block" for="gradeA">A</label>
                            </div>
                            <div class="text-center">
                                <input class="form-check-input" type="radio" name="grade" id="gradeMA" value="MA">
                                <label class="form-check-label d-block" for="gradeMA">MA</label>
                            </div>
                        </div>
                    </div>
                    <div id="criteriaContainer">
                        <h6 class="fw-bold">Kriteeriumid</h6>
                        <div id="checkboxesContainer">
                        </div>
                    </div>
                    <div id="commentSection" class="mb-3" style="display: none;">
                        <label for="studentComment" class="form-label fw-bold">Kommentaar</label>
                        <textarea class="form-control" id="studentComment" rows="3"
                                  placeholder="Lisa kommentaar siia..."></textarea>
                    </div>

                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary" id="submitButton">Salvesta muudatused</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tühista</button>
                </div>
            </div>
        </div>
    </div>

    <div id="context-menu" class="context-menu">
        <div class="grades">
            <ul>
                <li onclick="setGrade(5)">5</li>
                <li onclick="setGrade(4)">4</li>
                <li onclick="setGrade(3)">3</li>
                <li onclick="setGrade(2)">2</li>
                <li onclick="setGrade(1)">1</li>
                <li onclick="setGrade('MA')">MA</li>
            </ul>
        </div>
        <div class="criteria">
        </div>
    </div>

    <div>
        <table id="assignments-table" class="table table-bordered mt-5 mb-10" style="margin-top: 5em !important;">
            <thead>
            <tr>
                <?php foreach ($assignment['students'] as $s): ?>
                    <th data-bs-toggle="tooltip" title="<?= $s['studentName'] ?>">
                        <?= $s['initials'] ?>
                    </th>
                <?php endforeach; ?>
            </tr>
            </thead>
            <tbody>
            <tr>
                <?php foreach ($assignment['students'] as $s): ?>
                    <td class="<?= $s['class'] ?> text-center"
                        data-bs-toggle="tooltip"
                        title="<?= $s['tooltipText'] ?>"
                            <?php if (!$isStudent): ?>
                                oncontextmenu="showContextMenu(event, <?= $s['studentId'] ?>)"
                            <?php endif; ?>
                        onclick="openStudentModal(<?= $isStudent ? 'true' : 'false' ?>, <?= $s['studentId'] ?>)">
                        <?= $s['grade'] ?? '' ?>
                        <?php if ($s['assignmentStatusName'] !== 'Esitamata'): ?>
                            <span style="font-size: 8px"><?= $s['userDoneCriteriaCount'] ?>/<?= count($assignment['criteria']) ?></span>
                        <?php endif; ?>
                    </td>
                <?php endforeach; ?>
            </tr>
            </tbody>
        </table>
    </div>


    <div class="container mt-10 mb-4" style="margin-top: 15em !important;">
        <div class="card">
            <div class="card-body">
                <?php foreach ($assignment['messages'] as $message): ?>
                    <div class="d-flex align-items-start mb-3 border rounded p-3">
                        <div class="flex-shrink-0 me-3">
                            <span class="avatar bg-primary text-white rounded-circle p-2">
                        <?= strtoupper(substr($message['userName'], 0, 1)) ?>
                    </span>
                        </div>
                        <div class="flex-grow-1">
                            <div class="d-flex justify-content-between">
                                <h6 class="fw-bold mb-1"><?= $message['userName'] ?></h6>
                                <small class="text-muted"><?= $message['createdAt'] ?></small>
                            </div>
                            <p class="mb-1"><?= $message['content'] ?></p>

                            <?php if ($this->auth->userId !== $message['userId']): ?>
                                <div class="d-flex justify-content-end">
                                    <button type="button" class="btn btn-secondary btn-sm"
                                            style="font-size: 0.75rem; padding: 2px 8px;"
                                            onclick="replyToMessage(<?= $message['messageId'] ?>)">Vasta
                                    </button>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <div class="container mt-3 mb-5">
        <form>
            <div class="mb-3">
                <label for="messageContent" class="form-label">Sisesta sõnum</label>
                <textarea class="form-control" id="messageContent" name="content" rows="3"
                          placeholder="Kirjuta oma sõnum siia..."></textarea>
            </div>
            <div class="d-flex justify-content-end">
                <button type="button" class="btn btn-primary" onclick="submitMessage()">Postita</button>
            </div>
        </form>
    </div>

</div>
<script>
    const assignment = <?= json_encode($assignment) ?>;
    let currentStudentId = null;

    function showContextMenu(event, studentId) {
        event.preventDefault();
        currentStudentId = studentId;

        const menu = document.getElementById('context-menu');
        const criteriaContainer = menu.querySelector('.criteria');

        criteriaContainer.innerHTML = '';

        const student = assignment.students[studentId];
        const allCriteria = assignment.criteria;

        if (student && allCriteria) {
            Object.keys(assignment.criteria).forEach(criteriaId => {
                const criterion = assignment.criteria[criteriaId];
                const isCompleted = assignment.students[studentId]?.userDoneCriteria[criteriaId]?.completed;

                criteriaContainer.innerHTML += `
    <div class="form-check">
        <input class="form-check-input" type="checkbox" id="criteria_${criteriaId}" ${isCompleted ? 'checked' : ''}>
        <label class="form-check-label" for="criteria_${criteriaId}">
            ${criterion.criteriaName}
        </label>
    </div>
    `;
            });
        }

        menu.style.display = 'block';
        menu.style.left = `${event.pageX}px`;
        menu.style.top = `${event.pageY}px`;

        adjustDropdownPosition(menu, event.pageX, event.pageY);

        document.addEventListener('click', hideContextMenu);
    }

    function adjustDropdownPosition(menu, pageX, pageY) {
        const menuRect = menu.getBoundingClientRect();
        const windowWidth = window.innerWidth;
        const windowHeight = window.innerHeight;

        if (menuRect.right > windowWidth) {
            menu.style.left = `${pageX - menuRect.width}px`;
        }

        if (menuRect.bottom > windowHeight) {
            menu.style.top = `${pageY - menuRect.height}px`;
        }
    }

    function hideContextMenu() {
        const menu = document.getElementById('context-menu');
        menu.style.display = 'none';
        document.removeEventListener('click', hideContextMenu);
    }

    // Universal modal open function
    function openStudentModal(isStudent, studentId = null) {
        const modalTitle = document.getElementById('studentName');
        const solutionInputContainer = document.getElementById('solutionInputContainer');
        const solutionInput = document.getElementById('solutionInput');
        const solutionLinkContainer = document.getElementById('solutionLinkContainer');
        const gradeSection = document.getElementById('gradeSection');
        const commentSection = document.getElementById('commentSection');
        const submitButton = document.getElementById('submitButton');
        const criteriaContainer = document.getElementById('checkboxesContainer');
        const student = assignment.students[studentId];

        currentStudentId = studentId;

        if (student.grade) {
            document.querySelector(`#gradeRadioGroup input[value="${student.grade}"]`).checked = true;
        } else {
            document.querySelectorAll('#gradeRadioGroup input[type="radio"]').forEach(rb => {
                rb.checked = false;
            });
        }

        if (student.comment) {
            document.getElementById('studentComment').value = student.comment;
        } else {
            document.getElementById('studentComment').value = ''; // Очищаем поле комментария
        }

        if (isStudent) {
            modalTitle.textContent = 'Sisesta Lahendus';
            gradeSection.style.display = 'none'; // Hide the grade section for students
            commentSection.style.display = 'none'; // Hide the comment section for students
            solutionInputContainer.style.display = 'block'; // Show the input for students to enter a link
            submitButton.textContent = 'Esita';
            submitButton.disabled = true; // Initially disable the "Esita" button

            document.getElementById('checkboxesContainer').addEventListener('change', function(event) {
                if (event.target && event.target.type === 'checkbox') {
                    updateSubmitButtonState();
                }
            });

            solutionInput.addEventListener('input', updateSubmitButtonState);

            async function updateSubmitButtonState() {

                const solutionLinkValue = solutionInput.value.trim();

                const isValidUrl = isValidURL(solutionLinkValue);

                const solutionInputFeedback = document.getElementById('solutionInputFeedback');

                if (!isValidUrl) {
                    solutionInputFeedback.textContent = 'Sisestatud link pole kehtiv. Palun sisestage kehtiv link.';
                    solutionInputFeedback.style.color = 'red';
                    submitButton.disabled = true;
                    return;
                }
                let isValid = isValidUrl;
                const knownServicesPattern = /(github\.com|bitbucket\.org|gitlab\.com|docs\.google\.com)/i;
                const isKnownService = knownServicesPattern.test(solutionLinkValue);

                if (isKnownService) {
                    const isAccessible = await isLinkAccessible(solutionLinkValue);
                    console.log('Is Link Accessible:', isAccessible);

                    if (!isAccessible) {
                        solutionInputFeedback.textContent = 'Sisestatud link pole kättesaadav. Kontrollige, kas see on privaatne või vale link.';
                        solutionInputFeedback.style.color = 'red';
                        submitButton.disabled = true;
                        return;
                    }
                    isValid = isAccessible;
                }

                const allChecked = Array.from(document.querySelectorAll('#checkboxesContainer input[type="checkbox"]'))
                    .every(cb => cb.checked);

                solutionInputFeedback.textContent = '';
                submitButton.disabled = !(allChecked && isValid );
            }



        } else {
            modalTitle.textContent = student.studentName;
            gradeSection.style.display = 'block';
            commentSection.style.display = 'block';
            solutionInputContainer.style.display = 'none';
            submitButton.textContent = 'Salvesta';
            submitButton.disabled = false;

        }

        if (student.solutionLink) {
            solutionLinkContainer.innerHTML = `<a href="${student.solutionLink}" id="solutionLink" target="_blank" rel="noopener noreferrer">${student.solutionLink}</a>`;
        } else {
            solutionLinkContainer.innerHTML = 'Link puudub';  // Display plain text if no link
        }

        criteriaContainer.innerHTML = '';

        Object.keys(assignment.criteria).forEach(criteriaId => {
            const criterion = assignment.criteria[criteriaId];
            const isCompleted = assignment.students[studentId]?.userDoneCriteria[criteriaId]?.completed;

            criteriaContainer.innerHTML += `
            <div class="form-check">
                <input class="form-check-input" type="checkbox" id="criteria_${criteriaId}" ${isCompleted ? 'checked' : ''}>
                <label class="form-check-label" for="criteria_${criteriaId}">
                    ${criterion.criteriaName}
                </label>
            </div>
            `;
        });

        const modal = new bootstrap.Modal(document.getElementById('studentModal'));
        modal.show();
    }

    document.querySelectorAll('#criteriaContainer input[type="checkbox"]').forEach(checkbox => {
        checkbox.addEventListener('change', () => {
            const allChecked = Array.from(document.querySelectorAll('#criteriaContainer input[type="checkbox"]'))
                .every(cb => cb.checked);
            document.getElementById('submitButton').disabled = !allChecked;
        });
    });

    document.getElementById('submitButton').addEventListener('click', function () {
        if (submitButton.textContent === 'Esita') {
            const solutionLink = solutionInput.value;
            const criteria = getCriteriaList();

            ajax(`assignments/saveStudentSolutionLink`, {
                    assignmentId: assignment.assignmentId,
                    studentId: currentStudentId,
                    solutionLink: solutionLink,
                    criteria: criteria
                },
                function (res) {
                    if (res.status === 200) {
                        location.reload();
                    } else {
                        alert('Tekkis viga serveriga suhtlemisel.');
                    }
                }
            );
        } else {
            const grade = document.querySelector('#gradeSection input[type="radio"]:checked')?.value;
            console.log(grade);
            const criteria = getCriteriaList();

            const comment = document.getElementById('studentComment').value;

            ajax(`assignments/saveAssignmentGrade`, {
                    assignmentId: assignment.assignmentId,
                    studentId: currentStudentId,
                    grade: grade,
                    criteria: criteria,
                    comment: comment,
                    teacherId: assignment.teacherId
                },
                function (res) {
                    if (res.status === 200) {
                        location.reload();
                    } else {
                        alert('Tekkis viga serveriga suhtlemisel.');
                    }
                }
            );
        }
    });

    document.addEventListener('DOMContentLoaded', function () {
        [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
            .map(el => new bootstrap.Tooltip(el));
    });

    function saveStudentCriteria() {
        const formData = new FormData(document.getElementById('studentCriteriaForm'));

    }

    function getCriteriaList() {
        const criteria = {};
        document.querySelectorAll('#criteriaContainer input[type="checkbox"]').forEach(cb => {
            criteria[parseInt(cb.id.replace('criteria_', ''))] = cb.checked;
        });
        return criteria;
    }

    function isValidURL(string) {
        const urlPattern = new RegExp(
            '^(https?:\\/\\/)?' + // validate protocol (optional)
            '((([a-zA-Z0-9-]+\\.)+[a-zA-Z]{2,})|' + // validate domain name
            '(\\d{1,3}\\.){3}\\d{1,3})' + // OR validate IP (IPv4)
            '(\\:(6553[0-5]|655[0-2][0-9]|65[0-4][0-9]{2}|6[0-4][0-9]{3}|[1-5][0-9]{4}|[1-9][0-9]{0,3}))?' + // port (optional)
            '(\\/[-a-zA-Z0-9%_.~+]*)*' + // path (optional)
            '(\\?[;&a-zA-Z0-9%_.~+=-]*)?' + // query string (optional)
            '(\\#[-a-zA-Z0-9%_.~+=]*)?$', // fragment (optional)
            'i' // case-insensitive
        );
        return !!urlPattern.test(string);
    }

    async function isLinkAccessible(url) {
        try {
            const response = await fetch(url, {method: 'GET', mode: 'cors'});
            return response.ok || response.status === 200;
        } catch (error) {
            return false;
        }
    }
</script>
