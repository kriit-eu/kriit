<?php require 'templates/partials/master_header.php'; ?>
<body>
<script src="assets/js/main.js?<?= COMMIT_HASH ?>"></script>

<div class="container" id="container">
    <?php
    if ($auth->userIsAdmin) {
        require 'templates/partials/admin_and_logout_buttons.php';
    }
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
