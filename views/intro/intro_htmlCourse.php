<!DOCTYPE html>
<html lang="et">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HTML Kursus - Põhitõed</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
        }

        header, footer {
            background-color: #f8f8f8;
            padding: 10px;
            text-align: center;
        }

        section {
            margin: 20px 0;
        }

        code {
            background-color: #f4f4f4;
            padding: 2px 4px;
            border-radius: 4px;
            color: #d63384;
        }

        pre {
            background-color: #f4f4f4;
            padding: 10px;
            border-radius: 4px;
            overflow-x: auto;
            margin: 0;
            width: 100%;
            height: 100%;
        }

        .example-container {
            display: flex;
            width: 100%;
            max-width: 100%;
            margin-bottom: 20px;
            border: 1px solid #ccc;
            border-radius: 8px;
            overflow: hidden;
            flex-direction: column;
        }

        .code, .preview {
            width: 100%;
            padding: 10px;
        }

        .preview {
            background-color: #ffffff;
            height: 100%;
            padding: 0;
        }

        iframe {
            width: 100%;
            height: 200px;
            border: none;
            background-color: #ffffff;
        }

        @media (min-width: 768px) {
            .example-container {
                flex-direction: row;
            }

            .code, .preview {
                width: 50%;
            }
        }

        .button-container {
            text-align: center;
            margin-top: 40px;
        }

    </style>
</head>
<body>

<header>
    <h1>HTML kiirtutvustus</h1>
</header>

<section>
    <h4>Mis on HTML?</h4>
    <p>
        Hüpertekst on tekst, mis sisaldab <a href="https://et.wikipedia.org/wiki/H%C3%BCpertekst">linke</a> teistele
        tekstidele. HTML (HyperText Markup Language) on teksti markeerimise keel, mis võimaldab lihtsasti lisada
        lihttekstile vormindust (<strong>bold</strong>, <i>italic</i>, <u>underline</u> jne) lisada linke, aga
        ka märkida <code>&lt;h2&gt;pealkirju&lt;/h2></code> <code>&lt;p>paragrahvi algused ja lõpud&lt;/p></code>,
        lisada pilte <img src="https://yumove.co.uk/cdn/shop/articles/Dog_ageing_puppy.jpg?v=1581509635" width="16">,
        videosid, audiot, nuppe, tabeleid,
        tekstisisestusvälju, linnukesi ja palju muud.
        HTML-i kasutatakse igal veebilehel.
    </p>
    <p>

        Näiteks, et kuvada veebilehel kõige suurem (peamisem) pealkiri, kasutatakse <code>&lt;h1&gt;</code> märgendit:
        kui pealkirjaks on <i>Tere</i>, siis HTML kood selle pealkirjana kuvamiseks on
        <code>&lt;h1&gt;Tere&lt;/h1&gt;</code>.

        Brauser kuvab <code>&lt;h1&gt;</code> ja <code>&lt;/h1&gt;</code> vahel oleva teksti väga suurelt (vaata allpool
        järgmist peatükki, et seda näha),
        <code>&lt;h2&gt;</code> ja <code>&lt;/h2&gt;</code> märgendite vahel oleva teksti veidi väiksemalt jne.

        Kui sa tahad näha, kuidas praegune leht HTML-is välja näeb, siis vajuta praegu klaviatuuril <code>Ctrl+U</code>
        (või
        <code>Cmd+Option+U</code> Macil) klahvikombinatsiooni.

    </p>

    <p>Mõned näited:</p>

    <ul>
        <li><strong>Lingid</strong>: <code>&lt;a href="https://www.example.com"&gt;Mine Example.com
                lehele&lt;/a&gt;</code>.
        </li>
        <li><strong>Pildid</strong>: <code>&lt;img src="img/pilt.jpg"&gt;</code> (kui pilt asub alamkaustas img). Pilt,
            mis laaditakse otse internetist: <code>&lt;img src="https://www.sait.com/kaust/pilt.png"&gt;</code></li>
        <li><strong>Vormid</strong>: HTML-is saab kasutada vorme, et kasutajad saaks sisestada andmeid, näiteks
            kirjutada oma nime
            või e-posti aadressi. Näide form elemendist, mille sees on nime ja e-posti väljad: <code>&lt;form&gt;&lt;input
                type="text" name="nimi"&gt;&lt;input type="email" name="email"&gt;&lt;/form&gt;</code>.
        </li>
        <li><strong>Nupud</strong>: HTML võimaldab luua nuppe, mida kasutajad saavad klõpsata. Näiteks kirjutades HTML-i
            <code>&lt;button&gt;Klõpsa
                siia&lt;/button&gt;</code> näeb see brauseris välja selline:
            <button>Klõpsa siia</button>
            . Päris kole, eks? Aga selle ilustamiseks on omaette keel, mida nimetatakse CSS-iks.
            HTML-iga ei saa nupuga peale selle loomise eriti midagi muud teha, kuid
            lisades HTML lehele ka <code>&lt;script&gt; &lt;/script&gt;</code> elemendi ja selle sisse Javascript koodi,
            saab neid nuppe panna tegema ka midagi kasulikku. Näiteks saatma vormis olevaid andmeid serverisse.
        </li>
    </ul>
    <p>
    <p>HTML-i võiks kujundlikult võrrelda järgmiste asjadega:</p>

    <ol>
        <li><strong>Hoone karkass</strong>: HTML on nagu hoone karkass või raamistik, mis annab veebilehele struktuuri,
            kuid ilma sisu või stiilita. Just nagu hoone vajab viimistlust ja sisustust, vajab ka HTML CSS-i ja
            JavaScripti, et luua täielik ja funktsionaalne veebileht.
        </li>
        <li><strong>Raamatu sisu lehekülgede jaotusega</strong>: HTML on nagu raamatu struktuur, kus iga peatükk, lõik
            ja pilt on hoolikalt paigutatud. See annab vormi ja järjestuse, kuid ilma graafilise kujunduseta, mis annab
            sellele värvi ja elu (sarnane CSS-iga).
        </li>
        <li><strong>Skelett</strong>: HTML toimib nagu keha skelett, mis hoiab kõik elundid ja lihased (sisu ja
            funktsionaalsuse) paigas. Ilma selle raamistikuta ei saaks ülejäänud keha (veebileht) töötada.
        </li>
    </ol>

    <p>Need kujundlikud võrdlused aitavad mõista HTML-i rolli veebiarenduses.

        HTML-i kasutatakse iga veebilehe loomiseks. Ilma HTML-ita ei oleks veebilehti, nagu me neid täna tunneme.</p>

</section>
<section>
    <h2>1. Elementaarne HTML Dokument</h2>
    <p>HTML dokument koosneb mitmest olulisest osast. Vaatame neid lähemalt (värvid on lisatud parema loetavuse
        huvides):</p>
    <div class="example-container">
        <div class="code">
            <pre><code class="language-html">&lt;!DOCTYPE html&gt;
&lt;html lang="et"&gt;
    &lt;head&gt;
        &lt;meta charset="UTF-8"&gt;
        &lt;meta name="viewport" content="width=device-width, initial-scale=1.0"&gt;
        &lt;title&gt;Lehe Pealkiri&lt;/title&gt;
    &lt;/head&gt;
    &lt;body&gt;
        &lt;h1&gt;Tere tulemast!&lt;/h1&gt;
        &lt;p&gt;See on minu esimene HTML dokument.&lt;/p&gt;
    &lt;/body&gt;
&lt;/html&gt;</code></pre>
        </div>
        <div class="preview">
            <iframe></iframe>
        </div>
    </div>

    <p>Selgitame iga osa detailsemalt:</p>
    <strong>&lt;!DOCTYPE html&gt;</strong>
    <p>See rida on oluline, kuna see määrab dokumendi tüübi. <code>&lt;!DOCTYPE html&gt;</code> märgib, et tegemist on
        HTML5 dokumendiga. See tagab, et brauserid tõlgendavad lehekülge õigesti ja vastavalt HTML5 standardile.
        Varasemad HTML versioonid kasutasid pikemaid versioone. Näiteks HTML 4 nägi välja selline: <code>&lt;!DOCTYPE
            HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd"></code></p>

    <strong>&lt;html lang="et"&gt;</strong>
    <p><code>&lt;html&gt;</code> on kõige välimine element, mis sisaldab kogu HTML dokumenti. <code>lang</code>
        atribuudi väärtus <code>et</code>määrab lehe sisu keeleks eesti keele, mis on oluline näiteks otsimootoritele ja
        ekraanilugejatele.</p>

    <strong>&lt;head&gt;</strong>
    <p><code>&lt;head&gt;</code> element sisaldab metaandmeid (andmeid andmete kohta), mis ei ole lehe nähtav osa, kuid
        on olulised lehe
        toimimise ja brauseris õigesti kuvamise jaoks. Siin on mõned olulised elemendid <code>&lt;head></code>-is:</p>
    <ul>
        <li><code>&lt;meta charset="UTF-8"&gt;</code>: Määrab sümbolite komplekti, milliseid tähemärke ja
            muid sümboleid on võimalik lehel kasutada. Vanasti kasutati erinevates keeltes lehtede jaoks erinevaid
            sümbolikogumikke, näiteks eestikeelsetel lehtedel kasutati <a
                    href="https://en.wikipedia.org/wiki/ISO/IEC_8859-15#Codepage_layout">ISO 8859-15</a>
            tähemärgistikku (sisaldab umbes 256 erinevat sümbolit, sealhulgas kõiki eesti keele jaoks vajalikke
            täpitähti), kuid tänapäeval on kasutusel universaalne UTF-8 tähemärgistik, mis toetab korraga kõiki
            maailma
            keeli: inglise tähestiku tähed on kodeeritud ühe baidiga, muude keelte tähed kahe või kolme baidiga, mis
            teeb selle tähemärgistiku väga efektiivseks ja universaalseks, võimaldades veebilehel samaaegselt kuvada
            erinevate keelte tähemärke (üks bait on 8 bitti ehk kaheksa 0 või 1 reas, mille erinevad kombinatsioonid
            võimaldavad kuvada 256 erinevat sümbolit, mis on piisav ühe või mõne keele tähemärkide kuvamiseks, kuid
            mitte kõigi maailma keelte tähemärkide kuvamiseks (kreeka, vietnami, heebrea, korea, hiina, jaapani, vene,
            ukraina jne tähestikud sisaldavad kõik omi sümboelid), aga kui kasutada ühe tähemärgi kodeerimiseks kahte
            või kolme baiti, siis saab kuvada kõigi maailma keelte tähemärke).
        </li>
        <li><code>&lt;meta name="viewport" content="width=device-width, initial-scale=1.0"&gt;</code>: Tagab, et leht
            oleks responsiivne (kohandub automaatselt erinevatele ekraanisuurustele).
        </li>
        <li><code>&lt;title&gt;Lehe Pealkiri&lt;/title&gt;</code>: See määrab lehe pealkirja, mis kuvatakse brauseri
            vahekaardil ja otsingutulemustes.
        </li>
    </ul>

    <strong>&lt;body&gt;</strong>
    <p><code>&lt;body&gt;</code> element sisaldab kogu nähtavat sisu, mida kasutajad veebilehel näevad. See võib
        sisaldada teksti, pilte, linke, nuppe ja palju muud. Kõik, mis kuvatakse veebilehel, on selle elemendi sees.
    </p>
</section>

<section>
    <h2>2. Pealkirjad ja Paragrahvid</h2>
    <p>Pealkirjad aitavad struktureerida lehekülje sisu. HTML-s on kuus pealkirja taset:</p>
    <div class="example-container">
        <div class="code">
            <pre><code class="language-html">&lt;h1&gt;Pealkiri 1&lt;/h1&gt;
&lt;h2&gt;Pealkiri 2&lt;/h2&gt;
&lt;h3&gt;Pealkiri 3&lt;/h3&gt;
&lt;h4&gt;Pealkiri 4&lt;/h4&gt;
&lt;h5&gt;Pealkiri 5&lt;/h5&gt;
&lt;h6&gt;Pealkiri 6&lt;/h6&gt;</code></pre>
        </div>
        <div class="preview">
            <iframe></iframe>
        </div>
    </div>

    <p>Paragrahve loome kasutades <code>&lt;p&gt;</code> märgist (ehk märgendit või <i>tag</i>'i):</p>
    <div class="example-container">
        <div class="code">
            <pre><code class="language-html">&lt;p&gt;See on paragrahv.&lt;/p&gt;&lt;p&gt;See on teine paragrahv.&lt;/p&gt;</code></pre>
        </div>
        <div class="preview">
            <iframe></iframe>
        </div>
    </div>
</section>

<section>
    <h2>3. Lingid ja Pildid</h2>
    <p>Lingid võimaldavad kasutajal liikuda ühelt lehelt teisele:</p>
    <div class="example-container">
        <div class="code">
            <pre><code class="language-html">&lt;a href="https://www.example.com"&gt;Mine Example.com lehele&lt;/a&gt;</code></pre>
        </div>
        <div class="preview">
            <iframe></iframe>
        </div>
    </div>

    <p>Pilte saab HTML dokumenti lisada <code>&lt;img&gt;</code> märgisega. See on selle poolest eriline, et tal pole
        lõpumärgendit:</p>
    <div class="example-container">
        <div class="code">
            <pre><code class="language-html">&lt;img src="pilt.jpg" alt="Pildi kirjeldus"&gt;</code></pre>
        </div>
        <div class="preview">
            <iframe></iframe>
        </div>
    </div>
</section>

<section>
    <h2>4. Nimekirjad</h2>
    <p>HTML-s on kahte tüüpi nimekirju: järjestatud ja järjestamata.</p>
    <p>Järjestamata nimekiri (punktidega):</p>
    <div class="example-container">
        <div class="code">
            <pre><code class="language-html">&lt;ul&gt;
    &lt;li&gt;Esimene punkt&lt;/li&gt;
    &lt;li&gt;Teine punkt&lt;/li&gt;
&lt;/ul&gt;</code></pre>
        </div>
        <div class="preview">
            <iframe></iframe>
        </div>
    </div>

    <p>Järjestatud nimekiri (numbritega):</p>
    <div class="example-container">
        <div class="code">
            <pre><code class="language-html">&lt;ol&gt;
    &lt;li&gt;Esimene punkt&lt;/li&gt;
    &lt;li&gt;Teine punkt&lt;/li&gt;
&lt;/ol&gt;</code></pre>
        </div>
        <div class="preview">
            <iframe></iframe>
        </div>
    </div>
</section>

<section>
    <h2>5. Tabelid</h2>
    <p>Tabelid võimaldavad andmeid struktureerida ridade ja veergudena:</p>
    <div class="example-container">
        <div class="code">
            <pre><code class="language-html">&lt;table border="1"&gt;
    &lt;tr&gt;
        &lt;th&gt;Pealkiri 1&lt;/th&gt;
        &lt;th&gt;Pealkiri 2&lt;/th&gt;
    &lt;/tr&gt;
    &lt;tr&gt;
        &lt;td&gt;Andmed 1&lt;/td&gt;
        &lt;td&gt;Andmed 2&lt;/td&gt;
    &lt;/tr&gt;
&lt;/table&gt;</code></pre>
        </div>
        <div class="preview">
            <iframe></iframe>
        </div>
    </div>
</section>

<section>
    <h2>6. Vormid</h2>
    <p>Vormid võimaldavad kasutajalt andmeid koguda:</p>
    <div class="example-container">
        <div class="code">
            <pre><code class="language-html">&lt;form action="submit.php" method="post"&gt;
    &lt;label for="nimi"&gt;Nimi:&lt;/label&gt;
    &lt;input type="text" id="nimi" name="nimi"&gt;
    &lt;br&gt;
    &lt;label for="email"&gt;E-post:&lt;/label&gt;
    &lt;input type="email" id="email" name="email"&gt;
    &lt;br&gt;
    &lt;input type="submit" value="Saada"&gt;
&lt;/form&gt;</code></pre>
        </div>
        <div class="preview">
            <iframe></iframe>
        </div>
    </div>
</section>
<!-- Nupud, et minna tagasi intro ja CSS Course lehele -->
<div class="button-container">
    <a href="intro" class="btn btn-secondary back-button"><i class="bi bi-arrow-left"></i> Tagasi Introsse</a>
    <a href="intro/cssCourse" class="btn btn-success">CSS kiirtutvustus <i class="bi bi-arrow-right"></i></a>
</div>

<!-- Include Prism.js CSS -->
<link href="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/themes/prism.min.css" rel="stylesheet"/>

<!-- Include Prism.js -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/prism.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const examples = document.querySelectorAll('.example-container');

        examples.forEach(container => {
            const pre = container.querySelector('pre');
            const iframe = container.querySelector('iframe');

            const iframeDoc = iframe.contentDocument || iframe.contentWindow.document;
            iframeDoc.open();
            iframeDoc.write(`<div style="padding: 20px;">${pre.textContent}</div>`);
            iframeDoc.close();
        });

        Prism.highlightAll();
    });
</script>

</body>
</html>
