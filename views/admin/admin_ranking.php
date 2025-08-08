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
            font-size: 0.85em;
            width: 100%;
            min-width: 0;
            box-sizing: border-box;
            table-layout: auto;
        }

        .table th,
        .table td {
            padding: 0.12em 0.15em;
            white-space: normal;
            word-break: break-word;
            font-size: 0.85em;
            max-width: 80px;
            overflow-wrap: break-word;
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
                <th>Nimi<br><span class="d-inline d-sm-none" style="font-weight:normal">ID kood</span></th>
                <th class="d-none d-sm-table-cell">
                    <span class="d-none d-sm-inline">Isikukood</span>
                </th>
                <th>Ajakulu</th>
                <th>Aega jäänud</th>
                <th>
                    <span class="d-none d-sm-inline">Lahendatud ülesanded</span>
                    <span class="d-inline d-sm-none">Lah. Ül</span>
                </th>
                <th>
                    <span class="d-none d-sm-inline">Detailvaade</span>
                    <span class="d-inline d-sm-none">Detailv.</span>
                </th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($users as $user): ?>
                <tr>
                    <td><?= $user['userRank'] ?></td>
                    <td>
                        <span class="d-inline d-sm-none"><strong><?= $user['userName'] ?></strong></span>
                        <span class="d-none d-sm-inline"><?= $user['userName'] ?></span>
                        <br class="d-inline d-sm-none" />
                        <span class="d-inline d-sm-none" style="font-size:0.95em;color:#555;"><?= $user['userPersonalCode'] ?></span>
                    </td>
                    <td class="d-none d-sm-table-cell"><?= $user['userPersonalCode'] ?></td>
                    <td>
                        <?php
                        // Fuzzy time formatting for Ajakulu
                        $raw = $user['userTimeTotal'];
                        $seconds = 0;
                        if (is_numeric($raw)) {
                            $seconds = (int)$raw;
                        } elseif (is_string($raw) && preg_match('/^(\d{1,2}:)?\d{1,2}:\d{2}$/', $raw)) {
                            // Parse HH:MM:SS or MM:SS
                            $parts = explode(':', $raw);
                            if (count($parts) === 3) {
                                $seconds = $parts[0]*3600 + $parts[1]*60 + $parts[2];
                            } elseif (count($parts) === 2) {
                                $seconds = $parts[0]*60 + $parts[1];
                            }
                        }
                        if ($seconds > 0) {
                            $h = floor($seconds / 3600);
                            $m = floor(($seconds % 3600) / 60);
                            $s = $seconds % 60;
                            $parts = [];
                            if ($h > 0) $parts[] = $h . ' h';
                            if ($m > 0) $parts[] = $m . ' min';
                            if ($h === 0 && $m === 0 && $s > 0) $parts[] = $s . ' s';
                            echo implode(' ', $parts);
                        } else {
                            echo '-';
                        }
                        ?>
                    </td>
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