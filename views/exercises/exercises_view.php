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
                Tagasi ülesannete loendisse
            </a>
            <div class="timer"><?php require 'templates/partials/timer.php' ?></div>
            <button class="btn btn-success" onclick="validateSolution()">
                <i class="bi bi-check"></i>
                Kontrolli lahendust
            </button>
        </div>

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
<script src="https://cdnjs.cloudflare.com/ajax/libs/ace/1.23.0/ace.js"></script>
<script>
    let editor;

    function initEditor() {
        if (!editor) {
            editor = ace.edit("editor");
            editor.setTheme("ace/theme/monokai");
            editor.session.setMode("ace/mode/html");
            editor.setValue(<?= json_encode($exercise['exerciseInitialCode']) ?>);


            // Robustly disable paste (keyboard, context menu, etc)
            // 1. Override Ace's paste command
            editor.commands.addCommand({
                name: 'disablePaste',
                bindKey: {win: 'Ctrl-V', mac: 'Command-V'},
                exec: function() {},
                readOnly: true // false = enable in readOnly mode
            });

            // 2. Block native paste event on the editor's textarea
            setTimeout(function() {
                var textarea = editor.textInput.getElement();
                if (textarea) {
                    textarea.addEventListener('paste', function(e) {
                        e.preventDefault();
                        return false;
                    });
                }
            }, 0);

            // Update preview on editor change
            editor.session.on('change', function () {
                updatePreview();
            });
        }
        editor.resize();
    }

    function updatePreview() {
        const previewIframe = document.getElementById('preview-iframe');
        const iframeDoc = previewIframe.contentDocument || previewIframe.contentWindow.document;
        iframeDoc.open();
        iframeDoc.write(editor.getValue());
        iframeDoc.close();
    }

    function validateSolution() {
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
        updatePreview();

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
    });

    // Reinitialize editor when window is resized
    window.addEventListener('resize', function () {
        initEditor();
    });
</script>

