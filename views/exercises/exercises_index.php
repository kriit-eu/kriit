<div class="row">

    <h1>Ülesanded</h1>

    <p>Vali loendist ülesanne, mida alustada.</p>
    <div class="table-responsive">
        <table class="table table-striped table-bordered">
            <tbody>
            <?php foreach ($exercises as $exercise): ?>
                <tr data-href="exercises/<?= $exercise['exerciseId'] ?>">
                    <td><?= $exercise['exerciseName'] ?></td>
                    <td>
                        <?php if ($exercise['exerciseStatusId'] === 2): ?>
                            Lahendatud
                        <?php else: ?>
                            <a href="exercises/<?= $exercise['exerciseId'] ?>">Lahenda</a>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <div style="margin-bottom: 1em">
        <?php require 'templates/partials/timer.php'; ?>
    </div>
</div>
