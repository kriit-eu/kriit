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
        <div class="footer">
            <a class="btn btn-secondary" href="exercises">
                <i class="bi bi-arrow-left"></i>
                Ülesannete loendisse
            </a>
            <div id="timer" class="timer<?= (isset($this->elapsedTime) && $this->elapsedTime >= 300) ? ' overdue' : '' ?>"><?= gmdate("i:s", $this->elapsedTime ?? 0) ?></div>
            <button class="btn btn-success" onclick="validateSolution()">
                <i class="bi bi-check"></i>
                Kontrolli lahendust
            </button>
        </div>
        <style>
            .timer { min-width: 48px; display: inline-block; text-align: left; transition: color 0.3s; }
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
        z-index: 10;
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
                // Create Monaco Editor instance with basic configuration
                editor = monaco.editor.create(document.getElementById('editor'), {
                    value: <?= json_encode($exercise['exerciseInitialCode']) ?>,
                    language: 'html',
                    theme: 'vs-dark',
                    fontSize: 14,
                    automaticLayout: true,
                    wordWrap: 'on',
                    lineNumbers: 'on',
                    minimap: { enabled: false }
                });

                // Set up change listeners
                editor.onDidChangeModelContent(function() {
                    updatePreview();
                });

                // Initialize Emmet for HTML
                if (typeof emmetMonaco !== 'undefined') {
                    emmetMonaco.emmetHTML(monaco, ['html', 'php']);
                    console.log('Emmet initialized successfully for Monaco Editor');
                } else {
                    console.warn('Emmet library not loaded');
                }

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

        // Toggle the drawer on timer click
        document.querySelector('.timer').addEventListener('click', function () {
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

