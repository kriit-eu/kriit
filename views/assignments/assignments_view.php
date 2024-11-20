<style>

    .red-cell {
        background-color: rgb(255, 180, 176) !important;
    }

    .yellow-cell {
        background-color: #fff8b3 !important;
    }

    .modal-body {
        word-wrap: break-word;
        word-break: break-all;
    }

    #solutionUrl {
        display: inline-block;
        max-width: 100%;
        overflow-wrap: break-word;
        word-break: break-all;
    }

    .modal-dialog {
        max-width: 800px;
        width: 100%;
    }

    .modal-content {
        padding: 15px;
    }

    .text-center {
        text-align: center;
    }

    .context-menu {
        display: none;
        position: absolute;
        z-index: 1000;
        width: 250px;
        background-color: white;
        border: 1px solid #ccc;
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
        padding: 10px;
        overflow-x: auto;
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
        width: 45%;
        max-width: 100%;
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
        margin: 5px 2px 5px 0 !important;
    }

    .context-menu .form-check label {
        margin-right: 10px;
    }

    .student-criteria-section h5 {
        margin-bottom: 10px;
    }

    .form-check {
        margin-bottom: 5px;
    }

    .criteria-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 5px 0;
    }

    .criteria-row .form-check {
        flex-grow: 1;
    }

    .criteria-row button {
        margin-left: 10px;
    }

    .clickable-cells-row {
        cursor: pointer;
    }

    .assignments-body {
        display: flex;
        flex-wrap: nowrap;
        overflow-x: auto;
        border: 1px solid #ccc;
        border-radius: 8px;
    }

    .assignment-item {
        display: flex;
        flex-direction: column;
        margin: 0;
        flex: 1 0 0;
    }

    .header-item, .body-item {
        text-align: center;
        padding: 10px;
        box-sizing: border-box;
        border-bottom: 1px solid #ccc;
        min-height: 50px;
        background-color: inherit;
        white-space: nowrap;
    }

    .header-item {
        background-color: #f2f2f2;
    }

    .adaptive-background {
        width: 100%;
        max-width: 500px;
        min-width: 250px;
        padding: 15px;
        margin: 0;
        background-color: #f8f9fa;
        border: 1px solid #ccc;
        border-radius: 8px;
    }

    #notificationContainer {
        max-height: 500px;
        border: 2px solid #4a90e2;
        background-color: #e8f4ff;
        box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.1);
        border-radius: 8px;
        padding: 5px;
        margin-bottom: 20px;
    }

    #notificationContainer .content-part {
        max-height: 400px;
        overflow-y: auto;
        padding: 5px;
    }

    .notification-item {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        margin-bottom: 10px;
        padding: 5px;
        border-radius: 5px;
        background-color: #fff;
        box-shadow: 0px 2px 5px rgba(0, 0, 0, 0.05);
        transition: background-color 0.3s ease;
    }

    .notification-item:hover {
        background-color: #f0f8ff;
    }

    .notification-icon {
        margin-right: 2px;
        color: #4a90e2;
        font-size: 24px;
    }

    .notification-text {
        flex-grow: 1;
        font-size: 14px;
        color: #333;
    }

    .notification-time {
        font-size: 12px;
        color: #777;
        margin-left: 2px;
    }

    #messageContainer {
        max-height: 600px;
        overflow-x: hidden;
        border: 1px solid #ccc;
    }

    .content-part {
        max-height: 300px;
        overflow-y: auto;
        word-wrap: break-word;
        word-break: break-word;
        overflow-wrap: break-word;
        white-space: normal;
    }

    #messageContainer .content-part {
        max-height: 500px;
    }

    .card-body {
        word-wrap: break-word; /* Ensure long words break and wrap to the next line */
    }


    /* Grades column */
    #assignments-container {
        flex: 1;
        max-width: 100%;
        overflow-x: auto /* Set a max-width for the grades section */
        border-radius: 8px

    }

    #messages-container {
        flex: 2; /* Take the remaining space */
        display: flex;
        flex-direction: column;
        gap: 2em;
        margin-top: 3em;
    }

    @media (min-width: 769px) {
        .adaptive-background {
            margin-left: 0;
        }

        #messageContainer {
            max-width: 100%; /* Ensures it doesn't take the full width */
        }
    }

    .pre-wrap {
        white-space: pre-wrap;
        text-align: left;
    }

    .comment-row {
        border: 1px solid #ddd;
        padding: 5px;
        margin-bottom: 5px;
        border-radius: 3px;
        background-color: #f9f9f9;
    }
    .comment-name {
        font-weight: bold;
        color: #333;
        margin-bottom: 2px;
    }
    .comment-text {
        margin-top: 2px;
        color: #555;
    }
    .comment-date {
        margin-top: 2px;
        font-size: 0.8em;
        color: #999;
    }
</style>

<div>
    <div class="mb-3">
        <h2 class="mb-4"><?= $assignment['assignmentName'] ?></h2>
        <p class="mb-0"><?= $assignment['assignmentInstructions'] ?></p>
        <p class="mt-4 fw-bold">Tähtaeg: <?= $assignment['assignmentDueAt'] ?></p>

        <?php if ($isStudent): ?>
            <div class="d-flex justify-content-end mt-3">
                <span <?php if ($assignment['students'][$this->auth->userId]['isDisabledStudentActionButton'] === 'disabled'): ?>
                    data-bs-toggle="tooltip" title="
                            <?php if ($assignment['students'][$this->auth->userId]['isAllCriteriaCompleted']): ?>
                            Ülesanne on juba hinnatud
                            <?php else: ?>
                            Kõik kriteeriumid pole sul veel märgitud valmis
                            <?php endif; ?>"
                <?php endif; ?>>
                <button class="btn btn-primary"
                        onclick="openStudentModal(true, <?= $this->auth->userId ?>)" <?= $assignment['students'][$this->auth->userId]['isDisabledStudentActionButton'] ?>>
                    <?= $assignment['students'][$this->auth->userId]['studentActionButtonName'] ?>
                </button>
                </span>
            </div>
        <?php else: ?>
            <div class="d-flex justify-content-end mt-3">
                <button class="btn btn-secondary" onclick="editAssignment()">Muuda</button>
            </div>
        <?php endif; ?>

    </div>


    <div class="adaptive-background p-3 mb-5 mt-5">
        <h5 class="mb-3">Kriteeriumid</h5>
        <form id="studentCriteriaForm">
            <div id="requiredCriteria">
                <?php foreach ($assignment['criteria'] as $criterion): ?>
                    <?php
                    $isCompleted = true;
                    $studentId = $this->auth->userId;

                    if ($isStudent && isset($assignment['students'][$studentId]['userDoneCriteria'][$criterion['criteriaId']])) {
                        $isCompleted = $assignment['students'][$studentId]['userDoneCriteria'][$criterion['criteriaId']]['completed'];
                    }
                    ?>
                    <div class="form-check">
                        <input class="form-check-input" id="criterion_<?= $criterion['criteriaId'] ?>"
                               type="checkbox"
                               name="criteria[<?= $criterion['criteriaId'] ?>]"
                               value="1" <?= $isCompleted ? 'checked' : '' ?>
                            <?= $isStudent ? '' : 'disabled' ?>>
                        <label class="form-check-label" for="criterion_<?= $criterion['criteriaId'] ?>">
                            <?= $criterion['criteriaName'] ?>
                        </label>
                    </div>
                <?php endforeach; ?>
            </div>
            <?php if ($isStudent): ?>
                <button type="button" class="btn btn-primary mt-3" onclick="saveStudentCriteria()" hidden="hidden">
                    Salvesta
                </button>
            <?php endif; ?>
        </form>
    </div>

    <?php if (!$isStudent): ?>
        <div class="modal fade " id="editAssignmentModal" tabindex="-1" aria-labelledby="editAssignmentModalLabel"
             aria-hidden="true">
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
                                <input type="text" class="form-control" id="assignmentName" name="assignmentName"
                                       value="<?= $assignment['assignmentName'] ?>">
                            </div>
                            <div class="mb-3">
                                <label for="assignmentInstructions" class="form-label">Instruktsioon</label>
                                <textarea class="form-control" id="assignmentInstructions" name="assignmentInstructions"
                                          rows="3"><?= $assignment['assignmentInstructions'] ?></textarea>
                            </div>
                            <div class="mb-3">
                                <label for="assignmentDueAt" class="form-label">Tähtaeg</label>
                                <input type="date" class="form-control" id="assignmentDueAt" name="assignmentDueAt"
                                       value="<?= (!empty($assignment['assignmentDueAt']) && strtotime($assignment['assignmentDueAt']) > 0) ? date('Y-m-d', strtotime($assignment['assignmentDueAt'])) : "" ?>">
                            </div>


                            <!-- Block for criteria management -->
                            <div class="mb-3">
                                <h5>Kriteeriumid</h5>
                                <div id="editCriteriaContainer">
                                    <?php foreach ($assignment['criteria'] as $criterion): ?>
                                        <div class="criteria-row">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox"
                                                       id="edit_criterion_<?= $criterion['criteriaId'] ?>" checked
                                                       disabled>
                                                <label class="form-check-label"
                                                       for="edit_criterion_<?= $criterion['criteriaId'] ?>">
                                                    <?= $criterion['criteriaName'] ?>
                                                </label>
                                            </div>
                                            <button type="button" class="btn btn-danger btn-sm"
                                                    onclick="removeOldCriterion(<?= $criterion['criteriaId'] ?>)">X
                                            </button>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                                <button type="button" class="btn btn-primary mt-2" id="addCriterionButton">Lisa
                                    kriteerium
                                </button>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-primary" onclick="saveEditedAssignment()">Salvesta</button>
                        <button type="button" class="btn btn-secondary" onclick="location.reload()"
                                data-bs-dismiss="modal">Tühista
                        </button>
                    </div>
                </div>
            </div>
        </div>
        <div class="modal fade" id="addCriterionModal" tabindex="-1" aria-labelledby="addCriterionModalLabel"
             aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="addCriterionModalLabel">Lisa uus kriteerium</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form id="addCriterionForm">
                            <div class="mb-3">
                                <label for="newCriterionName" class="form-label">Kriteeriumi nimi</label>
                                <textarea class="form-control" id="newCriterionName" name="newCriterionName" rows="3"
                                          placeholder="Sisestage kriteeriumi nimi"></textarea>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-primary" onclick="addNewCriterion()">Lisa</button>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tühista</button>
                    </div>
                </div>
            </div>
        </div>

    <?php endif; ?>

    <div class="modal fade" id="studentModal" tabindex="-1" aria-labelledby="studentModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="studentName"></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="solutionUrl" class="form-label fw-bold">Lahenduse link</label>
                        <div id="solutionInputContainer">
                            <input type="text" id="solutionInput" class="form-control"
                                   placeholder="Sisesta link siia...">
                            <small id="solutionInputFeedback"></small>
                        </div>

                        <p class="mt-1" id="solutionUrlContainer">
                            <a href="#" id="solutionUrl" target="_blank" rel="noopener noreferrer">No link provided</a>
                        </p>
                    </div>
                    <?php if (!$isStudent): ?>
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
                    <?php endif; ?>
                    <div id="studentGradeCriteriaContainer">
                        <h6 class="fw-bold">Kriteeriumid</h6>
                        <div id="checkboxesContainer">
                        </div>
                    </div>
                    <div id="commentSection" class="mb-3">
                        <label for="studentComment" class="form-label fw-bold">Kommentaar</label>
                        <div id="commentsContainer"></div>
                        <textarea class="form-control" id="studentComment" rows="3"
                                  placeholder="Lisa kommentaar siia..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary" id="submitButton">
                        Salvesta muudatused
                    </button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tühista</button>
                </div>
            </div>
        </div>
    </div>

    <?php if (!$isStudent): ?>
        <div id="context-menu" class="context-menu">
            <div class="grades">
                <ul>
                    <li onclick="setGrade(5)">5</li>
                    <li onclick="setGrade(4)">4</li>
                    <li onclick="setGrade(3)">3</li>
                    <li onclick="setGrade(2)">2</li>
                    <li onclick="setGrade(1)">1</li>
                    <li onclick="setGrade('A')">A</li>
                    <li onclick="setGrade('MA')">MA</li>
                </ul>
            </div>
            <div class="criteria">
            </div>
        </div>
    <?php endif; ?>

    <div id="main-container">
        <div id="assignments-container">
            <div class="assignments-body">
                <?php foreach ($assignment['students'] as $s): ?>
                <div>
                    <div class="header-item" data-bs-toggle="tooltip" title="<?= $s['studentName'] ?>" style="<?= $s['studentId'] !== array_key_first($assignment['students']) ? 'border-left: 1px solid #ccc;' : '' ?>">
                        <?= $s['initials'] ?>
                </div>
                <div class="body-item <?= $s['class'] ?> text-center clickable-cells-row"
                     data-bs-toggle="tooltip"
                     style="<?= $s['studentId'] !== array_key_first($assignment['students']) ? 'border-left: 1px solid #ccc;' : '' ?>"
                     title="<?= $s['tooltipText'] ?>"
                    <?php if (!$isStudent): ?>
                        oncontextmenu="showContextMenu(event, <?= $s['studentId'] ?>)"
                    <?php endif; ?>
                     onclick="openStudentModal(<?= $isStudent ? 'true' : 'false' ?>, <?= $s['studentId'] ?>)">
                    <?= $s['grade'] ?? '' ?>
                    <?php if ($s['assignmentStatusName'] !== 'Esitamata'): ?>
                        <span style="font-size: 8px"><?= $s['userDoneCriteriaCount'] ?>/<?= count($assignment['criteria']) ?></span>
                    <?php endif; ?>
                </div>
            </div>
            <?php if ($isStudent): ?>
                <div class="assignment-item">
                    <div class="header-item">Kommentaarid</div>
                    <div>
                        <?php foreach ($s['comments'] as $comment): ?>
                            <div class="comment-row p-2 border rounded bg-light mb-2">
                                <div class="comment-name fw-bold text-dark mb-1"><?= isset($comment['name']) ? $comment['name'] : 'Tundmatu' ?></div>
                                <div class="comment-text text-muted"><?= nl2br(htmlspecialchars($comment['comment'])) ?></div>
                                <div class="comment-date text-secondary small"><?= $comment['createdAt'] ?></div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

            <?php endif; ?>
            <?php endforeach; ?>
        </div>
    </div>
    <div id="messages-container">
        <div id="notificationContainer" class="card mt-3">
            <div class="card-body">
                <h5>Sündmused</h5>
                <div class="content-part">
                    <?php foreach ($assignment['messages'] as $message): ?>
                        <?php if ($message['isNotification']): ?>
                            <div class="notification-item">
                                <i class="fa fa-bell notification-icon"></i>
                                <div class="notification-text">
                                    <p class="fw-bold mb-1"><?= $message['content'] ?></p>
                                </div>
                                <small class="notification-time text-muted"><?= $message['createdAt'] ?></small>
                            </div>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <div id="messageContainer" class="card">
            <div class="card-body">
                <h5>Vestlus</h5>
                <div class="content-part">
                    <?php foreach ($assignment['messages'] as $message): ?>
                        <?php if (!$message['isNotification']): ?>
                            <div class="d-flex align-items-start mb-3 border rounded p-3">
                                <div class="flex-shrink-0 me-3">
                                    <span class="avatar bg-primary text-white rounded-circle p-2"><?= strtoupper(substr($message['userName'], 0, 1)) ?></span>
                                </div>
                                <div class="flex-grow-1">
                                    <div class="d-flex flex-wrap justify-content-between">
                                        <h6 class="fw-bold mb-1"><?= $message['userName'] ?></h6>
                                        <small class="text-muted"><?= $message['createdAt'] ?></small>
                                    </div>
                                    <p class="mb-1"><?= strip_tags($message['content'], '<br><ul><ol><h2><li><h3><p><strong>') ?></p>
                                    <?php if ($this->auth->userId !== $message['userId']): ?>
                                        <div class="d-flex justify-content-end">
                                            <button type="button" class="btn btn-secondary btn-sm"
                                                    style="font-size: 0.75rem; padding: 2px 8px;"
                                                    onclick='replyToMessage(<?= json_encode($message['userName']) ?>, <?= $message['messageId'] ?>, <?= json_encode($message['content']) ?>, "<?= $message['createdAt'] ?>")'>
                                                Vasta
                                            </button>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>


        <div class="container mb-5">
            <form>
                <div class="mb-3">
                    <label for="messageContent" class="form-label">Sisesta sõnum</label>
                    <div id="replyInfo" class="alert alert-info " style="display:none;">
                        <div class="d-flex justify-content-end">
                            <button type="button" style="font-size: 0.75rem; padding: 2px 8px;"
                                    class="btn btn-sm btn-secondary mb-2" onclick="cancelReply()">x
                            </button>
                        </div>
                        <div id="replyMessage" class="border rounded bg-light p-2 mb-2"></div>
                    </div>
                    <textarea class="form-control" id="messageContent" name="content" rows="3"
                              placeholder="Kirjuta oma sõnum siia..."></textarea>
                </div>
                <div class="d-flex justify-content-end">
                    <button type="button" class="btn btn-primary" onclick="submitMessage()">Postita</button>
                </div>
            </form>
        </div>

    </div>

</div>
<script>
    const assignment = <?= json_encode($assignment) ?>;
    let currentStudentId = null;
    let newAddedCriteria = [];

    document.addEventListener('DOMContentLoaded', function () {
        initializeTooltips();
        scrollToBottom();
    });

    function initializeTooltips() {
        const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        const tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
    }


    document.getElementById('requiredCriteria').addEventListener('change', function (event) {
        if (event.target && event.target.type === 'checkbox') {
            document.querySelector('#studentCriteriaForm .btn-primary').hidden = false;
        }
    });

    document.getElementById('submitButton').addEventListener('click', function () {
        const gradeRadioGroup = document.querySelectorAll('#gradeRadioGroup input[type="radio"]');
        let gradeSelected = false;

        gradeRadioGroup.forEach(radio => {
            if (radio.checked) {
                gradeSelected = true;
            }
        });

        <?php if(!$isStudent): ?>
        if (!gradeSelected) {
            alert('Palun vali hinne.');
            return;
        }
        <?php endif ?>

        if (submitButton.textContent === 'Esita' || submitButton.textContent === 'Muuda') {
            const solutionUrl = solutionInput.value;
            const criteria = getCriteriaList();
            const comment = document.getElementById('studentComment').value;

            ajax(`assignments/saveStudentSolutionUrl`, {
                    assignmentId: assignment.assignmentId,
                    studentId: <?=$this->auth->userId?>,
                    studentName: assignment.students[<?=$this->auth->userId?>].studentName,
                    solutionUrl: solutionUrl,
                    criteria: criteria,
                    teacherId: assignment.teacherId,
                    teacherName: assignment.teacherName,
                    comment: comment
                },
                function (res) {
                    if (res.status === 200) {
                        location.reload();
                    }
                }, function (error) {
                    alert(error ?? 'Tekkis viga serveriga suhtlemisel.');
                });

        } else {
            const grade = document.querySelector('#gradeSection input[type="radio"]:checked')?.value;
            const criteria = getCriteriaList();

            const comment = document.getElementById('studentComment').value;

            ajax(`assignments/saveAssignmentGrade`, {
                    assignmentId: assignment.assignmentId,
                    studentId: currentStudentId,
                    grade: grade,
                    criteria: criteria,
                    comment: comment,
                    teacherId: assignment.teacherId,
                    teacherName: assignment.teacherName,
                    studentName: assignment.students[currentStudentId].studentName
                },
                function (res) {
                    if (res.status === 200) {
                        location.reload();
                    }
                }, function (error) {
                    alert(error ?? 'Tekkis viga serveriga suhtlemisel.');
                });
        }
    });

    document.querySelectorAll('#studentGradeCriteriaContainer input[type="checkbox"]').forEach(checkbox => {
        checkbox.addEventListener('change', () => {
            const allChecked = Array.from(document.querySelectorAll('#studentGradeCriteriaContainer input[type="checkbox"]'))
                .every(cb => cb.checked);
            document.getElementById('submitButton').disabled = !allChecked;
        });
    });

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
                    <input class="form-check-input" type="checkbox" id="check_criterion_${criteriaId}" ${isCompleted ? 'checked' : ''}>
                    <label class="form-check-label" for="check_criterion_${criteriaId}">
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

    function openStudentModal(isStudent, studentId = null) {
        const modalTitle = document.getElementById('studentName');
        const solutionInputContainer = document.getElementById('solutionInputContainer');
        const solutionInput = document.getElementById('solutionInput');
        const solutionUrlContainer = document.getElementById('solutionUrlContainer');
        const submitButton = document.getElementById('submitButton');
        const criteriaContainer = document.getElementById('checkboxesContainer');
        const commentsContainer = document.getElementById('commentsContainer'); // Container for comments
        const student = assignment.students[studentId];
        currentStudentId = studentId;

        // Reset comments container to ensure it's cleared before populating with new comments
        commentsContainer.innerHTML = '';

        // Populate comments section with Bootstrap cards
        if (student && student.comments && student.comments.length > 0) {
            student.comments.forEach(comment => {
                const card = document.createElement('div');
                card.classList.add('card', 'mb-3'); // Add Bootstrap card classes

                // Card body
                const cardBody = document.createElement('div');
                cardBody.classList.add('card-body');

                // Add comment content inside the card body
                const commentContent = document.createElement('p');
                commentContent.innerHTML = `
                    ${comment.createdAt} <strong>${comment.name || 'Tundmatu'}</strong><br>
                    <em>${comment.comment}</em>
                `;
                cardBody.appendChild(commentContent);

                // Append the card body to the card
                card.appendChild(cardBody);

                // Append the card to the comments container
                commentsContainer.appendChild(card);
            });
        } else {
            commentsContainer.innerHTML = '<p>Kommentaare pole.</p>';
        }

        if (isStudent) {
            modalTitle.textContent = 'Sisesta Lahendus';
            solutionInputContainer.style.display = 'block'; // Show the input for students to enter a link
            submitButton.textContent = student.studentActionButtonName;
            submitButton.disabled = true; // Initially disable the "Esita" button

            solutionInput.addEventListener('input', updateSubmitButtonState);

            document.getElementById('checkboxesContainer').addEventListener('change', function (event) {
                if (event.target && event.target.type === 'checkbox') {
                    updateButtonState();
                }
            });

            solutionInput.addEventListener('input', updateSubmitButtonState);

            let isValidUrl = false;

            async function updateSubmitButtonState() {
                const solutionUrlValue = solutionInput.value.trim();
                const solutionInputFeedback = document.getElementById('solutionInputFeedback');

                if (solutionUrlValue === '') {
                    solutionInputFeedback.textContent = '';
                    submitButton.disabled = true;
                    return;
                }
                try {
                    ajax('assignments/validateAndCheckLinkAccessibility', {
                        solutionUrl: solutionUrlValue
                    }, function (res) {
                        if (res.status === 200) {
                            solutionInputFeedback.textContent = 'Link on valideeritud ja kättesaadav.';
                            solutionInputFeedback.style.color = 'green';
                            isValidUrl = true;
                            updateButtonState();
                        }
                    }, function (error) {
                        solutionInputFeedback.textContent = error || 'Link on vigane või kättesaamatu.';
                        solutionInputFeedback.style.color = 'red';
                        isValidUrl = false;
                        updateButtonState();
                    });
                } catch (error) {
                    solutionInputFeedback.textContent = 'Tekkis viga URL-i valideerimisel';
                    solutionInputFeedback.style.color = 'red';
                    isValidUrl = false;
                    updateButtonState();
                }

                updateButtonState();
            }

            function updateButtonState() {
                const allChecked = Array.from(document.querySelectorAll('#checkboxesContainer input[type="checkbox"]'))
                    .every(cb => cb.checked);

                submitButton.disabled = !(allChecked && isValidUrl && student.isDisabledStudentActionButton === '');
            }

        } else {
            const gradeSection = document.getElementById('gradeSection');
            const commentSection = document.getElementById('commentSection');
            modalTitle.textContent = student.studentName;
            gradeSection.style.display = 'block';
            commentSection.style.display = 'block';
            solutionInputContainer.style.display = 'none';
            submitButton.textContent = 'Salvesta';
            submitButton.disabled = false;

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
                document.getElementById('studentComment').value = '';
            }
        }

        if (student.solutionUrl) {
            solutionUrlContainer.innerHTML = `
            <?php if ($isStudent): ?>
                <p class="pt-2 mb-0">Juba esitatud lahendus:</p>
            <?php endif?>
            <a href="${student.solutionUrl}" id="solutionUrl" target="_blank" rel="noopener noreferrer">${student.solutionUrl}</a>`;
        } else {
            solutionUrlContainer.innerHTML = 'Link puudub';  // Display plain text if no link
        }

        criteriaContainer.innerHTML = '';

        Object.keys(assignment.criteria).forEach(criteriaId => {
            const criterion = assignment.criteria[criteriaId];
            const isCompleted = assignment.students[studentId]?.userDoneCriteria[criteriaId]?.completed;

            criteriaContainer.innerHTML += `
            <div class="form-check">
                <input class="form-check-input" type="checkbox" id="criterion_${criteriaId}" ${isCompleted ? 'checked' : ''}>
                <label class="form-check-label" for="criterion_${criteriaId}">
                    ${criterion.criteriaName}
                </label>
            </div>
        `;
        });

        const modal = new bootstrap.Modal(document.getElementById('studentModal'));
        modal.show();
    }

    function saveStudentCriteria() {
        const criteria = getCriteriaList('#requiredCriteria input[type="checkbox"]');
        ajax(`assignments/saveStudentCriteria`, {
                assignmentId: assignment.assignmentId,
                studentId: <?= $this->auth->userId ?>,
                criteria: criteria,
                teacherId: assignment.teacherId,
                teacherName: assignment.teacherName
            },
            function (res) {
                if (res.status === 200) {
                    location.reload();
                }
            },
            function (error) {
                alert(error ?? 'Tekkis viga serveriga suhtlemisel.');
            });
    }

    function getCriteriaList(selector = '#studentGradeCriteriaContainer input[type="checkbox"]') {
        const criteria = {};
        document.querySelectorAll(selector).forEach(cb => {
            if (selector.startsWith('#edit')) {
                criteria[parseInt(cb.id.replace('edit_criterion_', ''))] = cb.checked;
            } else if (selector.startsWith('#context-menu') || selector.startsWith('#check')) {
                criteria[parseInt(cb.id.replace('check_criterion_', ''))] = cb.checked;
            } else {
                criteria[parseInt(cb.id.replace('criterion_', ''))] = cb.checked;
            }
        });
        return criteria;
    }

    function scrollToBottom() {
        const messageContainer = document.querySelector('#messageContainer .content-part');
        const notificationContainer = document.querySelector('#notificationContainer .content-part');


        if (messageContainer) {
            messageContainer.scrollTop = messageContainer.scrollHeight;
        }

        if (notificationContainer) {
            notificationContainer.scrollTop = notificationContainer.scrollHeight;
        }
    }


    function editAssignment() {
        const modal = new bootstrap.Modal(document.getElementById('editAssignmentModal'));
        modal.show();
    }

    function removeOldCriterion(param) {
        const editCriteriaContainer = document.getElementById('editCriteriaContainer');
        const criterionElement = document.getElementById(`edit_criterion_${param}`);

        if (criterionElement) {
            const criterionRow = criterionElement.closest('.criteria-row');
            if (criterionRow) {
                editCriteriaContainer.removeChild(criterionRow);
            } else {
                console.error("Criterion row not found");
            }
        } else {
            console.error("Criterion element not found");
        }
    }

    function saveEditedAssignment() {
        const assignmentName = document.getElementById('assignmentName').value;
        const assignmentInstructions = document.getElementById('assignmentInstructions').value;
        const assignmentDueAt = document.getElementById('assignmentDueAt').value;
        const criteria = getCriteriaList('#editCriteriaContainer input[type="checkbox"]');
        ajax(`assignments/editAssignment`, {
                assignmentId: assignment.assignmentId,
                teacherId: assignment.teacherId,
                teacherName: assignment.teacherName,
                assignmentName: assignmentName,
                assignmentInstructions: assignmentInstructions,
                assignmentDueAt: assignmentDueAt,
                oldCriteria: criteria,
                newCriteria: newAddedCriteria ?? [],
            },
            function (res) {
                if (res.status === 200) {
                    location.reload();
                    scrollToBottom();
                }
            },
            function (error) {
                alert(error ?? 'Tekkis viga serveriga suhtlemisel.');
            }
        );
    }

    <?php if (!$isStudent): ?>
    document.getElementById('addCriterionButton').addEventListener('click', function () {
        const modal = new bootstrap.Modal(document.getElementById('addCriterionModal'));
        modal.show();
    });
    <?php endif; ?>

    function addNewCriterion() {
        const criterionName = document.getElementById('newCriterionName').value.trim();

        if (!criterionName) {
            alert('Sisestage kriteeriumi nimi!');
            return;
        }

        ajax('assignments/checkCriterionNameSize', {
            criterionName: criterionName
        }, function (res) {
            if (res.status === 200) {
                const existingCriteria = Array.from(document.querySelectorAll('#editCriteriaContainer .form-check-label'))
                    .map(label => label.textContent.trim());

                if (existingCriteria.includes(criterionName) || newAddedCriteria.includes(criterionName)) {
                    alert('Selline kriteerium on juba olemas!');
                    return;
                }

                const modal = bootstrap.Modal.getInstance(document.getElementById('addCriterionModal'));
                modal.hide();

                newAddedCriteria.push(criterionName);

                const editCriteriaContainer = document.getElementById('editCriteriaContainer');

                const criterionHTML = `
                <div class="criteria-row">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" checked disabled>
                        <label class="form-check-label">${criterionName}</label>
                    </div>
                    <button type="button" class="btn btn-danger btn-sm" onclick="removeNewCriterion('${criterionName}')">X</button>
                </div>
            `;
                document.getElementById('newCriterionName').value = '';
                editCriteriaContainer.insertAdjacentHTML('beforeend', criterionHTML);
            }
        }, function (error) {
            alert(error ?? 'Tekkis viga serveriga suhtlemisel.');
        });
    }

    function removeNewCriterion(criterionName) {
        newAddedCriteria = newAddedCriteria.filter(name => name !== criterionName);

        const criterionRow = Array.from(document.querySelectorAll('.criteria-row')).find(row => row.textContent.includes(criterionName));
        if (criterionRow) {
            criterionRow.remove();
        }
    }

    function replyToMessage(userName, messageId, messageContent, createdAt) {
        document.getElementById('replyInfo').style.display = 'block';
        document.getElementById('replyMessage').innerHTML = `
        <div class="d-flex text-break align-items-start border rounded p-2" style="background-color: #f0f0f0;">
            <div class="me-3">
                <span class="avatar bg-primary text-white rounded-circle p-2">${userName[0]}</span>
            </div>
            <div>
                <strong>${userName}</strong> kirjutas: ${createdAt}<br>
                <em>${messageContent}</em>
            </div>
        </div>
    `;
        const content = document.getElementById('messageContent')
        content.setAttribute('data-reply-id', messageId)
        content.setAttribute('data-reply-user', userName)
        content.setAttribute('data-reply-time', createdAt)
        content.setAttribute('data-reply-content', messageContent)
        content.focus();
    }

    function cancelReply() {
        document.getElementById('replyInfo').style.display = 'none';
        document.getElementById('messageContent').removeAttribute('data-reply-id');
    }

    function submitMessage() {
        const messageContent = document.getElementById('messageContent');
        const answerToId = messageContent.getAttribute('data-reply-id') || null;

        let replyContent = '';
        if (answerToId) {
            const replyUser = messageContent.getAttribute('data-reply-user');
            const replyTime = messageContent.getAttribute('data-reply-time');
            const replyText = messageContent.getAttribute('data-reply-content');

            replyContent = `> **${replyUser}** kirjutas *${replyTime}*:\n> ${replyText}\n\n`;
        }

        const finalContent = replyContent + messageContent.value;

        ajax('assignments/saveMessage', {
            assignmentId: assignment.assignmentId,
            userId: <?= $this->auth->userId ?>,
            content: finalContent,
            answerToId: answerToId,
            teacherId: assignment.teacherId,
            teacherName: assignment.teacherName
        }, function (res) {
            if (res.status === 200) {
                location.reload();
                scrollToBottom();
            }
        }, function (error) {
            alert(error ?? 'Tekkis viga serveriga suhtlemisel.');
        });
    }

    function setGrade(grade) {

        ajax(`assignments/saveAssignmentGrade`, {
                assignmentId: assignment.assignmentId,
                studentId: currentStudentId,
                grade: grade,
                teacherName: assignment.teacherName,
                studentName: assignment.students[currentStudentId].studentName,
                teacherId: assignment.teacherId
            },
            function (res) {
                if (res.status === 200) {
                    location.reload();
                }
            },
            function (error) {
                alert(error ?? 'Tekkis viga serveriga suhtlemisel.');
            }
        );
    }
</script>
