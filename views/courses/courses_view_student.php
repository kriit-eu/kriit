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
</ul>

<div class="tab-content mt-3">
    <div id="panel-overview" class="tab-pane fade <?= $this->tab === 'overview' ? 'show active' : '' ?>" role="tabpanel">
        <div class="row g-3">
            <!-- Kursuse olek card removed for student view -->
            <div class="col-md-4">
                <div class="card shadow-sm h-100">
                    <div class="card-body">
                        <h5 class="card-title">Edenemine</h5>
                        <?php
                            $completed = $this->completedExercisesCount ?? 0;
                            $total = $this->totalExercisesCount ?? count($this->exercises ?? []);
                            $progressPercent = ($total > 0) ? round(($completed / $total) * 100) : 0;
                            $progressText = $this->progressLabel ?? sprintf('Edenemine: %d/%d', $completed, $total);
                        ?>
                        <p class="fs-5 fw-semibold mb-2"><?= htmlspecialchars($progressText) ?></p>
                        <div class="progress" style="height:8px;">
                            <div class="progress-bar" role="progressbar" style="width: <?= $progressPercent ?>%;" aria-valuenow="<?= $progressPercent ?>" aria-valuemin="0" aria-valuemax="100"></div>
                        </div>
                        <p class="mt-3 mb-0 small text-muted">Sinu edenemine kursuse harjutuste lahendamisel.</p>
                    </div>
                </div>
            </div>
            <!-- Harjutuste arv card removed for student view -->
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

    <div id="panel-exercises" class="tab-pane fade <?= $this->tab === 'exercises' ? 'show active' : '' ?> pb-5" role="tabpanel">
        <?php if (empty($this->courseIsActive)): ?>
            <div class="alert alert-warning">Kursus ei ole aktiivne.</div>
        <?php endif; ?>

        <?php if (empty($this->exercises)): ?>
            <div class="alert alert-info">Selles kursuses ei ole veel harjutusi.</div>
        <?php else: ?>
            <div class="row row-cols-1 row-cols-md-2 g-3">
                <?php foreach ($this->exercises as $exercise): ?>
                    <?php
                        $statusLabel = $exercise['statusLabel'] ?? 'Tegemata';
                        $badgeClass = 'bg-secondary';
                        if ($statusLabel === 'Tehtud') {
                            $badgeClass = 'bg-success';
                        } elseif ($statusLabel === 'Pooleli') {
                            $badgeClass = 'bg-warning text-dark';
                        }
                        $instructions = trim(strip_tags($exercise['exerciseInstructions'] ?? ''));
                        if (mb_strlen($instructions) > 200) {
                            $instructions = mb_substr($instructions, 0, 197) . '…';
                        }
                        $actionDisabled = !empty($exercise['actionDisabled']);
                    ?>
                    <div class="col">
                        <div class="card h-100 shadow-sm">
                            <div class="card-body d-flex flex-column">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <h5 class="card-title mb-0"><?= htmlspecialchars($exercise['exerciseName'] ?? 'Harjutus') ?></h5>
                                    <span class="badge <?= $badgeClass ?>"><?= $statusLabel ?></span>
                                </div>
                                <?php if (!empty($instructions)): ?>
                                    <p class="text-muted small mb-3"><?= htmlspecialchars($instructions) ?></p>
                                <?php endif; ?>
                                <div class="mt-auto d-flex justify-content-between align-items-center">
                                    <span class="text-muted small">
                                        <?php if (($exercise['userStatus'] ?? '') === 'completed' && !empty($exercise['endTime'])): ?>
                                            <?php $completedAt = new DateTime($exercise['endTime']); ?>
                                            Lõpetatud <?= $completedAt->format('d.m.Y') ?>
                                        <?php elseif (($exercise['userStatus'] ?? '') === 'started' && !empty($exercise['startTime'])): ?>
                                            <?php $startedAt = new DateTime($exercise['startTime']); ?>
                                            Alustatud <?= $startedAt->format('d.m.Y') ?>
                                        <?php endif; ?>
                                    </span>
                                    <?php
                                        $btnClasses = trim('btn btn-sm ' . ($exercise['actionClass'] ?? 'btn-primary'));
                                        $disabledAttr = $actionDisabled ? 'disabled aria-disabled="true" tabindex="-1"' : '';
                                    ?>
                                    <?php if (($exercise['userStatus'] ?? '') !== 'completed'): ?>
                                        <a href="<?= BASE_URL ?>exercises/<?= $exercise['exerciseId'] ?>?courseId=<?= $this->courseId ?>"
                                           class="<?= $btnClasses ?>"
                                           <?= $disabledAttr ?>><?= htmlspecialchars($exercise['actionLabel'] ?? 'Ava') ?></a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- Ranking tab removed for students -->
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
        const panels = {};
        ['overview','exercises','ranking'].forEach(key => {
            const el = document.getElementById('panel-' + key);
            if (el) panels[key] = el;
        });

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
