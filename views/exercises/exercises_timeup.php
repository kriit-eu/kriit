<h1>Aeg on lõppenud!</h1>
<div class="results">
    <p>Teie <?= (int)(EXERCISES_SESSION_DURATION/60) ?> minutit on läbi ja katsed on nüüd lõppenud.</p>
</div>
<p>Täname teid osalemise eest! Kui olete vestluse ja fotoboksi külastuse läbi teinud, olete tänaseks vaba!</p>
<p>Edu!</p>
<div class="button-container">
    <button class="btn btn-success">Alusta uut sessiooni</button>
</div>
<script>
    document.querySelector('.btn-success').addEventListener('click', () => {
        window.location.href = '/logout';
    });
</script>