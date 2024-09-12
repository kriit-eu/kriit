<div class="row">
    <h1>Kandidaadid</h1>
    <div class="col text-end mb-3">
        <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addApplicantModal">
            Lisa uus kandidaat
        </button>
    </div>
    <div class="table-responsive">
        <table class="table table-striped table-bordered">
            <thead>
            <tr>
                <th>Nimi</th>
                <th>Isikukood</th>
                <th>Email</th>
                <th>Esm. sisselogimine</th>
                <th>Alustamise aeg</th>
                <th>Ajakulu</th>
                <th>Ülesandeid</th>
                <th>Tegevused</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($users as $user): ?>
                <tr data-href="applicants/<?= $user['userId'] ?>">
                    <td><?= $user['userName'] ?></td>
                    <td><?= $user['userPersonalCode'] ?></td>
                    <td><?= $user['userEmail'] ?? '' ?></td>
                    <td><?= $user['userFirstLogin'] ?></td>
                    <td><?= $user['userStartTimer'] ?></td>
                    <td><?= $user['userTimeTotal'] ?></td>
                    <td><?= $user['userExercisesDone'] === 0 ? '' : $user['userExercisesDone'] ?></td>
                    <td>
                        <a href="#" class="text-warning edit-user" data-id="<?= $user['userId'] ?>"
                           data-name="<?= $user['userName'] ?>" data-personalcode="<?= $user['userPersonalCode'] ?>">
                            <i class="bi bi-pencil-fill"></i>
                        </a>&nbsp;

                        <a href="#" class="text-danger delete-user" data-id="<?= $user['userId'] ?>">
                            <i class="bi bi-trash-fill"></i>
                        </a>&nbsp;
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<div class="modal fade" id="addApplicantModal" tabindex="-1" role="dialog" aria-labelledby="addApplicantModalLabel"
     aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addApplicantModalLabel">Lisa uus kandidaat</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="addApplicantForm" method="post">
                    <input type="hidden" id="userId" name="userId">
                    <div class="mb-3">
                        <label for="userName" class="form-label">Nimi</label>
                        <input type="text" name="userName" class="form-control" id="userName"
                               aria-describedby="userNameHelp" required>
                        <div id="userNameHelp" class="form-text">Sisesta kandidaadi nimi</div>
                    </div>
                    <div class="mb-3">
                        <label for="userPersonalCode" class="form-label">Isikukood</label>
                        <input type="text" name="userPersonalCode" class="form-control" id="userPersonalCode"
                               aria-describedby="userPersonalCodeHelp" data-context="applicant" required>
                        <div id="userPersonalCodeHelp" class="form-text">Sisesta kandidaadi isikukood</div>
                    </div>
                    <button type="submit" id="addApplicant" class="btn btn-primary">Lisa uus</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    $(document).ready(function () {
        const $userNameInput = $("#userName");
        const $userNameHelp = $("#userNameHelp");
        const $userPersonalCodeInput = $("#userPersonalCode");
        const $submitButton = $("#addApplicant");
        const $modalTitle = $("#addApplicantModalLabel");
        const $userIdInput = $("#userId");

        $('.edit-user').on('click', function (e) {
            e.preventDefault();

            const userId = $(this).data('id');
            const userName = $(this).data('name');
            const userPersonalCode = $(this).data('personalcode');

            $userIdInput.val(userId);
            $userNameInput.val(userName);
            $userPersonalCodeInput.val(userPersonalCode);

            $modalTitle.text('Muuda kandidaadi andmed');

            $submitButton.text('Salvesta muudatused');

            $('#addApplicantModal').modal('show');
        });

        $('.delete-user').on('click', function (e) {
            e.preventDefault();

            const userId = $(this).data('id');

            if (confirm('Oled kindel, et soovid selle kandidaadi kustutada?')) {
                const url = 'admin/AJAX_deleteUser';
                const data = {userId: userId};

                ajax(url, data, function (response) {
                    alert('Kandidaat edukalt kustutatud!');
                    location.reload();
                }, function (error) {
                    alert('Viga: ' + error);
                });
            }
        });

        $('#addApplicantForm').on('submit', function (e) {
            e.preventDefault();

            const data = $(this).serialize();
            let url;

            if ($userIdInput.val()) {
                url = 'admin/editApplicant';
            } else {
                url = 'admin/addApplicant';
            }

            ajax(url, data, function (response) {
                alert('Andmed edukalt salvestatud!');
                $('#addApplicantModal').modal('hide');
                location.reload();
            }, function (error) {
                alert('Viga: ' + error);
            });
        });

        $('#addApplicantModal').on('hidden.bs.modal', function () {
            $userIdInput.val('');
            $userNameInput.val('');
            $userPersonalCodeInput.val('');

            $modalTitle.text('Lisa uus kandidaat');
            $submitButton.text('Lisa uus');
        });
    });
</script>
