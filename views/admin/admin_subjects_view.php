<style>
    #subject-table th {
        background-color: #f2f2f2;
    }

    #subject-table tr {
        cursor: pointer;
    }


    #assignments-table .delete-btn {
        color: red !important;
        cursor: pointer !important;
        font-weight: bold !important;
        font-size: 1.5em !important;
    }

    #assignments-table .delete-btn:hover {
        color: darkred !important;
    }

</style>

<h1>Ülesanded</h1>
<div class="col text-end mb-3">
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addAssignmentModal">Lisa uus Ülesanne</button>
</div>
<div class="row">
    <div class="table-responsive">
        <table id="assignments-table" class="table table-bordered">
            <thead>
            <tr>
                <th>Nimi</th>
                <th>Juhised</th>
                <th>Algne kood</th>
                <th>Valideerimisfunktsioon</th>
                <th>Tähtaeg</th>
                <th></th>
            </tr>
            <tr></tr>
            </thead>
            <tbody>
            <?php foreach ($assignments as $a): ?>
                <tr>
                    <td onclick="window.location.href='assignments/<?= $a['assignmentId'] ?>'" ><b><?= $a['assignmentName'] ?></b></td>
                    <td onclick="window.location.href='assignments/<?= $a['assignmentId'] ?>'"><?= $a['assignmentInstructions'] ?></td>
                    <td onclick="window.location.href='assignments/<?= $a['assignmentId'] ?>'"><?= $a['assignmentInitialCode'] ?></td>
                    <td onclick="window.location.href='assignments/<?= $a['assignmentId'] ?>'"><?= $a['assignmentValidationFunction'] ?></td>
                    <td onclick="window.location.href='assignments/<?= $a['assignmentId'] ?>'"><?= (new DateTime($a['assignmentDueAt']))->format('d.m.Y') ?></td>
                    <td class="text-center" style="">
                        <span class="delete-btn" onclick="deleteAssignment(<?= $a['assignmentId'] ?>)">&#x2717;</span>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Add Assignment Modal -->
<div class="modal fade" id="addAssignmentModal" tabindex="-1" aria-labelledby="addAssignmentModalLabel"
     aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addAssignmentModalLabel">Lisa uus Ülesanne</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label for="assignmentName">Ülesande nimi<span class="text-danger" data-bs-toggle="tooltip" title="See väli on kohustuslik">*</span></label>
                    <input type="text" class="form-control" id="assignmentName" placeholder="Sisestage ülesande nimi">
                </div>
                <div class="form-group mt-2">
                    <label for="assignmentInstructions">Juhised<span class="text-danger" data-bs-toggle="tooltip" title="See väli on kohustuslik">*</span></label>
                    <textarea class="form-control" id="assignmentInstructions" placeholder="Sisestage juhised"></textarea>
                </div>
                <div class="form-group mt-2">
                    <label for="assignmentInitialCode">Algne kood</label>
                    <textarea class="form-control" id="assignmentInitialCode" placeholder="Sisestage algne kood (valikuline)"></textarea>
                </div>
                <div class="form-group mt-2">
                    <label for="assignmentValidationFunction">Valideerimisfunktsioon</label>
                    <input type="text" class="form-control" id="assignmentValidationFunction" placeholder="Sisestage valideerimisfunktsioon (valikuline)">
                </div>
                <div class="form-group mt-2">
                    <label for="assignmentDueAt">Tähtaeg<span class="text-danger" data-bs-toggle="tooltip" title="See väli on kohustuslik">*</span></label>
                    <input type="date" class="form-control" id="assignmentDueAt">
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-primary" id="saveAssignmentBtn" disabled>Salvesta</button>
                <button type="button" class="btn btn-secondary" id="clearFieldsBtn" data-bs-dismiss="modal">Tühista</button>
            </div>
        </div>
    </div>
</div>

<script>
    async function deleteAssignment(assignmentId) {

        if (confirm('Kas olete kindel, et soovite selle ülesande kustutada?')) {
            await ajax('admin/deleteAssignment', { assignmentId: assignmentId }, function (response) {
                if (response.status === 200) {
                    location.reload();
                }
            }, function (error) {
                alert('Viga: ' + error);
            });
        }

    }

    $(document).ready(function() {
        $('[data-bs-toggle="tooltip"]').tooltip();

        function checkRequiredFields() {
            const assignmentName = $.trim($('#assignmentName').val());
            const assignmentInstructions = $.trim($('#assignmentInstructions').val());
            const assignmentDueAt = $('#assignmentDueAt').val();

            if (assignmentName && assignmentInstructions && assignmentDueAt) {
                $('#saveAssignmentBtn').prop('disabled', false);
            } else {
                $('#saveAssignmentBtn').prop('disabled', true);
            }
        }

        $('#assignmentName, #assignmentInstructions, #assignmentDueAt').on('input change', function() {
            checkRequiredFields();
        });

        $('#clearFieldsBtn').on('click', function() {
            $('#addAssignmentModal').find('input, textarea').val('');
            $('#saveAssignmentBtn').prop('disabled', true);
        });

        $('#saveAssignmentBtn').on('click', async function () {
            const assignmentName = $.trim($('#assignmentName').val());
            const assignmentInstructions = $.trim($('#assignmentInstructions').val());
            const assignmentInitialCode = $.trim($('#assignmentInitialCode').val());
            const assignmentValidationFunction = $.trim($('#assignmentValidationFunction').val());
            const assignmentDueAt = $('#assignmentDueAt').val();

            if (!assignmentName || !assignmentInstructions || !assignmentDueAt) {
                alert('Palun täitke kõik vajalikud väljad (Nimi, Juhised, Tähtaeg).');
                return;
            }

            const formData = {
                subjectId: <?= $this->getId() ?>,
                assignmentName: assignmentName,
                assignmentInstructions: assignmentInstructions,
                assignmentInitialCode: assignmentInitialCode,
                assignmentValidationFunction: assignmentValidationFunction,
                assignmentDueAt: assignmentDueAt
            };

            console.log(formData);

            await ajax('admin/addAssignment', formData, function (response) {
                if (response.status === 200) {
                    $('#addAssignmentModal').modal('hide');
                    location.reload();
                }
            }, function (error) {
                alert('Viga: ' + error);
            });
        });
    });
</script>

