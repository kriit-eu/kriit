<!DOCTYPE html>
<html lang="en">
<head>
    <base href="<?= BASE_URL ?>">
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="">
    <meta name="author" content="">
    <link rel="shortcut icon" href="assets/ico/favicon.png">

    <title><?= PROJECT_NAME ?></title>

    <!-- Site core CSS -->
    <link href="assets/css/main.css?<?= COMMIT_HASH ?>" rel="stylesheet">

    <!-- jQuery -->
    <script src="node_modules/jquery/dist/jquery.min.js?<?= COMMIT_HASH ?>"></script>

    <!-- Bootstrap core CSS -->
    <script src="node_modules/bootstrap/dist/js/bootstrap.min.js?<?= COMMIT_HASH ?>"></script>
    <link href="node_modules/bootstrap/dist/css/bootstrap.min.css?<?= COMMIT_HASH ?>" rel="stylesheet">
    <link rel="stylesheet" href="node_modules/bootstrap-icons/font/bootstrap-icons.css?<?= COMMIT_HASH ?>">

    <!-- Fav and touch icons -->
    <link rel="apple-touch-icon" sizes="180x180" href="apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="favicon-16x16.png">
    <link rel="manifest" href="site.webmanifest">
</head>

<body>
<script src="assets/js/main.js?<?= COMMIT_HASH ?>"></script>

<div class="container" id="container">
    <nav class="nav nav-pills flex-column flex-sm-row" style="margin-bottom: 4em">
        <a class="flex-sm-fill text-sm-center nav-link active" aria-current="page" href="#">Kandidaadid</a>
        <a class="flex-sm-fill text-sm-center nav-link" href="admin/exercises">Ülesanded</a>
        <a class="flex-sm-fill text-sm-center nav-link" href="admin/exercises">Ranking</a>
        <a class="flex-sm-fill text-sm-center nav-link" href="admin/admins">Administraatorid</a>
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
