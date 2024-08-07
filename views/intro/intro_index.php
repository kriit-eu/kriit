<h2 class="ui header">Viljandi Kutseõppekeskus</h2>
<br>
<br>

<p>Meil on hea meel, et olete otsustanud meie kooli kasuks. Enne katsete alustamist lugege läbi järgmised juhised ja
    reeglid:</p>

<h6><strong>Alustamine</strong></h6>
<p>Teil on 60 minutit ülesannete lahendamiseks. Te võite kasutada internetti lahenduste leidmiseks, kuid lubatud ei ole
    koostöö teiste isikutega, sealhulgas tehisintellektiga. Vahele jäämine tähendab automaatset läbikukkumist.</p>

<h6><strong>Ülesannete lahendamine</strong></h6>
<p>Ülesanne kuvatakse lehe vasakul poolel ja lahenduse koht paremal poolel. Kui olete ülesande lahendanud, vajutage
    nupule "Kontrolli".</p>
<p>Õige lahendus: Kuvatakse tehtud ja veel tegemata ülesannete loend. Saate valida järgmise ülesande.</p>
<p>Vale lahendus: Kuvatakse teade ja aeg jookseb edasi. Jätkake lahendamist.</p>


<h6><strong>Lõpp</strong></h6>
<p>Programm lõppeb, kui kõik ülesanded on lahendatud või 60 minutit on ära kasutatud.</p>
<p>Kui lahendate kõik ülesanded, siis mida vähem aega teil kulus, seda eespool olete pingereas. Kui aeg sai enne otsa,
    siis mida rohkem ülesandeid on lahendatud, seda eespool olete pingereas.</p>

<p>Soovime teile edu katsetel!</p>

<?php if (isset($errors)) {
    foreach ($errors as $error): ?>
        <div class="alert alert-danger" role="alert">
            <?= $error ?>
        </div>
    <?php endforeach;
} ?>
<br>
<br>

<div class="center">
    <label>
        <input type="checkbox" id="agreement"> Olen reeglitega tutvunud ja nõustun nendega
    </label>
    <br>
    <br>
    <button type="button" id="submitButton" class="btn btn-primary" disabled>Alusta</button>
</div>
<script>

    // Enable submit button when agreement checkbox is checked and disabled otherwise
    $('#agreement').change(function () {
        if (this.checked) {
            $('#submitButton').prop('disabled', false);
        } else {
            $('#submitButton').prop('disabled', true);
        }
    })

    // Navigate to exercises/ on submit
    $('#submitButton').click(function (e) {
        e.preventDefault();
        window.location.href = 'exercises/';
    })

</script>

<script src="assets/js/main.js?<?= COMMIT_HASH ?>"></script>


