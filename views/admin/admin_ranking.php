<div class="row">
    <h1 style="margin-bottom: 1em">Kandidaadi Ranking</h1>
    <div class="row">
        <div class="col-md-12">
            <h3>Keskmine lahendatud ülesannete arv: <?= number_format($this->averageExercisesDone, 2) ?></h3>
        </div>
    </div>

    <div class="table-responsive">
        <table class="table table-striped table-bordered">
            <thead>
            <tr>
                <th>Rank</th>
                <th>Nimi</th>
                <th>Isikukood</th>
                <th>Ajakulu</th>
                <th>Lahendatud ülesanded</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($users as $user): ?>
                <tr>
                    <td><?= $user['userRank'] ?></td>
                    <td><?= $user['userName'] ?></td>
                    <td><?= $user['userPersonalCode'] ?></td>
                    <td><?= $user['userTimeTotal'] ?></td>
                    <td><?= $user['userExercisesDone'] ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

