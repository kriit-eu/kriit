<div class="row">
    <h1 style="margin-bottom: 1em">Kandidaadi Ranking</h1>

    <div class="table-responsive">
        <table class="table table-striped table-bordered">
            <thead>
            <tr>
                <th>Nimi</th>
                <th>Isikukood</th>
                <th>Ajakulu</th>
                <th>Lahendatud ülesanded</th>
                <th>Rank</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($users as $user): ?>
                <tr>
                    <td><?= $user['userName'] ?></td>
                    <td><?= $user['userPersonalCode'] ?></td>
                    <td><?= $user['userTimeTotal'] ?></td>
                    <td><?= $user['userExercisesDone'] ?></td>
                    <td><?= $user['userRank'] ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

