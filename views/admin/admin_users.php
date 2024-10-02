<div class="row">
    <h1>Kasutajad</h1>
    <div class="col text-end mb-3">
        <div class="d-inline-block">
            <button class="btn btn-primary" onclick="window.location.href='applicants'">Kandidaadid</button>
        </div>
        <div class="d-inline-block">
            <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addUserModal"
                    data-context="admin">
                Lisa uus kasutaja
            </button>
        </div>
    </div>

    <div class="col-12 mb-3">
        <input type="text" id="searchBox" class="form-control" placeholder="Otsi kasutajaid...">
        <small class="form-text feedback"></small>
    </div>

    <div class="table-responsive">
        <table class="table table-striped table-bordered table-searchable">
            <thead>
            <tr>
                <th class="text-center">#</th>
                <th>Nimi</th>
                <th>Isikukood</th>
                <th class="text-center">Grupp</th>
                <th class="text-center">Email</th>
                <th class="text-center">Admin</th>
                <th>Tegevused</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($users as $user): ?>
                <tr>
                    <td class="text-center"><?= $user['userId'] ?></td>
                    <td><?= $user['userName'] ?></td>
                    <td><?= $user['userPersonalCode'] ?></td>
                    <td class="text-center"><?= $user['groupName'] ?></td>
                    <td class="text-center"><?= $user['userEmail'] ?></td>
                    <td class="text-center"><?= $user['userIsAdmin'] ? "&#9989;" : "" ?></td>
                    <td data-searchable="false">
                        <div class="btn-group btn-group-sm" role="group">
                            <a href="#" class="text-warning edit-user" data-id="<?= $user['userId'] ?>"
                               data-name="<?= $user['userName'] ?>" data-personalcode="<?= $user['userPersonalCode'] ?>"
                               data-userisadmin="<?= $user['userIsAdmin'] ?>" data-groupid="<?= $user['groupId'] ?>"
                               data-useremail="<?= $user['userEmail'] ?>"
                               data-context="edit">
                                <i class="bi bi-pencil-fill"></i>
                            </a>
                            <a href="#" class="text-danger delete-user" data-id="<?= $user['userId'] ?>">
                                <i class="bi bi-trash-fill"></i>
                            </a>
                        </div>
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
                <h5 class="modal-title" id="addUserModalLabel">Lisa uus kasutaja</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="addUserForm" method="post">
                    <input type="hidden" id="userId" name="userId">

                    <div class="mb-3">
                        <label for="userName" class="form-label">Nimi</label>
                        <input type="text" name="userName" class="form-control" id="userName"
                               aria-describedby="userNameHelp" required>
                        <div id="userNameHelp" class="form-text">Sisesta kasutaja nimi</div>
                    </div>

                    <div class="mb-3">
                        <label for="userPersonalCode" class="form-label">Isikukood</label>
                        <input type="text" name="userPersonalCode" class="form-control" id="userPersonalCode"
                               aria-describedby="userPersonalCodeHelp" data-context="admin" required>
                        <div id="userPersonalCodeHelp" class="form-text">Sisesta kasutaja isikukood</div>
                    </div>
                    <div class="mb-3">
                        <label for="userEmail" class="form-label">Email</label>
                        <input type="text" name="userEmail" class="form-control" id="userEmail"
                                data-context="admin">
                        <div id="userEmail" class="form-text">Sisesta kasutaja email</div>
                    </div>

                    <div class="mb-3">
                        <label for="userPassword" class="form-label">Parool</label>
                        <input type="password" name="userPassword" class="form-control" id="userPassword"
                               aria-describedby="userPasswordHelp">
                        <div id="userPasswordHelp" class="form-text">Sisesta kasutaja parool</div>
                    </div>

                    <div class="mb-3">
                        <label for="userGroup" class="form-label">Grupp</label>
                        <select name="groupId" class="form-control" id="userGroup">
                            <option value="">-- Vali grupp --</option>
                            <?php foreach ($groups as $group): ?>
                                <option value="<?= $group['groupId'] ?>"><?= $group['groupName'] ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-3 form-check">
                        <input type="checkbox" name="userIsAdmin" class="form-check-input" id="userIsAdmin">
                        <label class="form-check-label" for="userIsAdmin">Admin</label>
                    </div>

                    <button type="submit" id="submitUser" class="btn btn-primary">Lisa uus</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const searchBox = document.getElementById('searchBox');
        const feedback = document.querySelector('.feedback');
        const table = document.querySelector('.table-searchable tbody');
        const rows = [...table.rows];

        const getCellValue = (cell) => cell.textContent || cell.innerText;

        searchBox.addEventListener('input', function (e) {
            const term = e.target.value.toLowerCase();
            const hasValue = !!term.length;
            let resultCount = 0;

            rows.forEach((row) => {
                const cells = [...row.querySelectorAll('td:not([data-searchable="false"])')];
                let found = cells.some((cell) => {
                    const value = getCellValue(cell).toLowerCase();
                    return value.includes(term);
                });

                row.style.display = found ? '' : 'none';
                if (found) resultCount++;
            });

            feedback.textContent = hasValue
                ? resultCount === 1
                    ? '1 tulemus leitud'
                    : `${resultCount} tulemust leitud`
                : '';
        });

        const $userNameInput = $("#userName");
        const $userPersonalCodeInput = $("#userPersonalCode");
        const $userPasswordInput = $("#userPassword");
        const $submitButton = $("#submitUser");
        const $modalTitle = $("#addUserModalLabel");
        const $userIdInput = $("#userId");
        const $userIsAdminCheckbox = $("#userIsAdmin");
        const $userGroupSelect = $("#userGroup");
        const $userEmailInput = $("#userEmail");

        function setContext(context) {
            if (context === "edit") {
                $modalTitle.text('Muuda kasutaja andmed');
                $submitButton.text('Salvesta muudatused');
                $userPasswordInput.removeAttr('required');
            } else if (context === "admin") {
                $modalTitle.text('Lisa uus kasutaja');
                $submitButton.text('Lisa uus');
                $userPasswordInput.removeAttr('required');
            }
        }

        $('.edit-user').on('click', function (e) {
            e.preventDefault();

            const context = $(this).data('context');
            setContext(context);

            const userId = $(this).data('id');
            const userName = $(this).data('name');
            const userPersonalCode = $(this).data('personalcode');
            const userIsAdmin = $(this).data('userisadmin');
            const userGroupId = $(this).data('groupid');
            const userEmail = $(this).data('useremail');

            $userIdInput.val(userId);
            $userNameInput.val(userName);
            $userPersonalCodeInput.val(userPersonalCode);
            $userIsAdminCheckbox.prop('checked', userIsAdmin);
            $userEmailInput.val(userEmail);

            if (userGroupId) {
                $('#userGroup').val(userGroupId);
            } else {
                $('#userGroup').val(null);
            }

            $('#addUserModal').modal('show');
        });

        $('[data-bs-toggle="modal"]').on('click', function () {
            const context = $(this).data('context');
            setContext(context);

            clearForm();

        });

        $('#addUserForm').on('submit', async function (e) {
            e.preventDefault();

            const data = $(this).serialize();
            let url;

            if ($userIdInput.val()) {
                url = 'admin/editUser';
            } else {
                url = 'admin/addUser';
            }

            await ajax(url, data, function (response) {
                $('#addUserModal').modal('hide');
                location.reload();
            }, function (error) {
                alert('Viga: ' + error);
            });
        });

        $('.delete-user').on('click', async function (e) {
            e.preventDefault();
            const userId = $(this).data('id');
            if (confirm('Kas oled kindel, et soovid kasutaja kustutada?')) {
                await ajax('admin/deleteUser', {userId: userId}, function (response) {
                    location.reload();
                }, function (error) {
                    alert('Viga: ' + error);
                });
            }
        });

        $('#addUserModal').on('hidden.bs.modal', function () {
            clearForm();
        });

        function clearForm() {
            $userIdInput.val('');
            $userNameInput.val('');
            $userPersonalCodeInput.val('');
            $userPasswordInput.val('');
            $userIsAdminCheckbox.prop('checked', false);
            $userGroupSelect.val('');
            $userEmailInput.val('');
        }
    });
</script>

<style>
    .table-searchable .is-hidden {
        display: none;
    }
</style>
