<h1 style="margin-bottom: 1em">Kandidaadi Ranking</h1>
<style>
    /* Responsive styles for phones */
    @media (max-width: 600px) {
        h1 {
            font-size: 1.3em;
            margin-bottom: 0.7em;
        }

        h3 {
            font-size: 1em;
        }

        .table-responsive {
            width: 100%;
            overflow-x: auto;
            box-sizing: border-box;
            padding: 0;
            margin: 0 auto;
        }

        .table {
            font-size: 0.95em;
            min-width: 600px;
            width: 100%;
            box-sizing: border-box;
        }

            .table th,
            .table td {
                padding: 0.15em 0.3em;
                white-space: normal;
                word-break: break-word;
                font-size: 0.95em;
                max-width: 120px;
                overflow-wrap: break-word;
            }
            .table td:nth-child(3), .table th:nth-child(3) {
                white-space: nowrap;
            }

        .btn {
            font-size: 0.95em;
            padding: 0.3em 0.7em;
        }

        .row {
            margin-left: 0;
            margin-right: 0;
            padding-left: 0;
            padding-right: 0;
            max-width: 100vw;
            overflow-x: hidden;
        }
    }
</style>
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
                <th>Detailvaade</th>
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
                            echo '<span class="time-left" data-timeupat="' . htmlspecialchars($user['userTimeUpAt']) . '">' . $display . '</span>';
                        } else {
                            echo '';
                        }
                        ?>
                    </td>
                    <td><?= $user['userExercisesDone'] ?></td>
                    <td>
                        <a href="/views/admin/admin_exercise_detail.php?userId=<?= $user['userId'] ?>" class="btn btn-info btn-sm">Vaata</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
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
                if (diff >= 60) {
                    const minutes = Math.ceil(diff / 60);
                    span.textContent = minutes + ' min';
                } else {
                    span.textContent = diff + ' s';
                }
            } else {
                span.textContent = 'Aeg läbi';
            }
        });
    }
    setInterval(updateTimeLeft, 1000);
    window.addEventListener('DOMContentLoaded', updateTimeLeft);
</script>