<h1>Aeg on lõppenud!</h1>
<div class="results">
    <p>Teie 20 minutit on läbi ja katsed on nüüd lõppenud.</p>
</div>
<p>Täname teid osalemise eest! Palun andke vastuvõtukomisjonile teada, et olete katse lõpetanud.</p>
<p>Edu!</p>
<div class="button-container">
    <button class="button">Alusta uut sessiooni</button>
</div>
<script>
    document.querySelector('.button').addEventListener('click', () => {
        window.location.href = 'logout';
    });
</script>
