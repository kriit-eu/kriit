<!-- Paste disabled banner (pushes page down, not fixed, always visible at top) -->
<div id="paste-banner" style="display:none;opacity:0;background:#c00;color:#fff;text-align:center;font-weight:bold;padding:14px 0 14px 0;font-size:1.2em;box-shadow:0 2px 8px rgba(0,0,0,0.08);margin-bottom:18px;transition: opacity 0.5s;">
    Kleepimine (paste) on kõigis ülesannetes keelatud. Lõbusat tippimist!
</div>
<!-- Main content wrapper -->
<div class="codecademy-ui d-flex flex-column h-100">
    <!-- Top panel for small screens -->
    <div class="d-md-none">
        Leht on liiga kitsas. Palun tee brauseris see aken laiemaks.
    </div>

    <!-- Content area -->
    <div class="tab-content flex-grow-1 d-flex">
        <!-- Instructions panel -->
        <div class="tab-pane fade show active d-md-flex col-md-3 bg-light instructions-panel" id="instructions"
             role="tabpanel"
             aria-labelledby="instructions-tab">
            <div class="p-3 overflow-auto h-100">
                <h3>Ülesanne:</h3>
                <p><?= $exercise['exerciseInstructions'] ?></p>
                <?php
                $helpMessages = [
                    "NB! Kui sa ei leia lahendust, vaata internetist abi!",
                    "NB! Kui midagi välja ei tule, otsi abi veebist!",
                    "NB! Kui takerdud, internet pakub lahendusi!",
                    "NB! Kui sa ei saa selgeks, internet aitab!",
                    "NB! Kui jääd segadusse, otsi nõu veebist!",
                    "NB! Kui sa ei mõista, vaata vastuseid internetist!",
                    "NB! Kui vajad tuge, internet on parim abiline!",
                    "NB! Kui tekib küsimusi, küsi internetist!",
                    "NB! Kui sa ei leia vastust, pöördu veebiallika poole!",
                    "NB! Kui oled ummikus, leia lahendusi internetis!",
                    "NB! Kui sul jääb arusaamine puudu, vaata internetist!",
                    "NB! Kui ei lähe plaanipäraselt, uurige veebist edasi!",
                    "NB! Kui sul ei õnnestu üksi, leia veebist abi!",
                    "NB! Kui sa seisad silmitsi raskustega, internet on toeks!",
                    "NB! Kui sa ei tea, kust alustada, internet suunab sind!",
                    "NB! Kui jääd puntrasse, kasuta interneti tuge!",
                    "NB! Kui ei õnnestu omal jõul, internet pakub abi!",
                    "NB! Kui sa ei saa edasi, internet annab vastuseid!",
                    "NB! Kui sul tekib ummik, pööra pilk internetile!",
                    "NB! Kui sa ei oska ise, otsi suuniseid veebist!"
                ];
                $randomMessage = $helpMessages[array_rand($helpMessages)];
                ?>
                <div class="help-message mt-3" style="color: #111; font-size: 1.25em; font-weight: bold;">
                    <?= htmlspecialchars($randomMessage) ?>
                </div>
            </div>
        </div>
        <!-- Editor panel -->
        <div class="tab-pane fade d-md-flex col-md-5 p-0 h-100" id="editor-panel" role="tabpanel"
             aria-labelledby="editor-tab">
            <div id="editor" class="h-100 w-100"></div>
        </div>

        <!-- Preview panel -->
        <div class="tab-pane fade d-md-flex col-md-4 p-0 h-100" id="preview" role="tabpanel"
             aria-labelledby="preview-tab">
            <div id="preview-content" class="p-0 d-flex flex-column h-100 w-100">
                <div id="preview-body" class="flex-grow-1 position-relative">
                    <iframe id="preview-iframe" class="w-100 h-100" style="border: none;"></iframe>
                </div>
            </div>
        </div>
    </div>


    <div class="footer-container">
            <div class="footer clickable-footer">
                <a class="btn btn-secondary footer-btn-left" href="exercises" tabindex="-1">
                    <i class="bi bi-arrow-left"></i>
                    Ülesannete loendisse
                </a>
                <div class="timer-label-group">
                    <span class="timer-label">Ülesandele kulunud aeg:</span>
                    <span id="timer" class="timer<?= (isset($this->elapsedTime) && $this->elapsedTime >= 300) ? ' overdue' : '' ?>"><?= gmdate("i:s", $this->elapsedTime ?? 0) ?></span>
                </div>
                <button class="btn btn-success footer-btn-right" onclick="validateSolution()" tabindex="-1">
                    <i class="bi bi-check"></i>
                    Kontrolli lahendust
                </button>
            </div>
        <style>
            .footer {
                display: flex;
                align-items: center;
                justify-content: center;
                position: relative;
                padding: 10px;
                background-color: #f1f1f1;
                border-top: 1px solid #ccc;
                height: 60px;
            }
            .footer-btn-left {
                position: absolute;
                left: 10px;
                top: 50%;
                transform: translateY(-50%);
                z-index: 2;
            }
            .footer-btn-right {
                position: absolute;
                right: 10px;
                top: 50%;
                transform: translateY(-50%);
                z-index: 2;
            }
            .timer-label-group {
                display: flex;
                flex-direction: row;
                align-items: center;
                justify-content: center;
                margin: 0 auto;
                position: relative;
                z-index: 1;
                white-space: nowrap;
            }
            .timer-label {
                font-size: 2rem;
                color: #333;
                font-weight: bold;
                margin-bottom: 0;
                margin-right: 10px;
            }
            .timer {
                min-width: 48px;
                display: inline-block;
                text-align: left;
                transition: color 0.3s;
                font-weight: bold;
                font-size: 2rem;
                margin-left: 0;
            }
            .timer.overdue { color: #c00; font-weight: bold; }
        </style>

        <div class="validation-code-section">
            <h5 class="text-center" style="background-color: #f4f2f0; padding: 0; margin: 0">Sinu lahendust
                valideeritakse järgneva algoritmiga (võibolla aitab see sind, aga kui mitte, siis ignoreeri seda):</h5>
            <pre><code class="language-javascript"><?= htmlspecialchars($exercise['exerciseValidationFunction']) ?></code></pre>
        </div>
    </div>
</div>

<style>
    /* Lower Monaco scrollbar z-index so footer overlays it */
    .monaco-scrollable-element {
        z-index: 1 !important;
    }
    html, body, #container {
        height: 100%;
        margin: 0;
        padding: 0;
    }

    #container {
        max-width: 100%;
    }

    .codecademy-ui {
        min-height: 100vh;
    }

    #editor {
        font-size: 14px;
    }

    @media (min-width: 768px) {
        .tab-content {
            flex-direction: row !important;
        }

        .tab-pane {
            display: flex !important;
            opacity: 1 !important;
            flex-grow: 1;
        }

        #preview-content {
            display: flex;
            flex-direction: column;
            height: 100%;
            width: 100%;
        }

        #preview-body {
            flex-grow: 1;
            position: relative;
        }

        #preview-iframe {
            width: 100%;
            height: 100%;
            position: absolute;
            top: 0;
            left: 0;
        }
    }

    .instructions-section, .validation-code-section {
        padding-bottom: 15px;
    }

    .validation-code-section pre {
        margin: 0 !important;
        padding: 0 !important;
        font-size: 0.7em;
    }

    hr.my-2 {
        margin-top: 10px;
        margin-bottom: 10px;
        border: 0;
        border-top: 1px solid #ccc;
    }

    .footer-container {
        position: fixed;
        bottom: 0;
        left: 0;
        width: 100%;
        z-index: 100;
        background-color: #f1f1f1;
        border-top: 1px solid #ccc;
        height: 60px; /* Only footer visible by default */
        overflow: hidden; /* Hide any overflowing content initially */
        transition: height 0.3s ease-in-out;
    }

    /* Expanded height to fit content */
    .footer-container.active {
        height: auto;
    }


    .footer {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 10px;
        background-color: #f1f1f1;
        border-top: 1px solid #ccc;
        height: 60px;
    }

    .footer .btn-secondary {
        order: 1;
    }

    .footer .timer {
    order: 2;
    flex-grow: 1;
    width: 100%;
    text-align: center;
    font-weight: bold;
    display: block;
    font-size: 2rem;
    }

    .footer .btn-success {
        order: 3;
    }

    /* Reduce the padding and font size for the code block */
    .instructions-panel pre {
        background: #f8f9fa; /* Light background for better readability */
        font-size: 10px; /* Smaller font size */
        padding: 10px; /* Reduce padding */
        overflow-x: auto; /* Enable horizontal scrolling */
        margin: 0; /* Remove margin */
    }

    .instructions-panel code {
        white-space: pre-wrap; /* Allow wrapping of code */
        word-break: break-word; /* Break long words to fit */
    }

    /* Styling for the validation section */
    .validation-code-section {
        padding: 0; /* Remove padding */
        margin: 0; /* Remove margin */
        background-color: #f8f9fa;
        border-top: 1px solid #ccc;
        border-bottom: none; /* Remove any unwanted borders */
        box-sizing: border-box; /* Ensure padding doesn't add to width/height */
    }

    .footer-container.active .validation-code-section {
        visibility: visible; /* Show when active */
    }

</style>
<style>
    /* Monaco decoration for CSS selectors to match VS Code style (see screenshot) */
    .css-selector-highlight-yellow {
        color: #d7ba7d !important; /* VS Code selector color */
        background: none !important;
        border: none !important;
        border-radius: 0 !important;
        padding: 0 !important;
    }
</style>


<!-- Include Prism.js CSS -->
<link href="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/themes/prism.min.css" rel="stylesheet"/>

<!-- Include Prism.js -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/prism.min.js"></script>
<!-- Include Prism.js for JavaScript language -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/components/prism-javascript.min.js"></script>
<!-- Include Monaco Editor (VS Code Editor) -->
<script src="https://cdn.jsdelivr.net/npm/monaco-editor@0.52.2/min/vs/loader.js"></script>
<!-- Include Emmet for Monaco Editor -->
<script src="https://unpkg.com/emmet-monaco-es@5.5.0/dist/emmet-monaco.min.js"></script>
<!-- Include CSSLint (local bundle) and bridge -->
<script src="/assets/js/csslint.js"></script>
<script src="/assets/js/csslint-cdn-bridge.js"></script>
<script>
    let editor;

    function initEditor() {
        if (!editor) {
            // Configure Monaco Editor paths
            require.config({ 
                paths: { 
                    'vs': 'https://cdn.jsdelivr.net/npm/monaco-editor@0.52.2/min/vs' 
                }
            });

            require(['vs/editor/editor.main'], function() {
                // Load html-validate bundle for browser
                const htmlValidateScript = document.createElement('script');
                htmlValidateScript.src = '/assets/js/html-validate-cdn-bridge.bundle.js';
                htmlValidateScript.onload = () => {
                    window.htmlValidateInstance = new window.htmlValidate({
                        extends: ["html-validate:recommended"],
                        elements: ["html5"]
                    });
                };
                document.head.appendChild(htmlValidateScript);
                // Create Monaco Editor instance with basic configuration
                editor = monaco.editor.create(document.getElementById('editor'), {
                    value: <?= json_encode($exercise['exerciseInitialCode']) ?>,
                    language: 'html',
                    theme: 'vs-dark', // Use dark modern theme
                    fontSize: 14,
                    automaticLayout: true,
                    wordWrap: 'on',
                    lineNumbers: 'on',
                    minimap: { enabled: false }
                });
                // Ensure theme is set after creation in case Monaco loads default
                monaco.editor.setTheme('vs-dark');

                // Configure HTML language defaults for better validation
                monaco.languages.html.htmlDefaults.setOptions({
                    validate: true,
                    lint: {
                        compatibleVendorPrefixes: 'ignore',
                        vendorPrefix: 'warning',
                        duplicateProperties: 'warning',
                        emptyRules: 'warning',
                        importStatement: 'ignore',
                        boxModel: 'ignore',
                        universalSelector: 'ignore',
                        zeroUnits: 'ignore',
                        fontFaceProperties: 'warning',
                        hexColorLength: 'error',
                        argumentsInColorFunction: 'error',
                        unknownProperties: 'warning',
                        ieHack: 'ignore',
                        unknownVendorSpecificProperties: 'ignore',
                        propertyIgnoredDueToDisplay: 'warning',
                        important: 'ignore',
                        float: 'ignore',
                        idSelector: 'ignore'
                    }
                });

                // Highlight all CSS selectors in <style> tags as yellow
                let selectorDecorations = [];
                function highlightCssSelectors() {
                    const model = editor.getModel();
                    const value = model.getValue();
                    const decorations = [];
                    // Find <style>...</style> blocks
                    const styleRegex = /<style[^>]*>([\s\S]*?)<\/style>/gi;
                    let styleMatch;
                    while ((styleMatch = styleRegex.exec(value)) !== null) {
                        const styleBlock = styleMatch[1];
                        // Find selectors (naive: match anything before {, skip @rules)
                        let offset = styleMatch.index + styleMatch[0].indexOf(styleBlock);
                        const selectorRegex = /(^|\n)\s*([^@\n{}][^{}]*)\s*\{/g;
                        let selMatch;
                        while ((selMatch = selectorRegex.exec(styleBlock)) !== null) {
                            const selectorText = selMatch[2];
                            // Calculate start/end in model
                            const before = value.slice(0, offset + selMatch.index + selMatch[0].indexOf(selectorText));
                            const startLine = before.split('\n').length;
                            const startCol = before.length - before.lastIndexOf('\n');
                            decorations.push({
                                range: new monaco.Range(startLine, startCol, startLine, startCol + selectorText.length),
                                options: {
                                    inlineClassName: 'css-selector-highlight-yellow'
                                }
                            });
                        }
                    }
                    selectorDecorations = editor.deltaDecorations(selectorDecorations, decorations);
                }


                // Set up change listeners
                editor.onDidChangeModelContent(async function() {
                    updatePreview();
                    // Use html-validate for robust HTML validation
                    if (window.htmlValidateInstance) {
                        const value = editor.getValue();
                        const report = await window.htmlValidateInstance.validateString(value);
                        const markers = (report.results[0]?.messages || []).map(msg => ({
                            startLineNumber: msg.line,
                            startColumn: msg.column,
                            endLineNumber: msg.line,
                            endColumn: msg.column + 1,
                            message: msg.message,
                            severity: monaco.MarkerSeverity.Error
                        }));
                        monaco.editor.setModelMarkers(editor.getModel(), 'html-validate', markers);
                    }
                    // CSS validation for <style> tags using CSSLint
                    if (window.CSSLint) {
                        const value = editor.getValue();
                        const styleRegex = /<style[^>]*>([\s\S]*?)<\/style>/gi;
                        let styleMatch;
                        let cssMarkers = [];
                        while ((styleMatch = styleRegex.exec(value)) !== null) {
                            const css = styleMatch[1];
                            const styleBlockStart = styleMatch.index + styleMatch[0].indexOf(css);
                            
                            // Run CSSLint
                            const result = window.CSSLint.verify(css);
                            for (const msg of result.messages) {
                                if (msg.line && msg.col) {
                                    // Find the exact position in the HTML document
                                    const cssLines = css.split('\n');
                                    const errorLine = cssLines[msg.line - 1] || '';
                                    
                                    // Calculate HTML position
                                    const beforeErrorLine = css.split('\n').slice(0, msg.line - 1).join('\n');
                                    const errorPositionInCss = beforeErrorLine.length + (beforeErrorLine ? 1 : 0) + (msg.col - 1);
                                    const absolutePosition = styleBlockStart + errorPositionInCss;
                                    
                                    // Convert to line/column in HTML
                                    const beforeError = value.slice(0, absolutePosition);
                                    const htmlLine = beforeError.split('\n').length;
                                    const htmlCol = absolutePosition - beforeError.lastIndexOf('\n');
                                    
                                    // Try to find the actual broken word/token for better highlighting
                                    const errorText = errorLine.slice(msg.col - 1);
                                    const wordMatch = errorText.match(/^[a-zA-Z0-9-_]+/);
                                    const errorLength = wordMatch ? wordMatch[0].length : 1;
                                    
                                    cssMarkers.push({
                                        startLineNumber: htmlLine,
                                        startColumn: htmlCol,
                                        endLineNumber: htmlLine,
                                        endColumn: htmlCol + errorLength,
                                        message: `CSS: ${msg.message}`,
                                        severity: msg.type === 'error' ? monaco.MarkerSeverity.Error : monaco.MarkerSeverity.Warning
                                    });
                                }
                            }
                        }
                        monaco.editor.setModelMarkers(editor.getModel(), 'csslint', cssMarkers);
                    }
                    highlightCssSelectors();
                });

                // Initial highlight after editor creation
                setTimeout(highlightCssSelectors, 100);

                // Initialize Emmet for HTML
                if (typeof emmetMonaco !== 'undefined') {
                    emmetMonaco.emmetHTML(monaco, ['html', 'php']);
                    console.log('Emmet initialized successfully for Monaco Editor');
                } else {
                    console.warn('Emmet library not loaded');
                }

                // Set up change listeners
                editor.onDidChangeModelContent(function() {
                    updatePreview();
                });

                // Disable paste functionality
                editor.addAction({
                    id: 'disable-paste',
                    label: 'Disable Paste',
                    keybindings: [
                        monaco.KeyMod.CtrlCmd | monaco.KeyCode.KeyV,
                        monaco.KeyMod.Shift | monaco.KeyCode.Insert
                    ],
                    run: function() {
                        // Show paste disabled banner
                        var banner = document.getElementById('paste-banner');
                        if (banner) {
                            banner.style.display = 'block';
                            void banner.offsetWidth;
                            banner.style.opacity = '1';
                            clearTimeout(banner._hideTimeout);
                            banner._hideTimeout = setTimeout(function() {
                                banner.style.opacity = '0';
                                setTimeout(function() {
                                    banner.style.display = 'none';
                                }, 500);
                            }, 3500);
                        }
                        return null;
                    }
                });
                // Show paste banner on global paste attempts
                document.addEventListener('keydown', function(e) {
                    if ((e.ctrlKey || e.metaKey) && e.key.toLowerCase() === 'v') {
                        var banner = document.getElementById('paste-banner');
                        if (banner) {
                            banner.style.display = 'block';
                            void banner.offsetWidth;
                            banner.style.opacity = '1';
                            clearTimeout(banner._hideTimeout);
                            banner._hideTimeout = setTimeout(function() {
                                banner.style.opacity = '0';
                                setTimeout(function() {
                                    banner.style.display = 'none';
                                }, 500);
                            }, 3500);
                        }
                    }
                }, true);

                // Block paste at DOM level
                const editorDom = editor.getDomNode();
                if (editorDom) {
                    editorDom.addEventListener('paste', function(e) {
                        e.preventDefault();
                        e.stopPropagation();
                        return false;
                    }, true);
                    
                    editorDom.addEventListener('contextmenu', function(e) {
                        e.preventDefault();
                        e.stopPropagation();
                        return false;
                    }, true);
                }

                // Initial preview update
                updatePreview();
            });
        }
    }

    function updatePreview() {
        if (!editor) return;
        
        const previewIframe = document.getElementById('preview-iframe');
        const iframeDoc = previewIframe.contentDocument || previewIframe.contentWindow.document;
        iframeDoc.open();
        iframeDoc.write(editor.getValue());
        iframeDoc.close();
    }

    function validateSolution() {
        if (!editor) return;
        
        const exerciseId = <?= json_encode($exercise['exerciseId']) ?>;
        const userCode = editor.getValue();

        // Get the validation function code from the PHP backend
        let validationCode = `<?= addslashes($exercise['exerciseValidationFunction']) ?>`;

        // Create an iframe to safely execute the user's code
        const validationIframe = document.createElement('iframe');
        document.body.appendChild(validationIframe);

        validationIframe.style.display = 'none'; // Hide the iframe
        validationIframe.onload = function() {
            try {
                // Inject the validation function code as a string into the iframe's context
                validationIframe.contentWindow.eval(validationCode);

                // Call the validate function and store the result
                const validationResult = validationIframe.contentWindow.validate();
                console.log(validationResult);

                if (validationResult) {
                    // If validation is successful, notify the backend
                    ajax(`exercises/markAsSolved/${exerciseId}`, {answer: userCode},
                        function (res) {
                            if (res.status === 200) {
                                window.location.href = 'exercises';
                            } else {
                                alert('Tekkis viga serveriga suhtlemisel.');
                            }
                        }
                    );
                } else {
                    alert('Teie lahendus ei läbinud valideerimist. Palun proovige uuesti.');
                }
            } catch (error) {
                alert('Valideerimine ebaõnnestus. Palun proovige uuesti.');
            } finally {
                // Remove the iframe after validation
                document.body.removeChild(validationIframe);
            }
        };

        // Inject the user's code into the iframe
        const validationDoc = validationIframe.contentDocument || validationIframe.contentWindow.document;
        validationDoc.open();
        validationDoc.write(userCode);
        validationDoc.close();
    }

    // Initialize things when the DOM is fully loaded
    document.addEventListener('DOMContentLoaded', function () {
        initEditor();

        const footerContainer = document.querySelector('.footer-container');
        const validationSection = document.querySelector('.validation-code-section');
        const footerHeight = 60;  // Height of the footer

        const validationHeight = validationSection.scrollHeight; // Get the height of the validation content
        footerContainer.style.height = `${footerHeight + validationHeight}px`;
        footerContainer.classList.add('active');
        validationSection.style.visibility = 'visible';

        // Toggle the drawer on footer click (not just timer)
        document.querySelector('.footer.clickable-footer').addEventListener('click', function (e) {
            // Prevent toggling when clicking on a button or link inside the footer
            if (e.target.closest('button') || e.target.closest('a')) return;
            if (footerContainer.classList.contains('active')) {
                // Collapse the footer to its original height
                footerContainer.style.height = `${footerHeight}px`;
                footerContainer.classList.remove('active');
                validationSection.style.visibility = 'hidden';
            } else {
                // Expand the footer to show the validation code
                const validationHeight = validationSection.scrollHeight; // Get the height of the validation content
                footerContainer.style.height = `${footerHeight + validationHeight}px`;
                footerContainer.classList.add('active');
                validationSection.style.visibility = 'visible';
            }
        });

        // Timer logic (count up)
        const timerElement = document.getElementById('timer');
        let elapsed = <?= $this->elapsedTime ?? 0 ?>;
        function updateTimer() {
            const minutes = Math.floor(elapsed / 60);
            const seconds = elapsed % 60;
            timerElement.textContent = `${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;
            if (elapsed >= 300) {
                timerElement.classList.add('overdue');
            } else {
                timerElement.classList.remove('overdue');
            }
            elapsed++;
        }
        // Set overdue class immediately if needed
        if (elapsed >= 300) {
            timerElement.classList.add('overdue');
        } else {
            timerElement.classList.remove('overdue');
        }
        updateTimer();
        setInterval(updateTimer, 1000);
    });

    // Reinitialize editor when window is resized
    window.addEventListener('resize', function () {
        if (editor) {
            editor.layout();
        }
    });
</script>

