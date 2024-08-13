<div class="row">
    <h1>Kandidaadid</h1>
    <div class="table-responsive">
        <table class="table table-striped table-bordered">
            <thead>
            <tr>
                <th>Nimi</th>
                <th>Isikukood</th>
                <th>Esm. sisselogimine</th>
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
                    <td><?= $user['userFirstLogin'] ?></td>
                    <td><?= $user['userTimeTotal'] ?></td>
                    <td><?= $user['userExercisesDone'] === 0 ? '' : $user['userExercisesDone'] ?></td>
                    <td>
                        <a href="users/edit/<?= $user['userId'] ?>" class="text-warning">
                            <i class="bi bi-pencil-fill"></i>
                        </a>&nbsp;

                        <a href="users/delete/<?= $user['userId'] ?>" class="text-danger">
                            <i class="bi bi-trash-fill"></i>
                        </a>&nbsp;
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
