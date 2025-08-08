<?php
// DEBUG: Dump $exercises array for inspection
if (isset($exercises) && is_array($exercises)) {
    echo "<!-- exercises: " . str_replace('--', '==', print_r($exercises, true)) . " -->\n";
}
?>
<h1 style="text-align:center;margin:0;padding:0.7em 0;">Ülesanded</h1>
<?php if (isset($this->timeLeft) && $this->timeLeft > 0): ?>
    <div class="sticky-timer" style="position:fixed;top:0.5em;right:1.5em;z-index:1000;display:flex;align-items:center;gap:1em;max-width:400px;background:#e9f7fe;border-radius:8px;padding:0.5em 1em;box-shadow:0 2px 8px rgba(0,0,0,0.04);">
        <span style="font-weight:bold;">Aega jäänud:</span>
        <span id="session-timer" data-time="<?= (int)$this->timeLeft ?>" style="min-width:60px;display:inline-block;"> <?= gmdate('i:s', $this->timeLeft) ?> </span>
    </div>
<?php endif; ?>

<div class="row">

    <style>
        /* Remove top padding/margin from Bootstrap container */
        .container {
            padding-top: 0 !important;
            margin-top: 0 !important;
        }

        .timer.overdue {
            color: #c00;
            font-weight: bold;
        }

        /* Fit table layout overrides */
        .fit-table-wrapper {
            width: 100%;
            overflow-x: auto;
            margin-bottom: 1em;
        }

        .fit-table {
            table-layout: auto !important;
            width: auto !important;
            min-width: 0 !important;
            border-collapse: collapse;
        }

        .fit-table th,
        .fit-table td {
            padding: 0.2em 0.5em;
            vertical-align: middle;
            word-break: break-word;
            white-space: nowrap;
            width: 1px;
        }

        .fit-table th {
            background: #f8f9fa;
            font-weight: bold;
        }

        /* Responsive styles for phones */
        @media (max-width: 600px) {
            h1 {
                font-size: 1.3em;
                padding: 0.5em 0.2em;
            }
            .sticky-timer {
                position: fixed;
                top: 0;
                left: 0;
                right: 0;
                width: 100vw;
                max-width: 100vw;
                border-radius: 0;
                padding: 0.5em 0.5em;
                font-size: 1em;
                box-shadow: 0 2px 8px rgba(0,0,0,0.04);
                justify-content: flex-start;
                gap: 0.5em;
            }
            .fit-table-wrapper {
                margin-left: -0.5em;
                margin-right: -0.5em;
                padding: 0;
            }
            .fit-table {
                font-size: 0.95em;
                min-width: 320px;
            }
            .fit-table th,
            .fit-table td {
                padding: 0.15em 0.3em;
                white-space: normal;
                width: auto;
                font-size: 0.95em;
            }
            .col-name, .col-time, .col-status {
                min-width: 80px;
                max-width: 160px;
                word-break: break-word;
            }
        }
    </style>
    <div class="fit-table-wrapper" style="display: flex; justify-content: center;">
        <table class="table fit-table table-bordered">
            <thead>
                <tr>
                    <th class="col-name">Ülesanne</th>
                    <th class="col-time">Aeg</th>
                    <th class="col-status">Staatus</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if (!function_exists('fuzzy_time_et')) {
                    function fuzzy_time_et($seconds)
                    {
                        if ($seconds < 60) return $seconds . ' sek';
                        $minutes = floor($seconds / 60);
                        return $minutes . ' min';
                    }
                }
                ?>
                <?php foreach ($exercises as $exercise): ?>
                    <tr data-href="exercises/<?= $exercise['exerciseId'] ?>">
                        <td class="col-name"><?= $exercise['exerciseName'] ?></td>
                        <td class="col-time">
                            <?php
                            // Only show completed time for completed exercises
                            $timerText = '';
                            if (
                                $exercise['status'] === 'completed'
                                && !empty($exercise['startTime'])
                                && !empty($exercise['endTime'])
                                && ($startTimestamp = strtotime($exercise['startTime'])) !== false
                                && ($endTimestamp = strtotime($exercise['endTime'])) !== false
                            ) {
                                $elapsed = $endTimestamp - $startTimestamp;
                                $timerText = fuzzy_time_et($elapsed);
                            }
                            ?>
                            <?php if ($exercise['status'] === 'completed'): ?>
                                <span class="timer" data-time="static" style="min-width:48px; display:inline-block; text-align:left; font-weight:bold;">
                                    <?= htmlspecialchars($timerText) ?>&nbsp;
                                </span>
                            <?php else: ?>
                                <!-- No timer for not started or running -->
                            <?php endif; ?>
                        </td>
                        <td class="col-status">
                            <?php if ($exercise['status'] === 'completed'): ?>
                                Lahendatud
                            <?php elseif ($exercise['status'] === 'started'): ?>
                                <a href="exercises/<?= $exercise['exerciseId'] ?>" class="btn btn-sm btn-primary me-2">Lahenda</a>
                            <?php else: ?>
                                <a href="exercises/<?= $exercise['exerciseId'] ?>" class="btn btn-sm btn-primary me-2">Lahenda</a>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

</div>

<script>
    function fuzzyTimeEt(seconds) {
        if (seconds < 60) return seconds + ' sek';
        const minutes = Math.floor(seconds / 60);
        return minutes + ' min';
    }

    function fuzzyTimeEtWithEllipsis(seconds) {
        return fuzzyTimeEt(seconds) + '…';
    }

    // Robust session timer: always calculate remaining time from session end timestamp
    let sessionTimerInterval = null;

    function updateSessionTimer(forceCheck = false) {
        const sessionTimer = document.getElementById('session-timer');
        if (!sessionTimer) return;
        const initialTime = parseInt(sessionTimer.getAttribute('data-time'), 10);
        // Store session end timestamp on first run
        if (!sessionTimer._sessionEnd) {
            sessionTimer._sessionEnd = Math.floor(Date.now() / 1000) + initialTime;
        }

        function tick() {
            const now = Math.floor(Date.now() / 1000);
            let remaining = sessionTimer._sessionEnd - now;
            if (remaining <= 0) {
                sessionTimer.textContent = '00:00';
                if (sessionTimerInterval) clearInterval(sessionTimerInterval);
                window.location.href = 'exercises/timeup';
                return;
            }
            const minutes = Math.floor(remaining / 60);
            const seconds = remaining % 60;
            sessionTimer.textContent = `${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;
        }
        tick();
        if (sessionTimerInterval) clearInterval(sessionTimerInterval);
        sessionTimerInterval = setInterval(tick, 1000);
        // On forceCheck, immediately redirect if expired
        if (forceCheck) {
            const now = Math.floor(Date.now() / 1000);
            let remaining = sessionTimer._sessionEnd - now;
            if (remaining <= 0) {
                window.location.href = 'exercises/timeup';
            }
        }
    }

    document.addEventListener('DOMContentLoaded', function() {
        // ...existing code for per-exercise timers...
        document.querySelectorAll('.timer[data-start]').forEach(timer => {
            const startTimestamp = parseInt(timer.getAttribute('data-start'), 10);
            if (isNaN(startTimestamp) || startTimestamp <= 0) {
                timer.textContent = 'alla minuti…';
                timer.classList.remove('overdue');
                return;
            }

            function updateTimer() {
                const now = Math.floor(Date.now() / 1000);
                const elapsed = now - startTimestamp;
                timer.textContent = fuzzyTimeEtWithEllipsis(elapsed);
                if (elapsed >= 300) {
                    timer.classList.add('overdue');
                } else {
                    timer.classList.remove('overdue');
                }
            }
            updateTimer();
            setInterval(updateTimer, 1000);
        });
        updateSessionTimer(true);
    });

    // Update session timer when tab becomes visible again
    document.addEventListener('visibilitychange', function() {
        if (!document.hidden) {
            updateSessionTimer(true);
        }
    });

    // Force a page reload when navigating back to the page to ensure timers are up-to-date
    window.addEventListener('pageshow', function(event) {
        if (event.persisted) {
            window.location.reload();
        }
    });
</script>