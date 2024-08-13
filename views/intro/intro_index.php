<h2 class="ui header">Viljandi Kutseõppekeskus</h2>
<br>
<br>

<p>Meil on hea meel, et olete otsustanud meie kooli kasuks. Enne katsete alustamist lugege läbi järgev:</p>
<h5>Reeglid</h5>
<ul>
    <li>Teil on <strong>20 minutit</strong> ülesannete lahendamiseks. Aeg algab reeglitega nõustumise järgselt.</li>
    <li>Te võite selles arvutis kasutada internetti lahenduste leidmiseks, kuid <strong>lubatud ei ole kommunikatsioon</strong> teiste isikutega,
        sealhulgas tehisintellektiga. Vahele jäämine tähendab automaatset läbikukkumist. Arvuti ekraani salvestatakse!
    </li>
</ul>
<h5>Punktiarvestus</h5>
<ul>
    <li>Programm lõppeb, kui kõik ülesanded on lahendatud või 20 minutit on ära kasutatud.</li>
    <li>Kui lahendate kõik ülesanded, siis mida vähem aega teil kulus, seda eespool olete pingereas.</li>
    <li>Kui aeg sai enne otsa, siis mida rohkem ülesandeid on lahendatud, seda eespool olete pingereas.
    </li>
</ul>

<p><strong>
        Pea meeles, et pingerida kujuneb kõige kiiremate lahendajate põhjal. Kui jääd mõne ülesande juures kinni ja tunned, et mõtted hakkavad ammenduma, proovi vahepeal teist ülesannet – see võimaldab uutel ideedel tekkida.
    </strong>
</p>

<p>Soovime teile edu katsetel!</p>

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
    const userId = <?php echo json_encode($_SESSION['userId']); ?>;
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
        ajax("exercises/start", {}, () => {

            window.location.href = 'exercises/1'
        })
    })

</script>


