<?php
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../classes/App/Db.php';
session_start();
if (empty($_SESSION['userId'])) {
    header('Location: /');
    exit;
}
$userId = $_SESSION['userId'];
$totalExercises = App\Db::getOne('SELECT COUNT(*) FROM exercises');
$completedExercises = App\Db::getOne('SELECT COUNT(*) FROM userExercises WHERE userId = ? AND status = "completed"', [$userId]);
if ($completedExercises < $totalExercises) {
    header('Location: /exercises');
    exit;
}
?>
<h1>Õnnitleme! Olete kõik ülesanded edukalt lahendanud!</h1>
<div class="results">
    <p>Teie tulemused:</p>
    <p>Lahendatud ülesandeid: <span class="highlight"><?=$solvedExercisesCount?></span></p>
    <p>Kokku kulunud aeg: <span class="highlight"><?=$auth->userTimeTotal?></span></p>
</div>

<p>Täname teid osalemise eest! Kui olete vestluse ja fotoboksi külastuse läbi teinud, olete tänaseks vaba!</p>
<p>Edu!</p>
<div class="button-container">
    <button class="btn btn-success" id="btnNewSession">Alusta uut sessiooni</button>
</div>
<script>
    document.querySelector('#btnNewSession').addEventListener('click', () => {
        window.location.href = '';
    });
</script>
