<style>
    #subject-table th {
        background-color: #f2f2f2;
    }
    #subject-table tr {
        cursor: pointer;
    }
</style>

<h1>Ained</h1>
<div class="col text-end mb-3">
    <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addSubjectModal">Lisa uus aine</button>
</div>
<div class="row">
    <div class="table-responsive">
        <table id="subject-table" class="table table-bordered">
            <thead>
            <tr>
                <th>Nimi</th>
                <th>Grupp</th>
                <th>Õpetaja</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($subjects as $subject): ?>
                <tr onclick="window.location.href='admin/subjects/<?= $subject['subjectId'] ?>'">
                    <td><b><?= $subject['subjectName'] ?></b></td>
                    <td><b><?= $subject['subjectGroup'] ?></b></td>
                    <td><b><?= $subject['teacherName'] ?></b></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Add Subject Modal -->
<div class="modal fade" id="addSubjectModal" tabindex="-1" aria-labelledby="addSubjectModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addSubjectModalLabel">Lisa uus Aine</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="addSubjectForm">
                    <div class="mb-3">
                        <label for="subjectName" class="form-label">Aine nimi<span class="text-danger" data-bs-toggle="tooltip" title="See väli on kohustuslik">*</span></label>
                        <input type="text" class="form-control" id="subjectName" required>
                    </div>
                    <div class="mb-3">
                        <label for="subjectExternalId" class="form-label">Tahvli ID<span class="text-danger" data-bs-toggle="tooltip" title="See väli on kohustuslik">*</span></label>
                        <input type="text" class="form-control" id="subjectExternalId" required>
                    </div>
                    <div class="mb-3">
                        <label for="subjectTeacher" class="form-label">Õpetaja<span class="text-danger" data-bs-toggle="tooltip" title="See väli on kohustuslik">*</span></label>
                        <select class="form-control" id="subjectTeacher" required>
                            <option value="">Vali õpetaja</option>
                            <?php foreach ($teachers as $teacher): ?>
                                <option value="<?= $teacher['userId'] ?>"><?= $teacher['userName'] ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="subjectGroup" class="form-label">Grupp<span class="text-danger" data-bs-toggle="tooltip" title="See väli on kohustuslik">*</span></label>
                        <select class="form-control" id="subjectGroup" required>
                            <option value="">Vali grupp</option>
                            <?php foreach ($groups as $group): ?>
                                <option value="<?= $group['groupId'] ?>"><?= $group['groupName'] ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" id="saveSubjectBtn" disabled>Salvesta</button>
                <button type="button" class="btn btn-secondary" id="clearFieldsBtn" data-bs-dismiss="modal">Tühista</button>
            </div>
        </div>
    </div>
</div>

<script>
    $(document).ready(function() {
        // Initialize Bootstrap tooltips
        $('[data-bs-toggle="tooltip"]').tooltip();

        // Disable the button by default
        $('#saveSubjectBtn').prop('disabled', true);

        // Enable/disable the button based on form validation
        $('#addSubjectForm input, #addSubjectForm select').on('input change', function() {
            checkFormValidation();
        });

        function checkFormValidation() {
            const subjectName = $.trim($('#subjectName').val());
            const subjectExternalId = $.trim($('#subjectExternalId').val());
            const subjectTeacher = $('#subjectTeacher').val();
            const subjectGroup = $('#subjectGroup').val();

            // Check if all required fields are filled
            if (subjectName && subjectExternalId && subjectTeacher && subjectGroup) {
                $('#saveSubjectBtn').prop('disabled', false); // Enable the button
            } else {
                $('#saveSubjectBtn').prop('disabled', true);  // Disable the button
            }
        }

        $('#clearFieldsBtn').on('click', function() {
            $('#addSubjectForm').trigger('reset');
            $('#saveSubjectBtn').prop('disabled', true);
        });

        // Handle save subject button click
        $('#saveSubjectBtn').on('click', async function() {
            const subjectName = $.trim($('#subjectName').val());
            const subjectExternalId = $.trim($('#subjectExternalId').val());
            const subjectTeacher = $('#subjectTeacher').val();
            const subjectGroup = $('#subjectGroup').val();

            // Validate required fields
            if (!subjectName || !subjectExternalId || !subjectTeacher || !subjectGroup) {
                alert('Palun täitke kõik vajalikud väljad.');
                return;
            }

            // Form data to send to the server
            const formData = {
                subjectName: subjectName,
                subjectExternalId: subjectExternalId,
                teacherId: subjectTeacher,
                groupId: subjectGroup
            };

            // Example AJAX request to save subject
            await ajax('admin/addSubject', formData, function(response) {
                if (response.status === 200) {
                    $('#addSubjectModal').modal('hide');
                    location.reload();
                }
            }, function(error) {
                alert('Viga: ' + error);
            });
        });
    });
</script>
