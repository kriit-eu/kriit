<div class="custom-container" style="max-width:1300px;margin:0 auto;">
    <div class="row">
        <div class="col">
            <h1><?= htmlspecialchars($this->course['name']) ?></h1>
            <?php if (!empty($this->course['description'])): ?>
                <p class="text-muted"><?= nl2br(htmlspecialchars($this->course['description'])) ?></p>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php if (!empty($this->showExerciseSavedToast)): ?>
    <div class="position-fixed top-0 end-0 p-3" style="z-index:1080;">
        <div id="exerciseSavedToast" class="toast text-bg-success border-0 align-items-center" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="d-flex">
                <div class="toast-body">Harjutus salvestatud</div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Sulge"></button>
            </div>
        </div>
    </div>
<?php endif; ?>

<ul class="nav nav-tabs mt-3" id="courseTabs" role="tablist">
    <li class="nav-item" role="presentation">
        <a class="nav-link <?= $this->tab === 'overview' ? 'active' : '' ?>" href="<?= BASE_URL ?>courses/<?= $this->courseId ?>?tab=overview" data-tab="overview" role="tab">Ülevaade</a>
    </li>
    <li class="nav-item" role="presentation">
        <a class="nav-link <?= $this->tab === 'exercises' ? 'active' : '' ?>" href="<?= BASE_URL ?>courses/<?= $this->courseId ?>?tab=exercises" data-tab="exercises" role="tab">Harjutused</a>
    </li>
    <li class="nav-item" role="presentation">
        <a class="nav-link <?= $this->tab === 'ranking' ? 'active' : '' ?>" href="<?= BASE_URL ?>courses/<?= $this->courseId ?>?tab=ranking" data-tab="ranking" role="tab">Edetabel</a>
    </li>
</ul>

<div class="tab-content mt-3">
    <div id="panel-overview" class="tab-pane fade <?= $this->tab === 'overview' ? 'show active' : '' ?>" role="tabpanel">
        <div class="row g-3">
            <?php $total = $this->totalExercisesCount ?? count($this->exercises ?? []); ?>
            <div class="col-md-4">
                <div class="card shadow-sm h-100">
                    <div class="card-body">
                        <h5 class="card-title">Kursuse olek</h5>
                        <?php if (!empty($this->courseIsActive)): ?>
                            <span class="badge bg-success">Aktiivne</span>
                        <?php else: ?>
                            <span class="badge bg-secondary">Mitteaktiivne</span>
                        <?php endif; ?>
                        <p class="mt-3 mb-0 small text-muted">Kursuse seisund määrab, kas harjutusi saab alustada või jätkata.</p>
                    </div>
                </div>
            </div>
            <!-- Edenemine card removed for teacher/admin view -->
            <div class="col-md-4">
                <div class="card shadow-sm h-100">
                    <div class="card-body">
                        <h5 class="card-title">Harjutuste arv</h5>
                        <p class="display-6 mb-0"><?= htmlspecialchars($total) ?></p>
                        <p class="mt-2 mb-0 small text-muted">Kokku harjutusi selles kursuses.</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="card shadow-sm mt-4">
            <div class="card-header fw-semibold">Seotud ülesanded</div>
            <?php if (!empty($this->linkedAssignments)): ?>
                <ul class="list-group list-group-flush">
                    <?php foreach ($this->linkedAssignments as $assignment): ?>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <a href="<?= BASE_URL ?>assignments/<?= $assignment['assignmentId'] ?>" class="text-decoration-none">
                                <?= htmlspecialchars($assignment['assignmentName']) ?>
                            </a>
                            <?php if (!empty($assignment['assignmentDueAt'])): ?>
                                <?php $due = new DateTime($assignment['assignmentDueAt']); ?>
                                <span class="badge bg-light text-dark">Tähtaeg: <?= $due->format('d.m.Y') ?></span>
                            <?php endif; ?>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php else: ?>
                <div class="card-body">
                    <p class="mb-0 text-muted">Selle kursusega pole ülesandeid seotud.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <div id="panel-exercises" class="tab-pane fade <?= $this->tab === 'exercises' ? 'show active' : '' ?>" role="tabpanel">
        <?php
            $exercises = $this->exercises;
            include __DIR__ . '/courses_exercises_teacher.php';
        ?>
    </div>

    <div id="panel-ranking" class="tab-pane fade <?= $this->tab === 'ranking' ? 'show active' : '' ?>" role="tabpanel">
        <?php
            $users = $this->users ?? [];
            include __DIR__ . '/courses_ranking.php';
        ?>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        if (typeof bootstrap !== 'undefined') {
            const toastEl = document.getElementById('exerciseSavedToast');
            if (toastEl) {
                new bootstrap.Toast(toastEl, {delay: 3000}).show();
            }
        }

        const tabLinks = document.querySelectorAll('#courseTabs a[data-tab]');
        const panels = {
            overview: document.getElementById('panel-overview'),
            exercises: document.getElementById('panel-exercises'),
            ranking: document.getElementById('panel-ranking')
        };

        tabLinks.forEach(link => {
            link.addEventListener('click', function (event) {
                event.preventDefault();
                const target = this.getAttribute('data-tab');
                if (!panels[target]) {
                    return;
                }

                tabLinks.forEach(l => l.classList.remove('active'));
                Object.values(panels).forEach(panel => panel.classList.remove('show', 'active'));

                this.classList.add('active');
                panels[target].classList.add('show', 'active');

                const url = new URL(this.href, window.location.origin);
                history.replaceState(null, '', url.pathname + url.search);
            });
        });
    });
</script>
