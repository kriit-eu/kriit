<?php require 'templates/partials/master_header.php'; ?>
<body>
<script src="assets/js/main.js?<?= COMMIT_HASH ?>"></script>

<div class="container" id="container">
    <nav class="nav nav-pills flex-column flex-sm-row" style="margin-bottom: 4em">
        <a class="flex-sm-fill text-sm-center nav-link <?= $this->setActive('applicants/index') ?>"
           aria-current="applicants" href="applicants">Kandidaadid</a>
        <a class="flex-sm-fill text-sm-center nav-link <?= $this->setActive('admin/exercises') ?>"
           href="admin/exercises">Ülesanded</a>
        <a class="flex-sm-fill text-sm-center nav-link <?= $this->setActive('admin/groups') ?>" href="admin/groups">Grupid</a>
        <a class="flex-sm-fill text-sm-center nav-link <?= $this->setActive('admin/logs') ?>" href="admin/logs">Logi</a>
        <a class="flex-sm-fill text-sm-center nav-link <?= $this->setActive('admin/ranking') ?>" href="admin/ranking">Ranking</a>
        <a class="flex-sm-fill text-sm-center nav-link <?= $this->setActive('admin/users') ?>" href="admin/users">Kasutajad</a>
    </nav>
    <?php
    /** @var string $controller set in Application::__construct() */
    /** @var string $action set in Application::__construct() */
    if (!file_exists("views/$controller/{$controller}_$action.php")) {
        error_out('The view <i>views/' . $controller . '/' . $controller . '_' . $action . '.php</i> does not exist. Create that file.');
    }
    @require "views/$controller/{$controller}_$action.php";
    ?>
</div>

<?php require 'templates/partials/error_modal.php'; ?>


</body>
</html>

<?php require 'system/error_translations.php' ?>
