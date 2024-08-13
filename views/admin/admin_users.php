<div class="row">
    <h1>Administraatorid</h1>
    <div class="col text-end mb-3">
        <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addUserModal"
                data-context="admin">
            Lisa uus administraator
        </button>
    </div>
    <div class="table-responsive">
        <table class="table table-striped table-bordered">
            <thead>
            <tr>
                <th>Nimi</th>
                <th>Isikukood</th>
                <th>Esm. sisselogimine</th>
                <th>Tegevused</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($users as $user): ?>
                <tr>
                    <td><?= $user['userName'] ?></td>
                    <td><?= $user['userPersonalCode'] ?></td>
                    <td><?= $user['userFirstLogin'] ?></td>
                    <td>
                        <a href="#" class="text-warning edit-user" data-id="<?= $user['userId'] ?>"
                           data-name="<?= $user['userName'] ?>" data-personalcode="<?= $user['userPersonalCode'] ?>"
                           data-context="edit">
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

<div class="modal fade" id="addUserModal" tabindex="-1" role="dialog" aria-labelledby="addUserModalLabel"
     aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addUserModalLabel">Lisa uus administraator</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="addUserForm" method="post">
                    <input type="hidden" id="userId" name="userId">
                    <div class="mb-3">
                        <label for="userName" class="form-label">Nimi</label>
                        <input type="text" name="userName" class="form-control" id="userName"
                               aria-describedby="userNameHelp" required>
                        <div id="userNameHelp" class="form-text">Sisesta administraatori nimi</div>
                    </div>
                    <div class="mb-3">
                        <label for="userPersonalCode" class="form-label">Isikukood</label>
                        <input type="text" name="userPersonalCode" class="form-control" id="userPersonalCode"
                               aria-describedby="userPersonalCodeHelp" data-context="admin" required>
                        <div id="userPersonalCodeHelp" class="form-text">Sisesta administraatori isikukood</div>
                    </div>
                    <div class="mb-3">
                        <label for="userPassword" class="form-label">Parool</label>
                        <input type="password" name="userPassword" class="form-control" id="userPassword"
                               aria-describedby="userPasswordHelp" required>
                        <div id="userPasswordHelp" class="form-text">Sisesta administraatori parool</div>
                    </div>
                    <button type="submit" id="submitUser" class="btn btn-primary">Lisa uus</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    $(document).ready(function () {
        const $userNameInput = $("#userName");
        const $userPersonalCodeInput = $("#userPersonalCode");
        const $userPasswordInput = $("#userPassword");
        const $submitButton = $("#submitUser");
        const $modalTitle = $("#addUserModalLabel");
        const $userIdInput = $("#userId");
        const $userNameHelp = $("#userNameHelp");
        const $userPersonalCodeHelp = $("#userPersonalCodeHelp");

        function setContext(context) {
            if (context === "edit") {
                $modalTitle.text('Muuda administraatori andmed');
                $submitButton.text('Salvesta muudatused');
                $userPasswordInput.removeAttr('required');
            } else if (context === "admin") {
                $modalTitle.text('Lisa uus administraator');
                $submitButton.text('Lisa uus');
                $userPasswordInput.attr('required', 'required');
            }
        }

        $('.edit-user').on('click', function (e) {
            e.preventDefault();

            const context = $(this).data('context');
            setContext(context);

            const userId = $(this).data('id');
            const userName = $(this).data('name');
            const userPersonalCode = $(this).data('personalcode');

            $userIdInput.val(userId);
            $userNameInput.val(userName);
            $userPersonalCodeInput.val(userPersonalCode);

            $('#addUserModal').modal('show');
        });

        $('[data-bs-toggle="modal"]').on('click', function () {
            const context = $(this).data('context');
            setContext(context);

            $userIdInput.val('');
            $userNameInput.val('');
            $userPersonalCodeInput.val('');
            $userPasswordInput.val('');
            $userNameHelp.text('Sisesta administraatori nimi').removeClass('text-danger');
            $userPersonalCodeHelp.text('Sisesta administraatori isikukood').removeClass('text-danger');
        });

        $('.delete-user').on('click', function (e) {
            e.preventDefault();

            const userId = $(this).data('id');

            if (confirm('Oled kindel, et soovid selle administraatori kustutada?')) {
                const url = 'admin/AJAX_deleteUser';
                const data = {userId: userId};

                ajax(url, data, function (response) {
                    alert('Administraator edukalt kustutatud!');
                    location.reload();
                }, function (error) {
                    alert('Viga: ' + error);
                });
            }
        });

        $('#addUserForm').on('submit', function (e) {
            e.preventDefault();

            const data = $(this).serialize();
            let url;

            if ($userIdInput.val()) {
                url = 'admin/editUser';
            } else {
                url = 'admin/addUser';
            }

            ajax(url, data, function (response) {
                alert('Andmed edukalt salvestatud!');
                $('#addUserModal').modal('hide');
                location.reload();
            }, function (error) {
                alert('Viga: ' + error);
            });
        });

        $('#addUserModal').on('hidden.bs.modal', function () {
            $userIdInput.val('');
            $userNameInput.val('');
            $userPersonalCodeInput.val('');
            $userPasswordInput.val('');
        });
    });
</script>
