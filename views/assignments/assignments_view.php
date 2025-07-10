<!-- Bootstrap Icons -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">

<style>
    .red-cell {
        background-color: rgb(255, 180, 176) !important;
    }

    .yellow-cell {
        background-color: #fff8b3 !important;
    }

    .modal-body {
        word-wrap: break-word;
        word-break: break-word;
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

    .form-check-label {
        word-wrap: break-word;
        word-break: break-all;
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

    /* Image preview styles for comments */
    .comment-image {
        transition: transform 0.2s ease-in-out, box-shadow 0.2s ease-in-out;
    }

    .comment-image:hover {
        transform: scale(1.02);
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
    }

    .image-preview-container {
        text-align: left;
    }

    .image-modal-content {
        max-width: 90vw;
        max-height: 90vh;
        object-fit: contain;
    }

    /* Modal backdrop for image viewing */
    .image-modal-backdrop {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.8);
        z-index: 9999;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
    }

    .image-modal-backdrop img {
        max-width: 90%;
        max-height: 90%;
        object-fit: contain;
        border-radius: 8px;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.5);
    }

    .image-modal-close {
        position: absolute;
        top: 20px;
        right: 30px;
        color: white;
        font-size: 30px;
        font-weight: bold;
        cursor: pointer;
        z-index: 10000;
    }

    .image-modal-close:hover {
        color: #ccc;
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

    .header-item,
    .body-item {
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
        word-wrap: break-word;
        /* Ensure long words break and wrap to the next line */
    }


    /* Grades column */
    #assignments-container {
        flex: 1;
        max-width: 100%;
        overflow-x: auto;
        /* Set a max-width for the grades section */
        border-radius: 8px;
    }

    #messages-container {
        flex: 2;
        /* Take the remaining space */
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
            max-width: 100%;
            /* Ensures it doesn't take the full width */
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

    /* Message content image styles */
    .content-part img {
        display: block;
        margin: 10px 0;
        max-width: 100%;
        height: auto;
        border-radius: 8px;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        clear: both;
    }

    /* Ensure text before images has proper spacing */
    .content-part p {
        line-height: 1.5;
        word-wrap: break-word;
    }

    /* Add spacing around images in message content */
    .content-part p img {
        margin: 15px 0;
    }

    /* Quoted message styling */
    .content-part blockquote {
        background-color: #f8f9fa;
        border-left: 4px solid #007bff;
        margin: 10px 0;
        padding: 10px 15px;
        border-radius: 4px;
        font-style: italic;
        color: #6c757d;
    }

    .content-part blockquote p {
        margin-bottom: 5px;
        line-height: 1.4;
    }

    .content-part blockquote p:last-child {
        margin-bottom: 0;
    }

    /* Style for quoted content within blockquotes */
    .content-part blockquote strong {
        color: #495057;
        font-weight: 600;
    }

    .content-part blockquote em {
        color: #6c757d;
        font-size: 0.9em;
    }
</style>
<div>
    <div class="mb-3">
        <h2 class="mb-2"><?= $assignment['assignmentName'] ?></h2>
        <?php if (!empty($assignment['primaryGroupName'])): ?>
            <p class="text-muted mb-3">
                <i class="bi bi-people-fill"></i> Grupp: <strong><?= $assignment['primaryGroupName'] ?></strong>
            </p>
        <?php endif; ?>
        <?php
        $parsedown = new Parsedown();
        $assignmentInstructions = $assignment['assignmentInstructions'];

        // Check if the text contains Markdown syntax
        if (strpos($assignmentInstructions, '#') !== false || strpos($assignmentInstructions, '*') !== false || strpos($assignmentInstructions, '-') !== false) {
            $assignmentInstructionsHtml = $parsedown->text($assignmentInstructions);
        } else {
            $assignmentInstructionsHtml = nl2br(htmlspecialchars($assignmentInstructions));
        }
        ?>
        <p class="mb-0"><?= $assignmentInstructionsHtml ?></p>
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


    <div id="criterionDisplay" class="adaptive-background p-3 mb-5 mt-5">
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
                            <?= htmlspecialchars($criterion['criteriaName'], ENT_QUOTES, 'UTF-8') ?>
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

                            <div class="mb-3 form-check">
                                <input type="checkbox" class="form-check-input" id="assignmentInvolvesOpenApi" name="assignmentInvolvesOpenApi"
                                    <?= isset($assignment['assignmentInvolvesOpenApi']) && $assignment['assignmentInvolvesOpenApi'] ? 'checked' : '' ?>>
                                <label class="form-check-label" for="assignmentInvolvesOpenApi">Ülesandel on OpenAPI</label>
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
                                                    <?= htmlspecialchars($criterion['criteriaName'], ENT_QUOTES, 'UTF-8') ?>
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
                    <?php include 'views/modules/openapi_module.php'; ?>
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
                                        <div class="comment-text text-muted" data-raw-comment="<?= htmlspecialchars($comment['comment'], ENT_QUOTES, 'UTF-8') ?>">
                                            <!-- Comment content will be processed by JavaScript -->
                                        </div>
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
                                        <p class="fw-bold mb-1 message-text" data-raw-message="<?= htmlspecialchars($message['content'], ENT_QUOTES, 'UTF-8') ?>">
                                            <!-- Message content will be processed by JavaScript -->
                                        </p>
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
                                        <p class="mb-1 message-text" data-raw-message="<?= htmlspecialchars($message['content'], ENT_QUOTES, 'UTF-8') ?>">
                                            <!-- Message content will be processed by JavaScript -->
                                        </p>
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
                            <?php else: ?>
                                <div class="d-flex align-items-start mb-3 border rounded p-3 bg-light">
                                    <div class="flex-shrink-0 me-3">
                                        <span class="avatar bg-primary text-white rounded-circle p-2"><?= strtoupper(substr($message['userName'], 0, 1)) ?></span>
                                    </div>
                                    <div class="flex-grow-1">
                                        <div class="d-flex flex-wrap justify-content-between">
                                            <h6 class="fw-bold mb-1 text-muted">
                                                <i class="fas fa-info-circle me-1"></i><?= $message['userName'] ?>
                                            </h6>
                                            <small class="text-muted"><?= $message['createdAt'] ?></small>
                                        </div>
                                        <p class="mb-1 text-muted message-text" data-raw-message="<?= htmlspecialchars($message['content'], ENT_QUOTES, 'UTF-8') ?>">
                                            <!-- Message content will be processed by JavaScript -->
                                        </p>
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
        const userIsAdmin = <?= $this->auth->userIsAdmin ? 'true' : 'false' ?>;

        document.addEventListener('DOMContentLoaded', function() {
            initializeTooltips();
            scrollToBottom();
            processComments(); // Add comment processing

            // Initialize OpenAPI button visibility
            const openApiButton = document.getElementById('openApiButton');
            if (openApiButton) {
                openApiButton.style.display = assignment.assignmentInvolvesOpenApi ? 'inline-block' : 'none';
            }
        });

        function initializeTooltips() {
            const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            const tooltipList = tooltipTriggerList.map(function(tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });
        }

        // If no criteria exists, display warning (add class 'warning-bg' to element of id 'criterionDisplay')

        if (Array.isArray(assignment['criteria']) && assignment['criteria'].length === 0 || assignment['criteria'] === null) {
            document.getElementById('criterionDisplay').classList.add('bg-warning');
            document.getElementById('requiredCriteria').innerHTML = '<p class="text-center">Kriteeriumid puuduvad</p>';
        }

        document.getElementById('requiredCriteria').addEventListener('change', function(event) {
            if (event.target && event.target.type === 'checkbox') {
                document.querySelector('#studentCriteriaForm .btn-primary').hidden = false;
            }
        });

        document.getElementById('submitButton').addEventListener('click', function() {
            const gradeRadioGroup = document.querySelectorAll('#gradeRadioGroup input[type="radio"]');
            let gradeSelected = false;

            gradeRadioGroup.forEach(radio => {
                if (radio.checked) {
                    gradeSelected = true;
                }
            });

            <?php if (!$isStudent): ?>
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
                        studentId: <?= $this->auth->userId ?>,
                        studentName: assignment.students[<?= $this->auth->userId ?>].studentName,
                        solutionUrl: solutionUrl,
                        criteria: criteria,
                        teacherId: assignment.teacherId,
                        teacherName: assignment.teacherName,
                        comment: comment
                    },
                    function(res) {
                        if (res.status === 200) {
                            location.reload();
                        }
                    },
                    function(error) {
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
                    function(res) {
                        if (res.status === 200) {
                            location.reload();
                        }
                    },
                    function(error) {
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

                document.getElementById('checkboxesContainer').addEventListener('change', function(event) {
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
                        }, function(res) {
                            if (res.status === 200) {
                                solutionInputFeedback.textContent = 'Link on valideeritud ja kättesaadav.';
                                solutionInputFeedback.style.color = 'green';
                                isValidUrl = true;
                                updateButtonState();
                            }
                        }, function(error) {
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

                    submitButton.disabled = !(allChecked && isValidUrl);
                }

            } else {
                const gradeSection = document.getElementById('gradeSection');
                const commentSection = document.getElementById('commentSection');
                const openApiButton = document.getElementById('openApiButton');

                modalTitle.textContent = student.studentName;
                gradeSection.style.display = 'block';
                commentSection.style.display = 'block';
                solutionInputContainer.style.display = 'none';
                submitButton.textContent = 'Salvesta';
                submitButton.disabled = false;

                // Show/hide the OpenAPI button based on the assignment's assignmentInvolvesOpenApi value
                if (openApiButton) {
                    openApiButton.style.display = assignment.assignmentInvolvesOpenApi ? 'inline-block' : 'none';
                }

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
            <?php endif ?>
            <a href="${student.solutionUrl}" id="solutionUrl" target="_blank" rel="noopener noreferrer">${student.solutionUrl}</a>`;
            } else {
                solutionUrlContainer.innerHTML = 'Link puudub'; // Display plain text if no link
            }

            criteriaContainer.innerHTML = '';

            Object.keys(assignment.criteria).forEach((criteriaId, index) => {
                const criterion = assignment.criteria[criteriaId];
                const isCompleted = assignment.students[studentId]?.userDoneCriteria[criteriaId]?.completed;

                criteriaContainer.innerHTML += `
    <div class="form-check">
        <input class="form-check-input" type="checkbox" id="criterion_${criteriaId}" ${isCompleted ? 'checked' : ''}>
        <label class="form-check-label" for="criterion_${criteriaId}">
            ${index + 1}. ${criterion.criteriaName}
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
                function(res) {
                    if (res.status === 200) {
                        location.reload();
                    }
                },
                function(error) {
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
            const assignmentInvolvesOpenApi = document.getElementById('assignmentInvolvesOpenApi').checked ? 1 : 0;
            const criteria = getCriteriaList('#editCriteriaContainer input[type="checkbox"]');
            ajax(`assignments/editAssignment`, {
                    assignmentId: assignment.assignmentId,
                    teacherId: assignment.teacherId,
                    teacherName: assignment.teacherName,
                    assignmentName: assignmentName,
                    assignmentInstructions: assignmentInstructions,
                    assignmentDueAt: assignmentDueAt,
                    assignmentInvolvesOpenApi: assignmentInvolvesOpenApi,
                    oldCriteria: criteria,
                    newCriteria: newAddedCriteria ?? [],
                },
                function(res) {
                    if (res.status === 200) {
                        location.reload();
                        scrollToBottom();
                    }
                },
                function(error) {
                    alert(error ?? 'Tekkis viga serveriga suhtlemisel.');
                }
            );
        }

        <?php if (!$isStudent): ?>
            document.getElementById('addCriterionButton').addEventListener('click', function() {
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
            }, function(res) {
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
            }, function(error) {
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
            }, function(res) {
                if (res.status === 200) {
                    location.reload();
                    scrollToBottom();
                }
            }, function(error) {
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
                function(res) {
                    if (res.status === 200) {
                        location.reload();
                    }
                });
        }

        // OpenAPI functionality
        function openSwaggerModal() {
            const solutionUrl = document.getElementById('solutionUrl').getAttribute('href');
            const swaggerUrlInput = document.getElementById('swaggerUrlInput');
            const promptTextarea = document.getElementById('promptTextarea');

            // Set the default URL by appending /swagger-ui-init.js to the solution URL
            if (solutionUrl && solutionUrl !== '#') {
                let swaggerUrl = solutionUrl;

                // Handle specific SwaggerUI links like https://docs.foo.me/en/#/forms/createForm
                // Extract the base URL (everything before the #)
                if (swaggerUrl.includes('#/')) {
                    swaggerUrl = swaggerUrl.split('#/')[0];
                }

                // Make sure the URL ends with a slash before appending swagger-ui-init.js
                if (!swaggerUrl.endsWith('/')) {
                    swaggerUrl += '/';
                }
                swaggerUrl += 'swagger-ui-init.js';
                swaggerUrlInput.value = swaggerUrl;
            } else {
                swaggerUrlInput.value = '';
            }

            // Clear previous output
            document.getElementById('swaggerDocOutput').value = '';

            // Load the prompt from settings
            loadPromptFromSettings();

            // Show the modal
            const modalElement = document.getElementById('swaggerModal');
            const modal = new bootstrap.Modal(modalElement);

            // Initialize tooltips when the modal is fully shown
            modalElement.addEventListener('shown.bs.modal', function() {
                // Initialize all tooltips within the modal
                const tooltipTriggerList = [].slice.call(modalElement.querySelectorAll('[data-bs-toggle="tooltip"]'));
                tooltipTriggerList.map(function(tooltipTriggerEl) {
                    return new bootstrap.Tooltip(tooltipTriggerEl);
                });
            });

            modal.show();
        }

        function loadPromptFromSettings() {
            const promptTextarea = document.getElementById('promptTextarea');
            const promptDisplay = document.getElementById('promptDisplay');

            // Use AJAX to fetch the prompt from settings
            ajax('assignments/getOpenApiPrompt', {}, function(response) {
                const promptText = (response.status === 200 && response.data && response.data.prompt !== undefined) ?
                    response.data.prompt :
                    '';

                // Set the prompt text in the appropriate element
                if (userIsAdmin) {
                    // For admins: set the value of the textarea
                    promptTextarea.value = promptText;

                    // Add event listener to save the prompt when it changes
                    promptTextarea.addEventListener('input', function() {
                        // Debounce the save operation
                        if (promptTextarea.saveTimeout) {
                            clearTimeout(promptTextarea.saveTimeout);
                        }
                        promptTextarea.saveTimeout = setTimeout(function() {
                            savePromptToSettings(promptTextarea.value);
                        }, 1000); // Save after 1 second of inactivity
                    });
                } else {
                    // For non-admins: set the text content of the pre element and the hidden textarea
                    if (promptDisplay) {
                        promptDisplay.textContent = promptText;
                    }
                    promptTextarea.value = promptText; // Still set the hidden textarea for copying
                }
            }, function(error) {
                console.error('Failed to load prompt from settings:', error);
                if (userIsAdmin) {
                    promptTextarea.value = '';
                } else if (promptDisplay) {
                    promptDisplay.textContent = '';
                    promptTextarea.value = ''; // Also clear the hidden textarea
                }
            });
        }

        function savePromptToSettings(promptText) {
            // Only admins can save the prompt
            if (!userIsAdmin) return;

            ajax('assignments/saveOpenApiPrompt', {
                prompt: promptText
            }, function(response) {
                if (response.status === 200) {
                    console.log('Prompt saved successfully');
                } else {
                    console.error('Failed to save prompt:', response.error);
                }
            }, function(error) {
                console.error('Failed to save prompt:', error);
            });
        }

        function fetchSwaggerDoc() {
            const swaggerUrl = document.getElementById('swaggerUrlInput').value.trim();
            const outputTextarea = document.getElementById('swaggerDocOutput');
            const fetchButton = document.getElementById('fetchSwaggerButton');
            const copyButton = document.getElementById('copyButton');
            const loadingSpinner = document.getElementById('swaggerLoadingSpinner');

            if (!swaggerUrl) {
                showError(outputTextarea, 'Palun sisesta kehtiv URL');
                return;
            }

            // Disable the buttons while fetching
            fetchButton.disabled = true;
            copyButton.disabled = true;
            fetchButton.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Loading...';
            outputTextarea.value = '';

            // Show the loading spinner
            loadingSpinner.classList.remove('d-none');

            // Use AJAX to fetch the swagger-ui-init.js file through a PHP proxy
            ajax('assignments/fetchSwaggerDoc', {
                url: swaggerUrl
            }, function(response) {
                // Reset UI elements
                fetchButton.disabled = false;
                fetchButton.innerHTML = 'Hangi OpenAPI spekk';
                loadingSpinner.classList.add('d-none');

                if (response.status === 200 && response.data && response.data.swaggerDoc) {
                    // Format the JSON for better readability
                    try {
                        // Get the base URL from the swagger URL
                        const swaggerDocObj = response.data.swaggerDoc;
                        const swaggerUrl = document.getElementById('swaggerUrlInput').value.trim();
                        const baseUrl = getBaseUrlFromSwaggerUrl(swaggerUrl);

                        // Check if we need to modify the servers array
                        if (baseUrl) {
                            // If servers array doesn't exist or first server is "/", add/replace with the base URL
                            if (!swaggerDocObj.servers ||
                                !swaggerDocObj.servers.length ||
                                (swaggerDocObj.servers.length > 0 && swaggerDocObj.servers[0].url === '/')) {

                                // Create servers array if it doesn't exist
                                if (!swaggerDocObj.servers) {
                                    swaggerDocObj.servers = [];
                                }

                                // Add or replace the first server with the base URL
                                if (swaggerDocObj.servers.length === 0) {
                                    swaggerDocObj.servers.push({
                                        url: baseUrl,
                                        description: 'API Server'
                                    });
                                } else {
                                    swaggerDocObj.servers[0] = {
                                        url: baseUrl,
                                        description: 'API Server'
                                    };
                                }
                            }
                        }

                        const formattedJson = JSON.stringify(swaggerDocObj, null, 2);
                        outputTextarea.value = formattedJson;
                        copyButton.disabled = false; // Enable the copy button only when we have content
                    } catch (e) {
                        showError(outputTextarea, 'Viga JSON-i vormindamisel: ' + e.message);
                        copyButton.disabled = true;
                    }
                } else {
                    let errorMessage = 'OpenAPI spetsifikatsiooni hankimine või parsimine ebaõnnestus';
                    if (response.error) {
                        errorMessage = response.error;
                    }
                    showError(outputTextarea, errorMessage);
                    copyButton.disabled = true;
                }
            }, function(error) {
                // Reset UI elements
                fetchButton.disabled = false;
                fetchButton.innerHTML = 'Hangi OpenAPI spekk';
                loadingSpinner.classList.add('d-none');

                let errorMessage = 'OpenAPI spetsifikatsiooni hankimisel tekkis viga';
                if (error) {
                    if (error.includes('404')) {
                        errorMessage = 'OpenAPI spetsifikatsiooni faili ei leitud (404). Palun kontrolli URL-i.';
                    } else if (error.includes('403')) {
                        errorMessage = 'Juurdepääs OpenAPI spetsifikatsioonile on keelatud (403). Sul ei pruugi olla õigusi sellele ressursile jõuda.';
                    } else if (error.includes('500')) {
                        errorMessage = 'Serveril tekkis viga (500) OpenAPI spetsifikatsiooni hankimisel.';
                    } else if (error.includes('timeout')) {
                        errorMessage = 'Päring aegus. Server võib olla aeglane või kättesaamatu.';
                    } else {
                        errorMessage = error;
                    }
                }

                showError(outputTextarea, errorMessage);
                copyButton.disabled = true;
            });
        }

        // Helper function to show formatted error messages
        function showError(textarea, message) {
            textarea.value = '⚠️ VIGA: ' + message;
            textarea.style.color = 'red';
            setTimeout(() => {
                textarea.style.color = ''; // Reset color after a delay
            }, 5000);
        }

        // Helper function to extract the base URL from the swagger-ui-init.js URL
        function getBaseUrlFromSwaggerUrl(swaggerUrl) {
            if (!swaggerUrl) return null;

            try {
                // Create a URL object from the swagger URL
                const urlObj = new URL(swaggerUrl);

                // Just return the origin (protocol + hostname) without any path
                // This ensures we get the root of the server (e.g., https://docs.eerovallistu.site)
                return urlObj.origin;
            } catch (e) {
                console.error('Viga URL-i parsimisel:', e);
                return null;
            }
        }

        function copyPromptAndSpec() {
            const promptTextarea = document.getElementById('promptTextarea');
            const swaggerTextarea = document.getElementById('swaggerDocOutput');
            const copyButton = document.getElementById('copyButton');
            const originalButtonText = copyButton.innerHTML;

            // Only proceed if there's content to copy
            if (!swaggerTextarea.value.trim()) {
                alert('OpenAPI spetsifikatsioon puudub. Palun hangi spetsifikatsioon enne kopeerimist.');
                return;
            }

            // Get the prompt text from the textarea (which exists for both admins and non-admins)
            // For non-admins, this is a hidden textarea that still contains the prompt text
            const promptText = promptTextarea.value;

            // Combine the content from both textareas
            const combinedText = promptText + '\n\n' + swaggerTextarea.value;

            // Use the modern Clipboard API if available
            if (navigator.clipboard && navigator.clipboard.writeText) {
                navigator.clipboard.writeText(combinedText)
                    .then(() => {
                        // Visual feedback that copy was successful
                        copyButton.innerHTML = '<i class="bi bi-check"></i> Kopeeritud!';
                        setTimeout(() => {
                            copyButton.innerHTML = originalButtonText;
                        }, 1500);
                    })
                    .catch(err => {
                        console.error('Teksti kopeerimine ebaõnnestus: ', err);
                        // Fallback to the older method
                        fallbackCopyTextToClipboard(combinedText);
                    });
            } else {
                // Fallback for browsers that don't support the Clipboard API
                fallbackCopyTextToClipboard(combinedText);
            }

            // Fallback copy method using execCommand
            function fallbackCopyTextToClipboard(text) {
                // Create a temporary textarea
                const tempTextarea = document.createElement('textarea');
                tempTextarea.style.position = 'fixed';
                tempTextarea.style.left = '-9999px';
                tempTextarea.style.top = '0';
                tempTextarea.value = text;
                document.body.appendChild(tempTextarea);

                // Select and copy the text
                tempTextarea.focus();
                tempTextarea.select();

                try {
                    const successful = document.execCommand('copy');
                    if (successful) {
                        // Visual feedback that copy was successful
                        copyButton.innerHTML = '<i class="bi bi-check"></i> Kopeeritud!';
                        setTimeout(() => {
                            copyButton.innerHTML = originalButtonText;
                        }, 1500);
                    } else {
                        alert('Teksti kopeerimine ebaõnnestus');
                    }
                } catch (err) {
                    console.error('Teksti kopeerimine ebaõnnestus: ', err);
                    alert('Teksti kopeerimine ebaõnnestus: ' + err);
                } finally {
                    // Remove the temporary textarea
                    document.body.removeChild(tempTextarea);
                }
            }
        }

        // Image modal functionality for comment images
        function showImageModal(modalId, imageUrl, altText) {
            // Remove any existing modal
            const existingModal = document.querySelector('.image-modal-backdrop');
            if (existingModal) {
                existingModal.remove();
            }

            // Create modal backdrop
            const modalBackdrop = document.createElement('div');
            modalBackdrop.className = 'image-modal-backdrop';
            modalBackdrop.onclick = function() {
                closeImageModal();
            };

            // Create close button
            const closeButton = document.createElement('span');
            closeButton.className = 'image-modal-close';
            closeButton.innerHTML = '&times;';
            closeButton.onclick = function(e) {
                e.stopPropagation();
                closeImageModal();
            };

            // Create image element
            const modalImage = document.createElement('img');
            modalImage.src = imageUrl;
            modalImage.alt = altText || 'Suurendatud pilt';
            modalImage.className = 'image-modal-content';
            modalImage.onclick = function(e) {
                e.stopPropagation(); // Prevent closing when clicking on image
            };

            // Handle image load error
            modalImage.onerror = function() {
                const errorDiv = document.createElement('div');
                errorDiv.style.color = 'white';
                errorDiv.style.textAlign = 'center';
                errorDiv.style.fontSize = '18px';
                errorDiv.innerHTML = '<i class="bi bi-exclamation-triangle"></i><br>Pilti ei õnnestunud laadida';

                modalBackdrop.innerHTML = '';
                modalBackdrop.appendChild(closeButton);
                modalBackdrop.appendChild(errorDiv);
            };

            // Append elements
            modalBackdrop.appendChild(closeButton);
            modalBackdrop.appendChild(modalImage);

            // Add to document
            document.body.appendChild(modalBackdrop);

            // Add keyboard event listener for ESC key
            document.addEventListener('keydown', handleImageModalKeydown);
        }

        function closeImageModal() {
            const modal = document.querySelector('.image-modal-backdrop');
            if (modal) {
                modal.remove();
            }
            // Remove keyboard event listener
            document.removeEventListener('keydown', handleImageModalKeydown);
        }

        function handleImageModalKeydown(e) {
            if (e.key === 'Escape') {
                closeImageModal();
            }
        }

        // Markdown parser function (handles both markdown and existing HTML)
        function parseMarkdown(text) {
            if (!text) return '';

            let html = text;

            // Check if the text already contains HTML tags (like <p>, <img>, etc.)
            if (html.includes('<img') || html.includes('<p>') || html.includes('</p>') || html.includes('<br')) {

                // If it contains img tags, enhance them for our modal functionality
                html = html.replace(/<img\s+([^>]*?)src=["']([^"']+)["']([^>]*?)alt=["']([^"']*?)["']([^>]*?)\/?>/gi, function(match, beforeSrc, src, betweenSrcAlt, alt, afterAlt) {

                    const modalId = 'imageModal_' + Math.random().toString(36).substr(2, 9);
                    return '<div class="image-preview-container mt-2 mb-2">' +
                        '<img src="' + src + '" alt="' + alt + '" ' +
                        'class="comment-image img-fluid rounded shadow-sm" ' +
                        'style="max-height: 200px; cursor: pointer; border: 1px solid #dee2e6; opacity: 0; transition: opacity 0.3s ease;" ' +
                        'onclick="showImageModal(\'' + modalId + '\', \'' + src + '\', \'' + alt + '\')" ' +
                        'onload="this.style.opacity=1" ' +
                        'onerror="this.style.display=\'none\'; this.nextElementSibling.style.display=\'block\'">' +
                        '<div class="image-error text-muted small" style="display: none; padding: 10px; border: 1px dashed #ccc; border-radius: 5px;">' +
                        '<i class="bi bi-image"></i> Pilti ei õnnestunud laadida' +
                        '</div>' +
                        '</div>';
                });

                // Also handle the alternative pattern where alt comes before src
                html = html.replace(/<img\s+([^>]*?)alt=["']([^"']*?)["']([^>]*?)src=["']([^"']+)["']([^>]*?)\/?>/gi, function(match, beforeAlt, alt, betweenAltSrc, src, afterSrc) {

                    const modalId = 'imageModal_' + Math.random().toString(36).substr(2, 9);
                    return '<div class="image-preview-container mt-2 mb-2">' +
                        '<img src="' + src + '" alt="' + alt + '" ' +
                        'class="comment-image img-fluid rounded shadow-sm" ' +
                        'style="max-height: 200px; cursor: pointer; border: 1px solid #dee2e6; opacity: 0; transition: opacity 0.3s ease;" ' +
                        'onclick="showImageModal(\'' + modalId + '\', \'' + src + '\', \'' + alt + '\')" ' +
                        'onload="this.style.opacity=1" ' +
                        'onerror="this.style.display=\'none\'; this.nextElementSibling.style.display=\'block\'">' +
                        '<div class="image-error text-muted small" style="display: none; padding: 10px; border: 1px dashed #ccc; border-radius: 5px;">' +
                        '<i class="bi bi-image"></i> Pilti ei õnnestunud laadida' +
                        '</div>' +
                        '</div>';
                });

                return html;
            }

            // Escape HTML first
            html = html.replace(/&/g, '&amp;');
            html = html.replace(/</g, '&lt;');
            html = html.replace(/>/g, '&gt;');

            // Convert line breaks
            html = html.replace(/\n/g, '<br>');

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
                const modalId = 'imageModal_' + Math.random().toString(36).substr(2, 9);
                return '<div class="image-preview-container mt-2 mb-2">' +
                    '<img src="' + src + '" alt="' + alt + '" ' +
                    'class="comment-image img-fluid rounded shadow-sm" ' +
                    'style="max-height: 200px; cursor: pointer; border: 1px solid #dee2e6; opacity: 0; transition: opacity 0.3s ease;" ' +
                    'onclick="showImageModal(\'' + modalId + '\', \'' + src + '\', \'' + alt + '\')" ' +
                    'onload="this.style.opacity=1" ' +
                    'onerror="this.style.display=\'none\'; this.nextElementSibling.style.display=\'block\'">' +
                    '<div class="image-error text-muted small" style="display: none; padding: 10px; border: 1px dashed #ccc; border-radius: 5px;">' +
                    '<i class="bi bi-image"></i> Pilti ei õnnestunud laadida' +
                    '</div>' +
                    '</div>';
            });

            // Links
            html = html.replace(/\[([^\]]+)\]\(([^)]+)\)/g, '<a href="$2" target="_blank">$1</a>');

            // Unordered lists
            html = html.replace(/^\* (.+)$/gm, '<li>$1</li>');
            html = html.replace(/(<li>.*<\/li>)/s, '<ul>$1</ul>');

            // Ordered lists
            html = html.replace(/^\d+\. (.+)$/gm, '<li>$1</li>');
            html = html.replace(/(<li>.*<\/li>)/s, function(match) {
                if (match.includes('<ul>')) return match;
                return '<ol>' + match + '</ol>';
            });

            // Blockquotes
            html = html.replace(/^> (.+)$/gm, '<blockquote>$1</blockquote>');

            // Horizontal rules
            html = html.replace(/^---$/gm, '<hr>');

            return html;
        }

        // Process all comments and messages on page load
        function processComments() {
            // Process comments
            const commentElements = document.querySelectorAll('.comment-text[data-raw-comment]');
            
            commentElements.forEach(function(element, index) {
                const rawComment = element.getAttribute('data-raw-comment');

                if (rawComment) {
                    const processedHtml = parseMarkdown(rawComment);
                    element.innerHTML = processedHtml;
                } else {
                    console.log('No raw comment data found for element', index + 1);
                }
            });
            
            // Process messages
            const messageElements = document.querySelectorAll('.message-text[data-raw-message]');
            
            messageElements.forEach(function(element, index) {
                const rawMessage = element.getAttribute('data-raw-message');

                if (rawMessage) {
                    const processedHtml = parseMarkdown(rawMessage);
                    element.innerHTML = processedHtml;
                } else {
                    console.log('No raw message data found for element', index + 1);
                }
            });
        }
    </script>