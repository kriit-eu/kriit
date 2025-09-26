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

// Fetch all exercises for this user using the computed status view
$progress = App\Db::getAll('
    SELECT 
        e.exerciseId, 
        e.exerciseName, 
        ue.startTime AS startedAt, 
        ue.endTime AS completedAt, 
        ue.status,
        NULL AS userTimeUpAt,
        NULL AS durationSeconds
    FROM exercises e
    LEFT JOIN userExercises ue ON ue.exerciseId = e.exerciseId AND ue.userId = ?
    ORDER BY e.exerciseId
', [$userId]);

?>
<!DOCTYPE html>
<html lang="et">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Kasutaja ülesannete detailvaade</title>
    <link rel="stylesheet" href="/assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="/assets/css/all.min.css">
    <link rel="stylesheet" href="/assets/css/main.css">
    <style>
            /* Responsive table: stack rows as cards on mobile (moved from main.css) */
            @media only screen and (max-width: 576px) {
                .table-responsive table,
                .table-responsive thead,
                .table-responsive tbody,
                .table-responsive th,
                .table-responsive td,
                .table-responsive tr {
                    display: block;
                    width: 100%;
                    box-sizing: border-box;
                }
                .table-responsive thead tr {
                    position: absolute;
                    top: -9999px;
                    left: -9999px;
                }
                .table-responsive tr {
                    margin-bottom: 1rem;
                    border: 1px solid #e3e6ea;
                    border-radius: 8px;
                    background: #fff;
                    box-shadow: 0 1px 4px rgba(0,0,0,0.04);
                    padding: 0.5rem 0.5rem;
                }
                .table-responsive td {
                    border: none;
                    border-bottom: 1px solid #f2f6fa;
                    position: relative;
                    padding-left: 45%;
                    min-height: 40px;
                    text-align: left;
                    background: none;
                    font-size: 1rem;
                }
                .table-responsive td:before {
                    position: absolute;
                    top: 8px;
                    left: 8px;
                    width: 40%;
                    padding-right: 10px;
                    white-space: nowrap;
                    font-weight: bold;
                    color: #495057;
                    content: attr(data-label);
                }
                .table-responsive td:last-child {
                    border-bottom: none;
                }
            }
        body {
            background: #f8f9fa;
        }

        .custom-container {
            max-width: 1200px;
            margin: 40px auto;
            padding: 0 1rem;
        }

        .detail-card {
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.07);
            border: 1px solid #e3e6ea;
            padding: 2.5rem 2rem 2rem 2rem;
        }

        @media (max-width: 576px) {
            .detail-card {
                padding: 1rem 0.5rem;
            }

            .custom-container {
                margin: 10px auto;
                padding: 0 0.2rem;
            }

            h2 {
                font-size: 1.1rem;
            }

            .btn-lg {
                font-size: 1rem;
                padding: 0.5rem 1rem;
            }

                .table th {
                    font-size: 1.05rem;
                    padding: 0.18rem 0.12rem;
                    text-align: center;
                    vertical-align: middle;
                }
                .table td {
                    font-size: 0.85rem;
                    padding: 0.18rem 0.12rem;
                    text-align: center;
                    vertical-align: middle;
                }
        }

        .table-responsive {
            width: 100%;
        }
        @media (max-width: 576px) {
            .table-responsive {
                overflow-x: unset;
            }
            .table {
                min-width: unset;
                width: 100% !important;
                table-layout: fixed;
                word-break: break-word;
            }
            .table td, .table th {
                white-space: normal !important;
                word-break: break-word;
                text-align: center !important;
                justify-content: center;
                align-items: center;
            }
            .table td[data-label] {
                word-break: break-word;
                overflow-wrap: break-word;
                hyphens: auto;
                max-width: 100%;
                white-space: normal !important;
                position: relative;
                padding-left: 0.2rem;
                text-align: center !important;
                padding-top: 0.7em;
            }
            .table td[data-label]::before {
                    content: attr(data-label);
                    display: block;
                    position: static;
                    font-weight: bold;
                    color: #495057;
                    margin-bottom: 0.08em;
                    font-size: 1.05em;
                    white-space: normal;
                    padding: 0;
                    text-align: center;
                    margin-left: auto;
                    margin-right: auto;
            }
        }

        .table {
            width: 100% !important;
            margin-bottom: 0;
        }

        .table thead th {
            border-bottom: 2px solid #dee2e6;
        }

        .badge.bg-success,
        .badge.bg-warning,
        .badge.bg-secondary,
        .badge.bg-danger {
            font-size: 1rem;
            padding: 0.5em 1em;
        }

        /* Fallback for zebra striping if Bootstrap is overridden */
        .table-striped>tbody>tr:nth-of-type(odd) {
            --bs-table-accent-bg: #f2f6fa;
            background-color: var(--bs-table-accent-bg) !important;
        }

        .btn-back {
            margin-top: 1.5rem;
        }

        /* Stack header and button on mobile */
        @media (max-width: 576px) {
            .d-flex.justify-content-between {
                flex-direction: column !important;
                align-items: stretch !important;
                gap: 0.5rem;
            }

            .btn {
                width: 100%;
                margin-bottom: 0.5rem;
            }

            h2 {
                margin-top: 0.5rem;
                text-align: center;
            }
        }
    </style>
</head>

<body>
    <div class="container custom-container">
        <div class="detail-card">
            <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap">
                <a href="/courses/1" class="btn btn-info btn-lg fw-bold shadow-sm rounded-pill px-4 py-2 me-3" style="letter-spacing:0.02em;"><i class="fas fa-arrow-left me-2"></i>Tagasi admini vaatesse</a>
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
                                    <td data-label="Ülesanne"><?= htmlspecialchars($row['exerciseName']) ?></td>
                                    <td data-label="Alustas">
                                    <?php
                                    if ($row['startedAt']) {
                                        $dt = new DateTime($row['startedAt']);
                                        echo '<strong>' . $dt->format('d.m.Y') . '</strong> ' . $dt->format('H:i:s');
                                    } else {
                                        echo '<span class="text-muted">-</span>';
                                    }
                                    ?>
                                </td>
                                    <td data-label="Lõpetas">
                                    <?php
                                    if ($row['status'] === 'completed' && $row['completedAt']) {
                                        $dt = new DateTime($row['completedAt']);
                                        echo '<strong>' . $dt->format('d.m.Y') . '</strong> ' . $dt->format('H:i:s');
                                    } else {
                                        echo '<span class="text-muted">-</span>';
                                    }
                                    ?>
                                </td>
                                    <td data-label="Kestus">
                                    <?php
                                    // If DB supplies durationSeconds use it; otherwise compute client-side from timestamps
                                    if ($row['status'] === 'completed') {
                                        if (!empty($row['durationSeconds'])) {
                                            $duration = $row['durationSeconds'];
                                            $hours = floor($duration / 3600);
                                            $minutes = floor(($duration % 3600) / 60);
                                            $seconds = $duration % 60;
                                            printf('<span class="badge bg-success">%02d:%02d:%02d</span>', $hours, $minutes, $seconds);
                                        } elseif (!empty($row['startedAt']) && !empty($row['completedAt'])) {
                                            // Render placeholder and data attributes; JS will compute duration
                                            $startIso = (new DateTime($row['startedAt']))->format(DateTime::ATOM);
                                            $endIso = (new DateTime($row['completedAt']))->format(DateTime::ATOM);
                                            echo '<span class="badge bg-success duration" data-start="' . htmlspecialchars($startIso) . '" data-end="' . htmlspecialchars($endIso) . '">-</span>';
                                        } else {
                                            echo '<span class="text-muted">-</span>';
                                        }
                                    } elseif ($row['status'] === 'started' && $row['startedAt']) {
                                        // Live timer for started exercises
                                        $startIso = (new DateTime($row['startedAt']))->format(DateTime::ATOM);
                                        $timeUpAtIso = $row['userTimeUpAt'] ? (new DateTime($row['userTimeUpAt']))->format(DateTime::ATOM) : null;
                                        echo '<span class="badge bg-warning text-dark live-timer" data-start="' . htmlspecialchars($startIso) . '" data-timeup="' . htmlspecialchars($timeUpAtIso ?: '') . '">00:00:00</span>';
                                    } elseif ($row['status'] === 'timed_out') {
                                        if (!empty($row['durationSeconds'])) {
                                            $duration = $row['durationSeconds'];
                                            $hours = floor($duration / 3600);
                                            $minutes = floor(($duration % 3600) / 60);
                                            $seconds = $duration % 60;
                                            printf('<span class="badge bg-danger">%02d:%02d:%02d</span>', $hours, $minutes, $seconds);
                                        } elseif (!empty($row['startedAt']) && !empty($row['userTimeUpAt'])) {
                                            // Show duration up to time limit; compute client-side
                                            $startIso = (new DateTime($row['startedAt']))->format(DateTime::ATOM);
                                            $timeUpAtIso = (new DateTime($row['userTimeUpAt']))->format(DateTime::ATOM);
                                            echo '<span class="badge bg-danger duration" data-start="' . htmlspecialchars($startIso) . '" data-end="' . htmlspecialchars($timeUpAtIso) . '">-</span>';
                                        } else {
                                            echo '<span class="text-muted">-</span>';
                                        }
                                    } else {
                                        echo '<span class="text-muted">-</span>';
                                    }
                                    ?>
                                </td>
                                    <td data-label="Staatus">
                                    <?php
                                    // Status is computed by the database view
                                    if ($row['status'] === 'completed') {
                                        echo '<span class="badge bg-success">Sooritatud</span>';
                                    } elseif ($row['status'] === 'started') {
                                        echo '<span class="badge bg-warning text-dark">Alustatud</span>';
                                    } elseif ($row['status'] === 'timed_out') {
                                        echo '<span class="badge bg-danger">Aeg läbi</span>';
                                    } elseif ($row['status'] === 'not_started') {
                                        echo '<span class="badge bg-secondary">Pole alustatud</span>';
                                    } else {
                                        echo '<span class="text-muted">-</span>';
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
            var timeUpIso = span.getAttribute('data-timeup');
            if (!startIso) return;
            
            var start = new Date(startIso);
            var timeUp = timeUpIso ? new Date(timeUpIso) : null;
            
            var diff;
            if (timeUp && now > timeUp) {
                // User time is up, show duration up to time limit
                diff = Math.floor((timeUp - start) / 1000);
                span.className = span.className.replace('bg-warning', 'bg-danger');
            } else if (timeUp) {
                // Cap duration at time limit
                var maxDiff = Math.floor((timeUp - start) / 1000);
                var actualDiff = Math.floor((now - start) / 1000);
                diff = Math.min(actualDiff, maxDiff);
            } else {
                // No time limit (admin user)
                diff = Math.floor((now - start) / 1000);
            }
            
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
    // Compute and render durations for completed/timed_out rows when DB doesn't provide durationSeconds
    function computeDurations() {
        document.querySelectorAll('.duration').forEach(function(el) {
            var startIso = el.getAttribute('data-start');
            var endIso = el.getAttribute('data-end');
            if (!startIso || !endIso) return;
            var start = new Date(startIso);
            var end = new Date(endIso);
            var diff = Math.floor((end - start) / 1000);
            if (diff < 0) diff = 0;
            var hours = Math.floor(diff / 3600);
            var minutes = Math.floor((diff % 3600) / 60);
            var seconds = diff % 60;
            el.textContent = (hours < 10 ? '0' : '') + hours + ':' + (minutes < 10 ? '0' : '') + minutes + ':' + (seconds < 10 ? '0' : '') + seconds;
        });
    }
    document.addEventListener('DOMContentLoaded', computeDurations);
</script>

</html>