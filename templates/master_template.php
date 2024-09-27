<?php require 'templates/partials/master_header.php'; ?>
<body>
<script src="assets/js/main.js?<?= COMMIT_HASH ?>"></script>

<div class="container" id="container">
    <nav class="nav nav-pills flex-column flex-sm-row" style="margin-bottom: 4em">
        <a class="flex-sm-fill text-sm-center nav-link <?= $this->setActive('subjects/index') ?>"
           aria-current="subjects" href="subjects">Ained</a>
    </nav>
    <?php
    require 'templates/partials/admin_and_logout_buttons.php';

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
