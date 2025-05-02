<?php require 'templates/partials/master_header.php'; ?>

<body>

<div class="container">

    <br/>
    <br/>

    <?php if (isset($errors)): ?>


        <?php foreach ($errors as $error): ?>

            <div class="alert alert-danger"><?= $error ?></div>

        <?php endforeach; ?>


    <?php else: ?>


        Unknown error!


    <?php endif; ?>

</div>

<?php
// Display SQL debug information if SQL_DEBUG is enabled and user is admin
if (defined('SQL_DEBUG') && SQL_DEBUG && isset($auth) && $auth->userIsAdmin) {
    \App\Db::displayDebugInfo();
}
?>

</body>
</html>
