<div class="custom-container" style="max-width:1300px;margin:0 auto;">
    <div class="row">
        <div class="col">
        <h1><?= htmlspecialchars($this->course['name']) ?></h1>
        <p><?= htmlspecialchars($this->course['description'] ?? '') ?></p>
    </div>
</div>

<ul class="nav nav-tabs mt-3" id="courseTabs">
    <li class="nav-item">
        <a id="tab-exercises" class="nav-link <?= ($this->tab === 'exercises') ? 'active' : '' ?>" href="<?= BASE_URL ?>courses/<?= $this->course['id'] ?>">Ülesanded</a>
    </li>
    <li class="nav-item">
        <a id="tab-ranking" class="nav-link <?= ($this->tab === 'ranking') ? 'active' : '' ?>" href="<?= BASE_URL ?>courses/<?= $this->course['id'] ?>?tab=ranking">Edetabel</a>
    </li>
</ul>

<div class="mt-3">
    <div id="panel-exercises" style="display: <?= ($this->tab === 'exercises') ? 'block' : 'none' ?>;">
        <?php
        // For the default Sisseastumine course (id=1) include the course-specific admin editor
        if (!empty($this->course) && intval($this->course['id']) === 1) {
            $exercises = $this->exercises;
            include __DIR__ . '/courses_exercises_teacher.php';
        } else {
            if (empty($this->exercises)) {
                echo '<div class="alert alert-info">Selles kursuses pole ülesandeid.</div>';
            } else {
                echo '<div class="exercises-container">';
                foreach ($this->exercises as $exercise) {
                    $title = htmlspecialchars($exercise['exerciseName'] ?? $exercise['exercise_name'] ?? 'Ülesanne');
                    $instr = htmlspecialchars($exercise['exerciseInstructions'] ?? '');
                    $url = BASE_URL . 'exercises/' . ($exercise['exerciseId'] ?? '');
                    echo "<div class=\"card mb-2\"><div class=\"card-body\"><h5 class=\"card-title\">{$title}</h5><p class=\"card-text\">{$instr}</p><a href=\"{$url}\" class=\"btn btn-primary\">Vaata</a></div></div>";
                }
                echo '</div>';
            }
        }
        ?>
    </div>

    <div id="panel-ranking" style="display: <?= ($this->tab === 'ranking') ? 'block' : 'none' ?>;">
        <?php // Use the canonical admin ranking view so all columns (Ajakulu, Aega jäänud, Detailvaade, etc.) are present.
        // Ensure the view has a local `$users` variable like the admin view expects.
        $users = $this->users ?? [];
            // Include the course-scoped ranking partial (copied from admin ranking)
            include __DIR__ . '/courses_ranking.php';
        ?>
    </div>
    </div>
</div>

<script>
    // Tab switching: keep user on same page and update URL without full reload
    (function () {
        const tabExercises = document.getElementById('tab-exercises');
        const tabRanking = document.getElementById('tab-ranking');
        const panelExercises = document.getElementById('panel-exercises');
        const panelRanking = document.getElementById('panel-ranking');

        function activateTab(tab) {
            if (tab === 'exercises') {
                tabExercises.classList.add('active');
                tabRanking.classList.remove('active');
                panelExercises.style.display = 'block';
                panelRanking.style.display = 'none';
                history.replaceState(null, '', tabExercises.getAttribute('href'));
            } else {
                tabRanking.classList.add('active');
                tabExercises.classList.remove('active');
                panelRanking.style.display = 'block';
                panelExercises.style.display = 'none';
                history.replaceState(null, '', tabRanking.getAttribute('href'));
            }
        }

        tabExercises.addEventListener('click', function (e) {
            e.preventDefault();
            activateTab('exercises');
        });

        tabRanking.addEventListener('click', function (e) {
            e.preventDefault();
            // When ranking tab is clicked, fetch the ranking content via redirect URL that the controller created
            // For now we rely on server-side prepared $this->users; just toggle UI and update URL
            activateTab('ranking');
        });
    })();
</script>
