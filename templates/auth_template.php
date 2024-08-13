<!DOCTYPE html>
<html lang="en">
<head>
    <base href="<?= BASE_URL ?>">
    <title><?= PROJECT_NAME ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>

    <!-- Bootstrap core CSS -->
    <script src="node_modules/bootstrap/dist/js/bootstrap.min.js?<?= COMMIT_HASH ?>"></script>
    <link href="node_modules/bootstrap/dist/css/bootstrap.min.css?<?= COMMIT_HASH ?>" rel="stylesheet">

    <!-- Fav and touch icons -->
    <link rel="apple-touch-icon" sizes="180x180" href="apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="favicon-16x16.png">
    <link rel="manifest" href="site.webmanifest">

    <!-- jQuery -->
    <script src="node_modules/jquery/dist/jquery.min.js?<?= COMMIT_HASH ?>"></script>

    <style>
        body {
            padding-top: 50px;
        }

        .form-signin .form-signin-heading,
        .form-signin .checkbox {
            margin-bottom: 10px;
        }

        .form-signin .checkbox {
            font-weight: normal;
        }

        .form-signin .form-control {
            position: relative;
            font-size: 16px;
            height: auto;
            padding: 10px;
            -webkit-box-sizing: border-box;
            -moz-box-sizing: border-box;
            box-sizing: border-box;
        }

        .form-signin .form-control:focus {
            z-index: 2;
        }

        .modal-input input[type="text"] {
            margin-bottom: -1px;
            border-bottom-left-radius: 0;
            border-bottom-right-radius: 0;
        }

        .modal-input input[type="password"] {
            margin-bottom: 10px;
            border-top-left-radius: 0;
            border-top-right-radius: 0;
        }

    </style>
</head>

<body>

<div class="container">
    <h2 class="ui header">Tere tulemast Viljandi Kutseõppekeskuse sisseastumiskatsetele</h2>

    <?php if (isset($errors)) {
        foreach ($errors as $error): ?>
            <div class="alert alert-danger" role="alert">
                <?= $error ?>
            </div>
        <?php endforeach;
    } ?>

    <form method="post">
        <div class="mb-3">
            <label for="userPersonalCode" class="form-label">Isikukood</label>
            <input type="text" name="userPersonalCode" class="form-control" id="userPersonalCode"
                   aria-describedby="userPersonalCode">
            <div id="userPersonalCodeHelp" class="form-text">Sisesta enda isikukood</div>
        </div>

        <div class="mb-3" id="password-field" style="display: none">
            <label for="userPassword" class="form-label">Parool</label>
            <input type="password" name="userPassword" class="form-control" id="userPassword">
        </div>
        <button type="submit" id="submitButton" class="btn btn-primary" disabled>Logi sisse</button>
    </form>
</div>
<script src="assets/js/main.js?<?= COMMIT_HASH ?>"></script>
</body>
</html>
