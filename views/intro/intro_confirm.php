<style>
    select.form-control {
        min-width: 120px;
        max-width: 180px;
        width: 100%;
        box-sizing: border-box;
        padding: 4px 8px;
        font-size: 1rem;
        line-height: 1.5;
        appearance: none;
        -webkit-appearance: none;
        -moz-appearance: none;
        background-color: #fff;
        border-radius: 4px;
        border: 2px solid #ced4da;
        margin: 0.1em 0.1em 0.1em 0.1em;
        vertical-align: middle;
    }
    li {
        margin-bottom: 0.2em;
    }
    ul {
        margin-bottom: 0.2em;
    }
    p {
        margin-bottom: 0.2em;
    }
    select.form-control:focus {
        outline: none;
    }
    select.form-control option {
        font-size: 1rem;
        padding: 4px 8px;
    }
</style>



<h2 class="ui header">Viljandi Kutseõppekeskus</h2>
<br>
<br>

<p>Enne katsete alustamist täitke lüngad, kinnitades, et olete eelneva lehe läbi lugenud.</p>
<h5>Reeglid</h5>
<ol>
        <li>Teil on <strong>
            <select id="input-reeglid-60" class="form-control d-inline-block" style="width:120px">
                <option value="">...</option>
                <option value="10">10</option>
                <option value="20">20</option>
                <option value="30">30</option>
            </select>
        </strong> minutit ülesannete lahendamiseks. Aeg algab <i>Alusta</i> nupule vajutamise järgselt.</li>
    <li><strong>
        <select id="input-reeglid-koostoo" class="form-control d-inline-block" style="width:120px">
            <option value="">...</option>
            <option value="Keelatud">Keelatud</option>
            <option value="Lubatud">Lubatud</option>
        </select>
        on igasugune koostöö teiste isikutega.
    </strong></li>
    <li>Te <strong>
        <select id="input-reeglid-voite" class="form-control d-inline-block" style="width:120px">
            <option value="">...</option>
            <option value="võite">võite</option>
            <option value="ei või">ei või</option>
        </select>
    </strong> selles arvutis kasutada internetti lahenduste leidmiseks.</li>
</ol>
<br>
<br>
<h5>Punktiarvestus</h5>
<ul>
        <li>Programm lõppeb, kui kõik ülesanded on lahendatud või 
            <select id="input-punkt-60" class="form-control d-inline-block" style="width:120px">
                <option value="">...</option>
                <option value="10">10</option>
                <option value="20">20</option>
                <option value="30">30</option>
            </select>
            minutit on ära kasutatud.
        </li>
    <li>Kui lahendate kõik ülesanded, siis mida vähem aega teil kulus, seda 
        <select id="input-punkt-pingereas" class="form-control d-inline-block" style="width:120px">
            <option value="">...</option>
            <option value="eespool">eespool</option>
            <option value="tagapool">tagapool</option>
        </select>
        olete pingereas.
    </li>
    <li>Kui aeg sai enne otsa, siis mida rohkem ülesandeid on lahendatud, seda 
        <select id="input-punkt-eespool" class="form-control d-inline-block" style="width:120px">
            <option value="">...</option>
            <option value="eespool">eespool</option>
            <option value="tagapool">tagapool</option>
        </select>
        olete pingereas.
    </li>
    <li>Kui mitu inimest on lahendanud sama arvu ülesandeid, siis määrab parema koha 
        <select id="input-punkt-kiirem" class="form-control d-inline-block" style="width:120px">
            <option value="">...</option>
            <option value="kiirem">kiirem</option>
            <option value="aeglasem">aeglasem</option>
        </select>
        lahendusaeg.
    </li>
</ul>

<h5>Soovitused:</h5>
<ul>
    <li>
        Kuna pingerida kujuneb kõige 
        <select id="input-soov-kiiremate" class="form-control d-inline-block" style="width:120px">
            <option value="">...</option>
            <option value="kiiremate">kiiremate</option>
            <option value="aeglasemate">aeglasemate</option>
        </select>
        lahendajate põhjal, kui jääte mõne ülesande juures kinni ja
    tunnete, et mõtted hakkavad ammenduma, ärge lase ajal kuluda, vaid proovige vahepeal teist ülesannet – vaheldus võimaldab uutel ideedel
    <select id="input-soov-tekkida" class="form-control d-inline-block" style="width:120px">
        <option value="">...</option>
        <option value="tekkida">tekkida</option>
        <option value="kaduda">kaduda</option>
    </select>.
    </li>
    <li>NB! Need ülesanded eeldavad
        <select id="input-html-css" class="form-control d-inline-block" style="width:180px">
            <option value="">...</option>
            <option value="HTML ja CSS">HTML ja CSS</option>
            <option value="JavaScript ja Python">JavaScript ja Python</option>
            <option value="React ja PHP">React ja PHP</option>
            <option value="TypeScript ja SQL">TypeScript ja SQL</option>
        </select>
        algtaseme teadmisi. Kui te pole HTML-i või CSS-iga varem kokku puutunud,
        siis soovitame enne alustamist tutvuda tutvustustega eelmisel lehel.
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
    'input-reeglid-60': '20',
    'input-reeglid-koostoo': 'Keelatud',
    'input-reeglid-voite': 'võite',
    'input-punkt-60': '20',
    'input-punkt-pingereas': 'eespool',
    'input-punkt-eespool': 'eespool',
    'input-punkt-kiirem': 'kiirem',
    'input-soov-kiiremate': 'kiiremate',
    'input-soov-vaheldus': 'vaheldus',
    'input-soov-tekkida': 'tekkida',
    'input-html-css': 'HTML ja CSS',
    };

    // Dropdown options for each select
    const dropdownOptions = {
    'input-reeglid-60': ['10', '20', '30'],
        'input-reeglid-koostoo': ['Keelatud', 'Lubatud'],
        'input-reeglid-voite': ['võite', 'ei või'],
    'input-punkt-60': ['10', '20', '30'],
        'input-punkt-pingereas': ['eespool', 'tagapool'],
        'input-punkt-eespool': ['eespool', 'tagapool'],
        'input-punkt-kiirem': ['kiirem', 'aeglasem'],
        'input-soov-kiiremate': ['kiiremate', 'aeglasemate'],
        'input-soov-tekkida': ['tekkida', 'kaduda'],
        'input-html-css': ['HTML ja CSS', 'JavaScript ja Python', 'React ja PHP', 'TypeScript ja SQL'],
    };

    function shuffle(array) {
        let currentIndex = array.length, randomIndex;
        while (currentIndex !== 0) {
            randomIndex = Math.floor(Math.random() * currentIndex);
            currentIndex--;
            [array[currentIndex], array[randomIndex]] = [array[randomIndex], array[currentIndex]];
        }
        return array;
    }

    function randomizeDropdowns() {
        for (const [id, options] of Object.entries(dropdownOptions)) {
            const select = document.getElementById(id);
            if (!select) continue;
            // Save the placeholder
            const placeholder = select.querySelector('option[value=""]');
            // Remove all options
            select.innerHTML = '';
            // Add placeholder first
            if (placeholder) {
                select.appendChild(placeholder.cloneNode(true));
            } else {
                const opt = document.createElement('option');
                opt.value = '';
                opt.textContent = '...';
                select.appendChild(opt);
            }
            // Shuffle and add options
            const shuffled = shuffle([...options]);
            for (const val of shuffled) {
                const opt = document.createElement('option');
                opt.value = val;
                opt.textContent = val;
                // Make Keelatud/Lubatud bold in koostoo dropdown
                if (id === 'input-reeglid-koostoo' && (val === 'Keelatud' || val === 'Lubatud')) {
                    opt.style.fontWeight = 'bold';
                }
                select.appendChild(opt);
            }
        }
    }

    function normalize(val) {
        return val.trim().replace(/\s+/g, ' ').toLowerCase();
    }

    function validateInputs() {
        let allValid = true;
        for (const [id, keyword] of Object.entries(keywords)) {
            const input = document.getElementById(id);
            if (!input) continue;
            if (input.tagName === 'SELECT') {
                const selectVal = input.value;
                if (id === 'input-html-css') {
                    if (selectVal !== 'HTML ja CSS') {
                        allValid = false;
                    }
                } else {
                    if (selectVal !== keyword) {
                        allValid = false;
                    }
                }
            } else {
                const textVal = normalize(input.value);
                if (!textVal.includes(keyword.toLowerCase())) {
                    allValid = false;
                }
            }
        }
        document.getElementById('confirmButton').disabled = !allValid;
    }

    document.addEventListener('DOMContentLoaded', function() {
        randomizeDropdowns();
        for (const id of Object.keys(keywords)) {
            const input = document.getElementById(id);
            if (!input) continue;
            if (input.tagName === 'SELECT') {
                // Bold the select itself if Keelatud/Lubatud is picked
                function updateBoldSelect() {
                    if (id === 'input-reeglid-koostoo' && (input.value === 'Keelatud' || input.value === 'Lubatud')) {
                        input.style.fontWeight = 'bold';
                    } else {
                        input.style.fontWeight = 'normal';
                    }
                }
                input.addEventListener('change', function(e) {
                    validateInputs();
                    updateBoldSelect();
                });
                input.addEventListener('blur', function() {
                    updateBoldSelect();
                });
                input.addEventListener('focus', function() {
                    // No border change
                });
                // Initial bold state
                updateBoldSelect();
            } else {
                input.addEventListener('input', function(e) {
                    validateInputs();
                });
                input.addEventListener('blur', function() {
                    // No border change
                });
                input.addEventListener('focus', function() {
                    // No border change
                });
            }
        }
        // Ensure button state is correct after setup
        validateInputs();
    });

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
