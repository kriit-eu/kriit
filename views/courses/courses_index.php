<div class="row">
    <div class="col">
        <h1>Kursused</h1>
    </div>
    <div class="col-auto">
        <!-- Add course button could go here in future -->
    </div>
</div>

<div class="list-group mt-3">
    <?php foreach ($this->courses as $course): ?>
        <a href="courses/<?= $course['id'] ?>" class="list-group-item list-group-item-action">
            <div class="d-flex w-100 justify-content-between">
                <h5 class="mb-1"><?= htmlspecialchars($course['name']) ?></h5>
                <small><?= htmlspecialchars($course['visibility'] ?? '') ?></small>
            </div>
            <p class="mb-1"><?= htmlspecialchars($course['description'] ?? '') ?></p>
        </a>
    <?php endforeach; ?>
</div>
