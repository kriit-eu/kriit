


<h2 class="ui header">Viljandi Kutseõppekeskus</h2>
<br>
<br>

<p>Enne katsete alustamist täitke lüngad, kinnitades, et olete eelneva lehe läbi lugenud.</p>
<h5>Reeglid</h5>
<ol>
    <li>Teil on <strong><input type="text" id="input-reeglid-60" class="form-control d-inline-block" style="width:120px" placeholder="..." autocomplete="off"></strong> ülesannete lahendamiseks. Aeg algab <i>Alusta</i> nupule vajutamise järgselt.</li>
    <li><strong>Keelatud on igasugune koostöö teiste isikutega</strong>. Vahele jäämine tähendab automaatset läbikukkumist. Arvuti ekraani <input type="text" id="input-reeglid-salvestatakse" class="form-control d-inline-block" style="width:120px" placeholder="..." autocomplete="off">!</li>
    <li>Te <strong><input type="text" id="input-reeglid-voite" class="form-control d-inline-block" style="width:120px" placeholder="..." autocomplete="off"></strong> selles arvutis kasutada internetti lahenduste leidmiseks.</li>
</ol>
<br>
<br>
<h5>Punktiarvestus</h5>
<ul>
    <li>Programm lõppeb, kui kõik ülesanded on lahendatud või <input type="text" id="input-punkt-60" class="form-control d-inline-block" style="width:120px" placeholder="..." autocomplete="off"> on ära kasutatud.</li>
    <li>Kui lahendate kõik ülesanded, siis mida vähem aega teil kulus, seda eespool olete <input type="text" id="input-punkt-pingereas" class="form-control d-inline-block" style="width:120px" placeholder="..." autocomplete="off">.</li>
    <li>Kui aeg sai enne otsa, siis mida rohkem ülesandeid on lahendatud, seda <input type="text" id="input-punkt-eespool" class="form-control d-inline-block" style="width:120px" placeholder="..." autocomplete="off"> olete pingereas.</li>
    <li>Kui mitu inimest on lahendanud sama arvu ülesandeid, siis määrab parema koha <input type="text" id="input-punkt-kiirem" class="form-control d-inline-block" style="width:120px" placeholder="..." autocomplete="off"> lahendusaeg.</li>
</ul>

<h5>Soovitused:</h5>
<ul>
    <li>
        Kuna pingerida kujuneb kõige <input type="text" id="input-soov-kiiremate" class="form-control d-inline-block" style="width:120px" placeholder="..." autocomplete="off"> lahendajate põhjal, kui jääte mõne ülesande juures kinni ja
        tunnete, et mõtted hakkavad ammenduma, ärge lase ajal kuluda, vaid proovige vahepeal teist ülesannet – <input type="text" id="input-soov-vaheldus" class="form-control d-inline-block" style="width:120px" placeholder="..." autocomplete="off"> võimaldab uutel ideedel tekkida.
    </li>
    <li>NB! Need ülesanded eeldavad HTML ja CSS algtaseme teadmisi. Kui te pole HTML-i või CSS-iga varem kokku puutunud,
        siis soovitame enne alustamist tutvuda järgnevate tutvustustega:
        <!-- Tutvustus nupud eemaldatud -->
    </li>
</ul>

<div style="display: flex; justify-content: space-between; align-items: center; margin-top: 1em;">
    <button type="button" id="backButton" class="btn btn-secondary">Tagasi</button>
    <button type="button" id="confirmButton" class="btn btn-primary" disabled>Alusta</button>
</div>
<div id="errorMsg" class="text-danger mt-2" style="display:none;"></div>

<div style="height: 4em;"></div>

<script>
    // Back button handler
    document.getElementById('backButton').onclick = function() {
        window.location.href = 'intro';
    };
    // Keywords for validation
    const keywords = {
        'input-reeglid-60': '60 minutit',
        'input-reeglid-salvestatakse': 'salvestatakse',
        'input-reeglid-voite': 'võite',
        'input-punkt-60': '60 minutit',
        'input-punkt-pingereas': 'pingereas',
        'input-punkt-eespool': 'eespool',
        'input-punkt-kiirem': 'kiirem',
        'input-soov-kiiremate': 'kiiremate',
        'input-soov-vaheldus': 'vaheldus',
    };

    function normalize(val) {
        return val.trim().replace(/\s+/g, ' ').toLowerCase();
    }

    function validateInputs() {
        let allValid = true;
        for (const [id, keyword] of Object.entries(keywords)) {
            const input = document.getElementById(id);
            const val = normalize(input.value);
            if (!val.includes(keyword.toLowerCase())) {
                allValid = false;
            }
        }
        document.getElementById('confirmButton').disabled = !allValid;
    }

    for (const id of Object.keys(keywords)) {
        const input = document.getElementById(id);
        input.addEventListener('input', function(e) {
            validateInputs();
            const val = normalize(input.value);
            if (val === keywords[id].toLowerCase()) {
                input.style.border = '2px solid green';
            } else {
                input.style.border = '';
            }
        });
        input.addEventListener('blur', function() {
            const val = normalize(input.value);
            if (val === keywords[id].toLowerCase()) {
                input.style.border = '2px solid green';
            } else if (!val.includes(keywords[id].toLowerCase())) {
                input.style.border = '2px solid red';
            } else {
                input.style.border = '';
            }
        });
        input.addEventListener('focus', function() {
            input.style.border = '';
        });
    }

    // Confirmation button handler
    document.getElementById('confirmButton').onclick = function() {
        fetch('exercises/start', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' }
        }).then(function() {
            window.location.href = 'exercises/1';
        });
    };
</script>
