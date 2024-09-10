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
        width: 200px;
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

</style>
<div>
    <div class="mb-3">
        <h2 class="mb-4"><?= $assignment['assignmentName'] ?></h2>
        <p class="mb-0"><?= $assignment['assignmentInstructions'] ?></p>
        <p class="mt-4 fw-bold">Tähtaeg: <?= $assignment['assignmentDueAt'] ?></p>

        <?php if ($isStudent): ?>
            <div class="d-flex justify-content-end mt-3">
                <button class="btn btn-primary" onclick="editAssignment()">Esita</button>
            </div>
        <?php else: ?>
            <div class="d-flex justify-content-end mt-3">
                <button class="btn btn-secondary" onclick="editAssignment()">Muuda</button>
            </div>
        <?php endif; ?>
    </div>


    <div class="p-3 mb-5 mt-5 border rounded bg-light" style="width: 30%;">
        <h5 class="mb-3">Kriteeriumid</h5>
        <?php foreach ($assignment['criteria'] as $criterion): ?>
            <div class="form-check">
                <input class="form-check-input" id="criterion_<?= $criterion['criteriaId'] ?>" type="checkbox"
                       name="criteria[]"
                       value="<?= $criterion['criteriaId'] ?>">
                <label class="form-check-label" for="criterion_<?= $criterion['criteriaId'] ?>">
                    <?= $criterion['criteriaName'] ?>
                </label>
            </div>
        <?php endforeach; ?>
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
                        oncontextmenu="showContextMenu(event, <?= $s['studentId'] ?>)">
                        <?= $s['grade'] ?? '' ?> <span style="font-size: 8px"><?= $s['userDoneCriteriaCount'] ?>/<?= count($assignment['criteria']) ?></span>
                    </td>
                <?php endforeach; ?>
            </tr>
            </tbody>
        </table>
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
            <?php foreach ($assignment['criteria'] as $criterion): ?>
                <div class="form-check">
                    <input class="form-check-input" id="criterion_<?= $criterion['criteriaId'] ?>" type="checkbox"
                           name="criteria[]"
                           value="<?= $criterion['criteriaId'] ?>">
                    <label class="form-check-label" for="criterion_<?= $criterion['criteriaId'] ?>">
                        <?= $criterion['criteriaName'] ?>
                    </label>
                </div>
            <?php endforeach; ?>
        </div>
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

    let currentStudentId = null;

    function showContextMenu(event, studentId) {
        event.preventDefault();
        currentStudentId = studentId;

        const menu = document.getElementById('context-menu');
        menu.style.display = 'block';
        menu.style.left = `${event.pageX}px`;
        menu.style.top = `${event.pageY}px`;

        document.addEventListener('click', hideContextMenu);
    }

    function hideContextMenu() {
        const menu = document.getElementById('context-menu');
        menu.style.display = 'none';
        document.removeEventListener('click', hideContextMenu);
    }

    function setGrade(grade) {
        alert('Set grade ' + grade + ' for student ID ' + currentStudentId);

        // Save grade to the database for the student

        hideContextMenu();
    }

    function replyToMessage(param) {

    }


    document.addEventListener('DOMContentLoaded', function () {
        [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
            .map(el => new bootstrap.Tooltip(el));
    });

    function editAssignment() {

    }

    function submitMessage() {

    }
</script>
