<div class="row">

    <h1>Ülesanded</h1>

    <p>Vali ülesanne, mida soovid alustada. Pea meeles, et pingerida kujuneb kõige kiiremate lahendajate põhjal. Kui
        jääd mõne ülesande juures kinni ja tunned, et mõtted hakkavad ammenduma, proovi vahepeal teist ülesannet – see
        võimaldab uutel ideedel ja lahendusviisidel tekkida. Lahenduste leidmiseks võid kasutada internetti, kuid
        suhtlemine teiste isikute (sh tehisintellektiga) on keelatud. Arvuti ekraanipilte salvestatakse!</p>
    <div class="table-responsive">
        <table class="table table-bordered">
            <thead>
            <tr>
                <th>Ülesanne</th>
                <th></th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($exercises as $exercise): ?>
                <tr data-href="exercises/<?= $exercise['exerciseId'] ?>">
                    <td><?= $exercise['exerciseName'] ?></td>
                    <td>
                        <?php if ($exercise['isSolved']): ?>
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
