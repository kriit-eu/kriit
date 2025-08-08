<h2 class="ui header">Viljandi Kutseõppekeskus</h2>
<br>
<br>

<p>Meil on hea meel, et olete otsustanud meie kooli kasuks. Enne katsete alustamist lugege läbi järgev:</p>
<h5>Reeglid</h5>
<ol>
    <li>Teil on <strong>20 minutit</strong> ülesannete lahendamiseks. Aeg algab <i>Alusta</i> nupule vajutamise
        järgselt.
    </li>
    <li><strong>Keelatud on igasugune koostöö teiste isikutega</strong>. Vahele jäämine tähendab automaatset läbikukkumist. Arvuti ekraani
        salvestatakse!
    </li>
    <li>
        Te <strong>võite</strong> selles arvutis kasutada internetti lahenduste leidmiseks.
    </li>
</ol>
<div>
    <label>
        <input type="checkbox" class="agreement"> Olen reeglitega tutvunud ja nõustun nendega
    </label>
</div>
<br>
<br>
<h5>Punktiarvestus</h5>
<ul>
    <li>Programm lõppeb, kui kõik ülesanded on lahendatud või 20 minutit on ära kasutatud.</li>
    <li>Kui lahendate kõik ülesanded, siis mida vähem aega teil kulus, seda eespool olete pingereas.</li>
    <li>Kui aeg sai enne otsa, siis mida rohkem ülesandeid on lahendatud, seda eespool olete pingereas.</li>
    <li>Kui mitu inimest on lahendanud sama arvu ülesandeid, siis määrab parema koha kiirem lahendusaeg.</li>
</ul>

<h5>Soovitused:</h5>
<ul>
    <li>
        Kuna pingerida kujuneb kõige kiiremate lahendajate põhjal, kui jääte mõne ülesande juures kinni ja
        tunnete, et mõtted hakkavad ammenduma, ärge lase ajal kuluda, vaid proovige vahepeal teist ülesannet – vaheldus
        võimaldab uutel ideedel tekkida.
    </li>
    <li>NB! Need ülesanded eeldavad HTML ja CSS algtaseme teadmisi. Kui te pole HTML-i või CSS-iga varem kokku puutunud,
        siis soovitame
        enne alustamist tutvuda järgnevate tutvustustega:
        <div>
            <button type="button" id="htmlButton" class="btn btn-success">HTML tutvustus</button>
            <button type="button" id="cssButton" class="btn btn-success">CSS tutvustus</button>
        </div>
    </li>
</ul>

<div class="center">

    <div>
        <label>
            <input type="checkbox" class="agreement"> Olen tutvunud HTML-ga
        </label>
    </div>
    <div>
        <label>
            <input type="checkbox" class="agreement"> Olen tutvunud CSS-ga
        </label>
    </div>

    <br>
    <br>
    <p>Soovime teile edu katsetel!</p>
    <button type="button" id="submitButton" class="btn btn-primary" disabled>Kinnitan et olen reeglitega tutvunud</button>

</div>

<script>
    const userId = <?php echo json_encode($_SESSION['userId']); ?>;
    // Enable submit button when agreement checkbox is checked and disabled otherwise
    // Function to check if all checkboxes are checked
    function checkAllCheckboxes() {
        const allChecked = $('.agreement').length === $('.agreement:checked').length;
        $('#submitButton').prop('disabled', !allChecked);
    }

    // Check all checkboxes on change event
    $('.agreement').change(function() {
        checkAllCheckboxes();
    });

    // Navigate to htmlCourse/ on submit
    $('#htmlButton').click(function(e) {
        e.preventDefault();
        window.location.href = 'intro/htmlCourse'
    })

    // Navigate to htmlCourse/ on submit
    $('#cssButton').click(function(e) {
        e.preventDefault();
        window.location.href = 'intro/cssCourse'
    })

    // Navigate to confirmation page on submit
    $('#submitButton').click(function(e) {
        e.preventDefault();
        window.location.href = 'intro/confirm';
    })
</script>