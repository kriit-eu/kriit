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
                    <th>Aega jäänud</th>
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
                        <td>
                        <?php
                            if (!empty($user['userTimeUpAt'])) {
                                $now = new DateTime();
                                $upAt = new DateTime($user['userTimeUpAt']);
                                $diff = $upAt->getTimestamp() - $now->getTimestamp();
                                $display = '';
                                if ($diff > 0) {
                                    $minutes = floor($diff / 60);
                                    $seconds = $diff % 60;
                                    $display = sprintf('%02d:%02d', $minutes, $seconds);
                                } else {
                                    $display = 'Aeg läbi';
                                }
                                // Output a span with data attributes for JS
                                echo '<span class="time-left" data-timeupat="' . htmlspecialchars($user['userTimeUpAt']) . '">' . $display . '</span>';
                            } else {
                                echo '';
                            }
                        ?>
                        </td>
</div>
<script>
function updateTimeLeft() {
    const now = new Date();
    document.querySelectorAll('.time-left').forEach(function(span) {
        const timeUpAt = span.getAttribute('data-timeupat');
        if (!timeUpAt) return;
        const upAt = new Date(timeUpAt.replace(' ', 'T'));
        let diff = Math.floor((upAt - now) / 1000);
        if (diff > 0) {
            const minutes = Math.floor(diff / 60);
            const seconds = diff % 60;
            span.textContent = (minutes < 10 ? '0' : '') + minutes + ':' + (seconds < 10 ? '0' : '') + seconds;
        } else {
            span.textContent = 'Aeg läbi';
        }
    });
}
setInterval(updateTimeLeft, 1000);
window.addEventListener('DOMContentLoaded', updateTimeLeft);
</script>
                    <td><?= $user['userExercisesDone'] ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

