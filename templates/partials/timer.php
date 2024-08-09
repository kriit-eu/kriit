<?php if ($auth->userIsAdmin !== 1): ?>
    <div class="timer"></div>
    <script>

        const startTimer = async (seconds) => {
            try {
                if (seconds > 0) {
                    let timerInterval = setInterval(function () {
                        let minutes = Math.floor(seconds / 60);
                        let secondsRemaining = seconds % 60;

                        if (minutes < 10) {
                            minutes = '0' + minutes;
                        }
                        if (secondsRemaining < 10) {
                            secondsRemaining = '0' + secondsRemaining;
                        }

                        document.querySelector('.timer').innerHTML = 'Aega jäänud <strong>' + minutes + ':' + secondsRemaining + '</strong>';

                        if (seconds <= 0) {
                            clearInterval(timerInterval);
                            window.location.href = 'exercises/timeup';
                        }
                        seconds--;
                    }, 1000);
                } else {
                    window.location.href = 'exercises/timeup';
                }
            } catch (error) {
                console.error("Error initializing timer:", error);
                document.querySelector('.timer').innerHTML = 'Error initializing timer';
            }
        }
        startTimer(<?= $timeLeft ?>);
    </script>
<?php endif; ?>
