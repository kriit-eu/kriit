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

</body>
</html>
