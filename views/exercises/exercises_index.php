<div class="row">

    <h1>Ülesanded</h1>
    <div style="margin-bottom: 1em">
        <!-- Timer partial removed: not needed -->
    </div>
    <div class="table-responsive">
        <table class="table table-bordered">
            <tbody>
            <?php foreach ($exercises as $exercise): ?>
                <tr data-href="exercises/<?= $exercise['exerciseId'] ?>">
                    <td><?= $exercise['exerciseName'] ?></td>
                    <td>
                        <?php
                        $showButton = false;
                        $showTimer = false;
                        $timerText = '';
                        $timerData = '';
                        if (isset($exercise['remainingTime'])) {
                            if ($exercise['remainingTime'] > 0) {
                                $showButton = true;
                                $showTimer = true;
                                $timerText = gmdate("i:s", $exercise['remainingTime']);
                                $timerData = $exercise['remainingTime'];
                            } else {
                                $showButton = false;
                                $showTimer = true;
                                $timerText = 'Aegunud';
                                $timerData = 0;
                            }
                        } elseif ($exercise['status'] === 'completed') {
                            $showButton = false;
                            $showTimer = false;
                            $timerText = '';
                        } elseif ($exercise['status'] === 'timed_out') {
                            $showButton = false;
                            $showTimer = true;
                            $timerText = 'Aegunud';
                            $timerData = 0;
                        } else {
                            $showButton = true;
                            $showTimer = false;
                            $timerText = '';
                        }
                        ?>
                        <?php if ($exercise['status'] === 'completed'): ?>
                            Lahendatud
                        <?php else: ?>
                            <a href="exercises/<?= $exercise['exerciseId'] ?>" class="btn btn-sm btn-primary me-2" style="<?= $showButton ? '' : 'display:none;' ?>">Lahenda</a>
                            <span class="timer" data-time="<?= htmlspecialchars($timerData) ?>" style="<?= $showTimer ? '' : 'display:none;' ?> min-width:48px; display:inline-block; text-align:left;">
                                <?= htmlspecialchars($timerText) ?>&nbsp;
                            </span>
                        <?php endif; ?>
</style>
<style>
    .timer { min-width: 48px; display: inline-block; text-align: left; }
</style>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>

</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const timers = document.querySelectorAll('.timer');
        timers.forEach(timer => {
            let timeInSeconds = parseInt(timer.getAttribute('data-time'), 10);
            if (isNaN(timeInSeconds) || timeInSeconds <= 0) {
                // Do not start countdown for invalid or non-positive timers
                return;
            }
            const countdown = setInterval(function () {
                const minutes = Math.floor(timeInSeconds / 60);
                const seconds = timeInSeconds % 60;
                timer.textContent = `${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;

                if (--timeInSeconds < 0) {
                    clearInterval(countdown);
                    // Remove Lahenda button in the same cell
                    const parentTd = timer.closest('td');
                    if (parentTd) {
                        const lahendaBtn = parentTd.querySelector('a.btn-primary');
                        if (lahendaBtn) {
                            lahendaBtn.style.display = 'none';
                        }
                    }
                    timer.textContent = "Aegunud";
                }
            }, 1000);
        });
    });

    // Force a page reload when navigating back to the page to ensure timers are up-to-date
    window.addEventListener('pageshow', function(event) {
        if (event.persisted) {
            window.location.reload();
        }
    });
</script>
