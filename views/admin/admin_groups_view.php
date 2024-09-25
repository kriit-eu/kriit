<style>
    #students-table th {
        background-color: #f2f2f2;
    }

    #students-table tr {
        cursor: pointer;
    }


    #students-table .delete-btn {
        color: red !important;
        cursor: pointer !important;
        font-weight: bold !important;
        font-size: 1.5em !important;
    }

    #students-table .delete-btn:hover {
        color: darkred !important;
    }

</style>

<h1><?=$group['groupName']?></h1>
<div class="col text-end mb-3">
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addStudentModal">Lisa uus Õpilane</button>
</div>
<div class="row">
    <div class="table-responsive">
        <table id="students-table" class="table table-bordered">
            <thead>
            <tr>
                <th>Isikukood</th>
                <th>Nimi</th>
                <th>Email</th>
                <th>Tahvel ID</th>
                <th></th>
            </tr>
            <tr></tr>
            </thead>
            <tbody>
            <?php foreach ($group['students'] as $s): ?>
                <tr>
                    <td onclick="window.location.href='admin/users'" ><b><?= $s['userPersonalCode'] ?></b></td>
                    <td onclick="window.location.href='admin/users'"><?= $s['userName'] ?></td>
                    <td onclick="window.location.href='admin/users'"><?= $s['userEmail'] ?></td>
                    <td onclick="window.location.href='admin/users'"><?= $s['tahvelStudentId'] ?></td>
                    <td class="text-center" style="">
                        <span class="delete-btn" onclick="deleteStudent(<?= $s['userId']?>)">&#x2717;</span>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Add Student Modal -->
<div class="modal fade" id="addStudentModal" tabindex="-1" aria-labelledby="addStudentModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addStudentModalLabel">Lisa uus Õpilane</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="addStudentForm">
                    <div class="mb-3">
                        <label for="userName" class="form-label">Õppija nimi<span class="text-danger" data-bs-toggle="tooltip" title="See väli on kohustuslik">*</span></label>
                        <input type="text" class="form-control" id="userName" required>
                    </div>
                    <div class="mb-3">
                        <label for="userPersonalCode" class="form-label">Isikukood<span class="text-danger" data-bs-toggle="tooltip" title="See väli on kohustuslik">*</span></label>
                        <input type="text" class="form-control" id="userPersonalCode" required>
                    </div>
                    <div class="mb-3">
                        <label for="tahvelStudentId" class="form-label">Tahvel ID</label>
                        <input type="text" class="form-control" id="tahvelStudentId" required>
                    </div>
                    <div class="mb-3">
                        <label for="userEmail" class="form-label">Email</label>
                        <input type="email" class="form-control" id="userEmail" required>
                    </div>
                </form>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-primary" id="saveStudentBtn" disabled>Salvesta</button>
                <button type="button" class="btn btn-secondary" id="clearFieldsBtn" data-bs-dismiss="modal">Tühista</button>
            </div>
        </div>
    </div>
</div>

<script>

    async function deleteStudent(studentId) {
        if (confirm('Kas olete kindel, et soovite selle õppija kustutada?')) {
            await ajax('admin/deleteStudent', { userId: studentId }, function (response) {
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
            const userName = $.trim($('#userName').val());
            const userPersonalCode = $.trim($('#userPersonalCode').val());

            if (userName && userPersonalCode) {
                $('#saveStudentBtn').prop('disabled', false);
            } else {
                $('#saveStudentBtn').prop('disabled', true);
            }
        }

        // Check fields on input change
        $('#userName, #userPersonalCode').on('input', function() {
            checkRequiredFields();
        });

        // Clear fields on cancel
        $('#clearFieldsBtn').on('click', function() {
            $('#addStudentModal').find('input').val('');
            $('#saveStudentBtn').prop('disabled', true);
        });

        // Save student data
        $('#saveStudentBtn').on('click', async function () {
            const userName = $.trim($('#userName').val());
            const userPersonalCode = $.trim($('#userPersonalCode').val());
            const tahvelStudentId = $.trim($('#tahvelStudentId').val());
            const userEmail = $.trim($('#userEmail').val());

            if (!userName || !userPersonalCode ) {
                alert('Palun täitke kõik vajalikud väljad.');
                return;
            }

            const formData = {
                groupId: <?= $this->getId() ?>,
                userName: userName,
                userPersonalCode: userPersonalCode,
                tahvelStudentId: tahvelStudentId,
                userEmail: userEmail
            };

            console.log(formData);

            await ajax('admin/addStudent', formData, function (response) {
                if (response.status === 200) {
                    $('#addStudentModal').modal('hide');
                    location.reload();
                }
            }, function (error) {
                alert('Viga: ' + error);
            });
        });
    })
</script>
