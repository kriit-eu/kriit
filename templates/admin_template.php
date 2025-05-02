<?php require 'templates/partials/master_header.php'; ?>
<body>
<script src="assets/js/main.js?<?= COMMIT_HASH ?>"></script>
<div class="text-end me-6 mt-2">
    <button type="button" class="btn btn-secondary logout-btn"
            style="position: fixed; top: 10px; right: 10px;"
            onclick="window.location.href = 'logout'">Logout</button>
</div>

<div class="container" id="container">
    <nav class="nav nav-pills flex-column flex-sm-row" style="margin-bottom: 4em">
        <a class="flex-sm-fill text-sm-center nav-link <?= $this->setActive('subjects/index') ?>"  aria-current="subjects" href="subjects">Ained</a>
        <a class="flex-sm-fill text-sm-center nav-link <?= $this->setActive('admin/groups') ?>" href="admin/groups">Grupid</a>
        <a class="flex-sm-fill text-sm-center nav-link <?= $this->setActive('admin/users') ?>" href="admin/users">Kasutajad</a>
        <a class="flex-sm-fill text-sm-center nav-link <?= $this->setActive('admin/logs') ?>" href="admin/logs">Logi</a>
        <a class="flex-sm-fill text-sm-center nav-link <?= $this->setActive('admin/exercises') ?>"
           href="admin/exercises">Ülesanded</a>
        <a class="flex-sm-fill text-sm-center nav-link <?= $this->setActive('admin/ranking') ?>" href="admin/ranking">Ranking</a>
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

<?php
// Display SQL debug information if SQL_DEBUG is enabled and user is admin
if (defined('SQL_DEBUG') && SQL_DEBUG && isset($auth) && $auth->userIsAdmin) {
    \App\Db::displayDebugInfo();
}
?>

</body>
</html>

<?php require 'system/error_translations.php' ?>
