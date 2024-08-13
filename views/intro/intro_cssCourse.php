<style>


    h1 {
        color: #333;
    }

    p {
        color: #666;
        font-size: 16px;
        line-height: 1.6;
    }

    .example-container {
        display: flex;
        justify-content: space-between;
        margin-bottom: 40px;
    }

    .editor-container, iframe {
        width: 48%;
        height: 400px;
        border: 1px solid #ddd;
        margin-bottom: 20px;
    }

    pre {
        background-color: #f4f4f4;
        padding: 10px;
        border-radius: 5px;
    }

    iframe {
        border-left: 1px solid #ddd;
    }

    .box {
        background-color: yellow;
        color: black;
        padding: 20px;
        border-radius: 5px;
        margin-bottom: 20px;
        border: 1px solid #ddd;
    }
</style>
<!-- Ace Editor CDN -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/ace/1.4.12/ace.js" crossorigin="anonymous"></script>
<!-- js-beautify CDN -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/js-beautify/1.14.0/beautify.min.js"
        crossorigin="anonymous"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/js-beautify/1.14.0/beautify-html.min.js"
        crossorigin="anonymous"></script>


<h2>Mis on CSS?</h2>
<p>CSS ehk Cascading Style Sheets on vahend, millega saab muuta veebilehtede välimust. See aitab meil otsustada,
    millised värvid, fondid ja paigutus on veebilehel, et see näeks välja just selline, nagu me soovime.</p>

<h2>Kuidas CSS töötab?</h2>
<p>CSS koosneb reeglitest, mis ütlevad veebilehele, kuidas erinevaid elemente (näiteks teksti või pilte) kuvada. Iga
    reegel koosneb kahest osast:</p>

<ul>
    <li><strong>Valija:</strong> See ütleb, milliseid HTML-i elemente muudetakse. Näiteks <code>h1</code> valija muudab
        kõiki pealkirju, mis on märgitud &lt;h1&gt; sildiga.
    </li>
    <li><strong>Deklaratsioon:</strong> See ütleb, kuidas neid elemente muudetakse. Näiteks <code>color: blue;</code>
        muudab teksti siniseks.
    </li>
</ul>

<p>Üks lihtne CSS-i reegel näeb välja selline:</p>
<pre><code>h1 {
    color: blue;
    font-size: 24px;
}</code></pre>

<p>Selles näites:</p>
<ul>
    <li><code>h1</code> ütleb, et see reegel mõjutab kõiki &lt;h1&gt; pealkirju.</li>
    <li><code>color: blue;</code> määrab, et need pealkirjad muutuvad siniseks.</li>
    <li><code>font-size: 24px;</code> määrab, et nende pealkirjade suurus on 24 pikslit.</li>
</ul>

<p>CSS-iga saad muuta palju erinevaid asju veebilehe välimuse kohta, nagu taustavärvi, teksti asukohta ja palju
    muud.</p>

<div class="box"> Kui sul on konkreetne eesmärk, mida soovid saavutada, sisesta Google'i otsingusse see koos märksõnaga
    "CSS".
    Näiteks, kui soovid elemente <code>div</code> elemendi sees keskendada, otsi <code>CSS center div</code>. Tavaliselt
    leiad kiiresti otsitava deklaratsiooni ja näited, kuidas seda kasutada.
</div>


<p>Allpool saad proovida ise CSS-i kasutamist ja näha, kuidas see muudab veebilehte.</p>
<p>Muuda julgelt allpool CSS-i (<code>&lt;style></code> märgendi sisu) ja vaata, kuidas see lõpptulemust muudab!</p>
<div id="examples-container"></div>

<!-- Nupud, et minna tagasi intro ja CSS Course lehele -->
<div class="button-container">
    <a href="intro" class="btn btn-secondary back-button"><i class="bi bi-arrow-left"></i> Tagasi Introsse</a>
    <a href="intro/htmlCourse" class="btn btn-success"><i class="bi bi-arrow-left"> HTML kiirtutvustus</i></a>
</div>

<br>

<script>
    // Staatiline HTML-mall, mis sisaldab muutuvat <style> tagi ja HTML-i sisu
    const htmlTemplate = (cssContent, htmlContent) => `<!DOCTYPE html><html lang="et"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><title>CSS Näide</title><style>${cssContent}</style></head><body>${htmlContent}</body></html>`;

    // Massiiv näidete andmetega, mis sisaldab ainult CSS-i ja HTML-i sisu
    const examples = [
        {
            title: "1. Muuda Teksti Värvi ja Suurust",
            description: "Allolevas näites saad muuta pealkirja värvi ja lõigu teksti suurust, muutes koodi paremal asuvas kastis.",
            cssContent: `h1 {\n    color: blue;\n}\np {\n    font-size: 20px;\n}`,
            htmlContent: `<h1>Pealkiri</h1><p>See on näidislõigu tekst.</p>`
        },
        {
            title: "2. Muuda Taustavärvi",
            description: "Siin saad katsetada, kuidas mõjutada kogu lehe taustavärvi CSS-i abil.",
            cssContent: `body {\n    background-color: lightblue;\n}`,
            htmlContent: `<h1>Pealkiri</h1><p>See on näidislõigu tekst.</p>`
        },
        {
            title: "3. Muuda Ääriste Välimust",
            description: "Proovi muuta kasti ääriseid ja tausta, kasutades CSS-i. Näed, kuidas väikeste muudatustega saad muuta kasti välimust.",
            cssContent: `div {\n    border: 2px solid black;\n    border-radius: 8px;\n    padding: 20px;\n    background-color: yellow;\n    box-shadow: 5px 5px 10px #888888;\n}\ndiv:hover {\n    text-decoration: underline;\n}`,
            htmlContent: `<div>Näidiskast</div>`
        }
    ];

    // Näidete genereerimine tsükliga
    const container = document.getElementById('examples-container');
    examples.forEach((example, index) => {
        // Loo HTML struktuur näidise jaoks
        const exampleDiv = document.createElement('div');
        exampleDiv.classList.add('example-container');
        exampleDiv.innerHTML = `<div id="editor-${index}" class="editor-container"></div><iframe id="result-${index}"></iframe>`;
        container.appendChild(exampleDiv);

        // Initsialiseeri Ace Editor iga näidise jaoks
        const editor = ace.edit(`editor-${index}`);
        editor.session.setMode("ace/mode/html");
        editor.setTheme("ace/theme/monokai");
        const initialContent = htmlTemplate(example.cssContent, example.htmlContent);

        // Autoformat - korrasta ja treppi koodi automaatselt
        const formattedContent = html_beautify(initialContent, {
            indent_size: 2,
            space_in_empty_paren: true,
            preserve_newlines: true,
            end_with_newline: true,
            max_preserve_newlines: 1,
            wrap_line_length: 0
        });

        editor.setValue(formattedContent, 1);

        // Funktsioon iframe'i sisu uuendamiseks
        function updateResult() {
            const iframe = document.getElementById(`result-${index}`);
            const content = editor.getValue();
            iframe.contentWindow.document.open();
            iframe.contentWindow.document.write(content);
            iframe.contentWindow.document.close();
        }

        // Uuenda iframe sisu, kui koodi muudetakse
        editor.session.on('change', updateResult);
        updateResult();
    });
</script>

