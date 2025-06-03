<!DOCTYPE html>
<html lang="en">
<head>
    <base href="<?= BASE_URL ?>">
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="">
    <meta name="author" content="">

    <title><?= PROJECT_NAME ?></title>

    <!-- Site core CSS -->
    <link href="assets/css/main.css?<?= COMMIT_HASH ?>" rel="stylesheet">

    <!-- Include CodeMirror CSS (Local) -->
    <link rel="stylesheet" href="node_modules/codemirror/lib/codemirror.css?<?= COMMIT_HASH ?>">
    <link rel="stylesheet" href="node_modules/codemirror/theme/monokai.css?<?= COMMIT_HASH ?>">
    <link rel="stylesheet" href="node_modules/@fortawesome/fontawesome-free/css/all.min.css?<?= COMMIT_HASH ?>">

    <!-- Include CodeMirror JS (Local) -->
    <script src="node_modules/codemirror/lib/codemirror.js?<?= COMMIT_HASH ?>"></script>
    <!-- CodeMirror mode for JSON -->
    <script src="node_modules/codemirror/mode/javascript/javascript.js?<?= COMMIT_HASH ?>"></script>


    <!-- jQuery -->
    <script src="node_modules/jquery/dist/jquery.min.js?<?= COMMIT_HASH ?>"></script>

    <!-- OFFLINE MODE: Configuration -->
    <script>
        const OFFLINE_MODE = <?= defined('OFFLINE_MODE') && OFFLINE_MODE ? 'true' : 'false' ?>;
    </script>

    <!-- Bootstrap core CSS -->
    <script src="node_modules/@popperjs/core/dist/umd/popper.min.js?<?= COMMIT_HASH ?>"></script>
    <script src="node_modules/bootstrap/dist/js/bootstrap.min.js?<?= COMMIT_HASH ?>"></script>
    <link href="node_modules/bootstrap/dist/css/bootstrap.min.css?<?= COMMIT_HASH ?>" rel="stylesheet">
    <link rel="stylesheet" href="node_modules/bootstrap-icons/font/bootstrap-icons.css?<?= COMMIT_HASH ?>">

    <!-- Fav and touch icons -->
    <link rel="apple-touch-icon" sizes="180x180" href="apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="favicon-16x16.png">
    <link rel="manifest" href="site.webmanifest">
</head>
