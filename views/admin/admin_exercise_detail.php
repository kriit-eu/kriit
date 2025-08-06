<?php
// admin_exercise_detail.php
require_once '../../config.php';
require_once '../../classes/App/Db.php';

$userId = isset($_GET['userId']) ? intval($_GET['userId']) : 0;
if (!$userId) {
    die('Vigane kasutaja ID');
}

// Fetch user info
$user = App\Db::getFirst('SELECT * FROM users WHERE userId = ?', [$userId]);
if (!$user) {
    die('Kasutajat ei leitud');
}

// Fetch all exercises for this user
$progress = App\Db::getAll('
    SELECT e.exerciseId, e.exerciseName, ue.startTime AS startedAt, ue.endTime AS completedAt, ue.status
    FROM exercises e
    LEFT JOIN userExercises ue ON ue.exerciseId = e.exerciseId AND ue.userId = ?
    ORDER BY e.exerciseId
', [$userId]);

?><!DOCTYPE html>
<html lang="et">
<head>
    <meta charset="UTF-8">
    <title>Kasutaja ülesannete detailvaade</title>
    <link rel="stylesheet" href="/assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="/assets/css/all.min.css">
    <link rel="stylesheet" href="/assets/css/main.css">
    <style>
        body { background: #f8f9fa; }
        .custom-container {
            max-width: 1200px;
            margin: 40px auto;
        }
        .detail-card {
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.07);
            border: 1px solid #e3e6ea;
            padding: 2.5rem 2rem 2rem 2rem;
        }
        .table-responsive {
            width: 100%;
        }
        .table {
            width: 100% !important;
            margin-bottom: 0;
        }
        .table thead th {
            border-bottom: 2px solid #dee2e6;
        }
        .badge.bg-success, .badge.bg-warning, .badge.bg-secondary, .badge.bg-danger {
            font-size: 1rem;
            padding: 0.5em 1em;
        }
        /* Fallback for zebra striping if Bootstrap is overridden */
        .table-striped > tbody > tr:nth-of-type(odd) {
            --bs-table-accent-bg: #f2f6fa;
            background-color: var(--bs-table-accent-bg) !important;
        }
        .btn-back {
            margin-top: 1.5rem;
        }
    </style>
</head>
<body>
<div class="container custom-container">
    <div class="detail-card">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <a href="/admin/ranking" class="btn btn-info btn-lg fw-bold shadow-sm rounded-pill px-4 py-2 me-3" style="letter-spacing:0.02em;"><i class="fas fa-arrow-left me-2"></i>Tagasi admini vaatesse</a>
            <h2 class="mb-0 flex-grow-1"><i class="fas fa-user"></i> <?= htmlspecialchars($user['userName']) ?> &mdash; Ülesannete detailvaade</h2>
        </div>
        <div class="table-responsive">
            <table class="table table-hover table-striped align-middle mb-0">
                <thead class="table-light">
                <tr>
                    <th>Ülesanne</th>
                    <th>Alustas</th>
                    <th>Lõpetas</th>
                    <th>Kestus</th>
                    <th>Staatus</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($progress as $row): ?>
                    <tr>
                        <td><?= htmlspecialchars($row['exerciseName']) ?></td>
                        <td>
                            <?php
                            if ($row['startedAt']) {
                                $dt = new DateTime($row['startedAt']);
                                echo '<strong>' . $dt->format('d.m.Y H:i:s') . '</strong>';
                            } else {
                                echo '<span class="text-muted">-</span>';
                            }
                            ?>
                        </td>
                        <td>
                            <?php
                            if ($row['status'] === 'completed' && $row['completedAt']) {
                                $dt = new DateTime($row['completedAt']);
                                echo '<strong>' . $dt->format('d.m.Y H:i:s') . '</strong>';
                            } else {
                                echo '<span class="text-muted">-</span>';
                            }
                            ?>
                        </td>
                        <td>
                            <?php
                            if ($row['startedAt'] && $row['completedAt'] && $row['status'] === 'completed') {
                                $start = strtotime($row['startedAt']);
                                $end = strtotime($row['completedAt']);
                                $duration = $end - $start;
                                $hours = floor($duration / 3600);
                                $minutes = floor(($duration % 3600) / 60);
                                $seconds = $duration % 60;
                                printf('<span class="badge bg-success">%02d:%02d:%02d</span>', $hours, $minutes, $seconds);
                            } elseif ($row['startedAt'] && !$row['completedAt'] && $row['status'] === 'started') {
                                // Live timer for started but not completed
                                $startIso = (new DateTime($row['startedAt']))->format(DateTime::ATOM);
                                echo '<span class="badge bg-warning text-dark live-timer" data-start="' . htmlspecialchars($startIso) . '">00:00:00</span>';
                            } else {
                                echo '<span class="text-muted">-</span>';
                            }
                            ?>
                        </td>
                        <td>
                            <?php
                            if ($row['status'] === 'completed') {
                                echo '<span class="badge bg-success">Sooritatud</span>';
                            } elseif ($row['status'] === 'started') {
                                echo '<span class="badge bg-warning text-dark">Alustatud</span>';
                            } elseif ($row['status'] === 'timed_out') {
                                echo '<span class="badge bg-danger">Aeg läbi</span>';
                            } elseif ($row['status'] === 'not_started' || !$row['status']) {
                                echo '<span class="badge bg-secondary">Pole alustatud</span>';
                            } else {
                                echo htmlspecialchars($row['status']);
                            }
                            ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <!-- Back button moved to top for better UX -->
    </div>
</div>
</body>
<script>
function updateLiveTimers() {
    var now = new Date();
    document.querySelectorAll('.live-timer').forEach(function(span) {
        var startIso = span.getAttribute('data-start');
        if (!startIso) return;
        var start = new Date(startIso);
        var diff = Math.floor((now - start) / 1000);
        if (diff < 0) diff = 0;
        var hours = Math.floor(diff / 3600);
        var minutes = Math.floor((diff % 3600) / 60);
        var seconds = diff % 60;
        span.textContent =
            (hours < 10 ? '0' : '') + hours + ':' +
            (minutes < 10 ? '0' : '') + minutes + ':' +
            (seconds < 10 ? '0' : '') + seconds;
    });
}
setInterval(updateLiveTimers, 1000);
window.addEventListener('DOMContentLoaded', updateLiveTimers);
</script>
</html>
