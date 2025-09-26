<?php
// If controller didn't provide $users (or we want to refresh from DB), load users and aggregate
// per-course completed exercise counts using the userExercises table joined with exercises.
use App\Db;

$courseId = $this->courseId ?? null;
if (empty($users) && $courseId) {
    // Load users ordered by their userTimeUpAt as requested
    $dbUsers = Db::getAll("SELECT * FROM users ORDER BY userTimeUpAt DESC");

    $users = [];
    foreach ($dbUsers as $u) {
        // Skip teachers and admins from ranking list
        if (!empty($u['userIsTeacher']) || !empty($u['userIsAdmin'])) {
            continue;
        }
        // Count distinct completed exercises for this course using the raw userExercises table
        // Here we consider a row completed when endTime IS NOT NULL
        $count = Db::getOne(
            "SELECT COUNT(DISTINCT ue.exerciseId) FROM userExercises ue JOIN exercises e ON ue.exerciseId = e.exerciseId WHERE ue.userId = ? AND e.courseId = ? AND ue.endTime IS NOT NULL",
            [$u['userId'], $courseId]
        );

        $users[] = [
            'userId' => $u['userId'],
            'userName' => $u['userName'],
            'userPersonalCode' => $u['userPersonalCode'] ?? '',
            'userTimeTotal' => $u['userTimeTotal'] ?? null,
            'userTimeUpAt' => $u['userTimeUpAt'] ?? null,
            // Include groupId so the view can decide to hide grouped users for legacy course 1
            'groupId' => $u['groupId'] ?? null,
            'userExercisesDone' => (int)$count,
        ];
    }

    // If this is course 1, apply legacy filter: exclude users who belong to a group
    if ($courseId == 1) {
        $users = array_values(array_filter($users, function ($x) { return empty($x['groupId']); }));
    }

    // Compute ranks: simply by current ordering (userTimeUpAt DESC) but prefer to rank by exercises done desc then time
    usort($users, function ($a, $b) {
        if ($a['userExercisesDone'] !== $b['userExercisesDone']) return $b['userExercisesDone'] <=> $a['userExercisesDone'];
        // Fallback to userTimeTotal asc (smaller is better) if available
        $at = $a['userTimeTotal'] ?? PHP_INT_MAX;
        $bt = $b['userTimeTotal'] ?? PHP_INT_MAX;
        if ($at !== $bt) return $at <=> $bt;
        return $a['userId'] <=> $b['userId'];
    });

    $rank = 1;
    foreach ($users as &$u) {
        $u['userRank'] = $rank++;
    }
    unset($u);

    // Compute average
    $filtered = array_filter($users, function ($x) { return ($x['userExercisesDone'] ?? 0) > 0; });
    $totalSolved = array_sum(array_column($filtered, 'userExercisesDone'));
    $userCount = count($filtered);
    $this->averageExercisesDone = $userCount > 0 ? $totalSolved / $userCount : 0;
}
?>

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
        .table th:nth-child(1), .table td:nth-child(1),
        .table th:nth-child(2), .table td:nth-child(2),
        .table th:nth-child(4), .table th:nth-child(5),
        .table td:nth-child(4), .table td:nth-child(5) {
            text-align: center;
            vertical-align: middle;
        }

        .btn {
            font-size: 0.95em;
            padding: 0.3em 0.7em;
        }
        .table th:last-child,
        .table td:last-child {
            text-align: center;
            vertical-align: middle;
        }
        .table th:nth-child(6), .table td:nth-child(6) {
            text-align: center;
            vertical-align: middle;
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
                    <span class="d-inline d-sm-none">Lah.<br>Ül</span>
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
<?php
// Previously this file attempted to include an admin ranking template
// which doesn't exist in some installations and caused a fatal error.
// The per-course ranking table is rendered above, so no include is necessary.
// If an admin ranking template is added later, guard it with file_exists here.
// Example (uncomment to enable):
// if (file_exists(__DIR__ . '/../admin/admin_ranking.php')) {
//     include __DIR__ . '/../admin/admin_ranking.php';
// }
