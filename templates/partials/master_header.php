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

    <!-- Include CodeMirror CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.5/codemirror.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.12/theme/monokai.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">

    <!-- Include CodeMirror JS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.5/codemirror.min.js"></script>
    <!-- CodeMirror mode for JSON -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.5/mode/javascript/javascript.min.js"></script>


    <!-- jQuery -->
    <script src="node_modules/jquery/dist/jquery.min.js?<?= COMMIT_HASH ?>"></script>

    <!-- Bootstrap core CSS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/2.11.8/umd/popper.min.js"></script>
    <script src="node_modules/bootstrap/dist/js/bootstrap.min.js?<?= COMMIT_HASH ?>"></script>
    <link href="node_modules/bootstrap/dist/css/bootstrap.min.css?<?= COMMIT_HASH ?>" rel="stylesheet">
    <link rel="stylesheet" href="node_modules/bootstrap-icons/font/bootstrap-icons.css?<?= COMMIT_HASH ?>">

    <!-- Fav and touch icons -->
    <link rel="apple-touch-icon" sizes="180x180" href="apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="favicon-16x16.png">
    <link rel="manifest" href="site.webmanifest">
</head>
