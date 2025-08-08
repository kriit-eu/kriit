
<?php
// DEBUG: Dump $exercises array for inspection
if (isset($exercises) && is_array($exercises)) {
    echo "<!-- exercises: ".str_replace('--','==',print_r($exercises, true))." -->\n";
}
?>
<?php if (isset($this->timeLeft) && $this->timeLeft > 0): ?>
<div class="alert alert-info" style="display:flex;align-items:center;gap:1em;max-width:400px;margin:1em auto 0 auto;">
    <span style="font-weight:bold;">Aega jäänud:</span>
    <span id="session-timer" data-time="<?= (int)$this->timeLeft ?>" style="min-width:60px;display:inline-block;"> <?= gmdate('i:s', $this->timeLeft) ?> </span>
</div>
<?php endif; ?>

<div class="row">

    <h1>Ülesanded</h1>
    <div style="margin-bottom: 1em">
        <!-- Timer partial removed: not needed -->
    </div>

    <style>
        .timer.overdue { color: #c00; font-weight: bold; }
    </style>
    <div class="table-responsive">
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Ülesanne</th>
                    <th>Aeg</th>
                    <th>Staatus</th>
                </tr>
            </thead>
            <tbody>
            <?php
            if (!function_exists('fuzzy_time_et')) {
                function fuzzy_time_et($seconds) {
                    if ($seconds < 60) return $seconds . ' sek';
                    $minutes = floor($seconds / 60);
                    return $minutes . ' min';
                }
            }
            ?>
            <?php foreach ($exercises as $exercise): ?>
                <tr data-href="exercises/<?= $exercise['exerciseId'] ?>">
                    <td><?= $exercise['exerciseName'] ?></td>
                    <td>
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
                    <td>
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

    document.addEventListener('DOMContentLoaded', function() {
        // Per-exercise timers (count up only for started)
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
            // Set overdue class immediately if needed
            updateTimer();
            setInterval(updateTimer, 1000);
        });

        // Session timer
        const sessionTimer = document.getElementById('session-timer');
        if (sessionTimer) {
            let sessionTime = parseInt(sessionTimer.getAttribute('data-time'), 10);
            const sessionInterval = setInterval(function () {
                if (sessionTime <= 0) {
                    sessionTimer.textContent = '00:00';
                    clearInterval(sessionInterval);
                    // Redirect to timeup page when session ends
                    window.location.href = 'exercises/timeup';
                    return;
                }
                const minutes = Math.floor(sessionTime / 60);
                const seconds = sessionTime % 60;
                sessionTimer.textContent = `${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;
                sessionTime--;
            }, 1000);
        }
    });

    // Force a page reload when navigating back to the page to ensure timers are up-to-date
    window.addEventListener('pageshow', function(event) {
        if (event.persisted) {
            window.location.reload();
        }
    });
</script>
