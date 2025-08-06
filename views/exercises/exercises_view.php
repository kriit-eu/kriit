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

    /* Hide Monaco editor underlines on HTML tags */
    /* Turn off the underline that the theme puts on tokens */
    .monaco-editor .mtku {
        text-decoration: none !important;
        text-underline-position: initial !important;
    }

    /* Also kill the underline that appears on Ctrl/Cmd-hover */
    .monaco-editor .goto-definition-link,
    .monaco-editor .detected-link {
        border-bottom: none !important;
        text-decoration: none !important;
    }

</style>


<!-- Include Prism.js CSS -->
<link href="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/themes/prism.min.css" rel="stylesheet"/>

<!-- Include Prism.js -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/prism.min.js"></script>
<!-- Include Prism.js for JavaScript language -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/components/prism-javascript.min.js"></script>
<!-- Include Monaco Editor (VS Code Editor) -->
<script src="https://cdn.jsdelivr.net/npm/monaco-editor@0.44.0/min/vs/loader.js"></script>
<!-- Include Emmet for Monaco Editor -->
<script src="https://cdn.jsdelivr.net/npm/emmet-monaco-es@5.3.0/dist/emmet-monaco.min.js"></script>
<script>
    let editor;

    function initEditor() {
        if (!editor) {
            // Configure Monaco Editor paths
            require.config({ 
                paths: { 
                    'vs': 'https://cdn.jsdelivr.net/npm/monaco-editor@0.44.0/min/vs' 
                }
            });

            require(['vs/editor/editor.main'], function() {
                // Define custom theme without underlines for HTML tags
                monaco.editor.defineTheme('vs-dark-no-underline', {
                    base: 'vs-dark',
                    inherit: true,
                    rules: [
                        // Remove underline from HTML tags
                        { token: 'entity.name.tag', fontStyle: '', foreground: '569CD6' },
                        { token: 'entity.name.tag.html', fontStyle: '', foreground: '569CD6' },
                        { token: 'meta.tag.html', fontStyle: '', foreground: '569CD6' },
                        { token: 'punctuation.definition.tag', fontStyle: '', foreground: '808080' },
                        { token: 'punctuation.definition.tag.html', fontStyle: '', foreground: '808080' },
                        { token: 'punctuation.definition.tag.begin.html', fontStyle: '', foreground: '808080' },
                        { token: 'punctuation.definition.tag.end.html', fontStyle: '', foreground: '808080' },
                        { token: 'entity.other.attribute-name', fontStyle: '', foreground: '9CDCFE' },
                        { token: 'entity.other.attribute-name.html', fontStyle: '', foreground: '9CDCFE' },
                        { token: 'string.quoted.double.html', fontStyle: '', foreground: 'CE9178' },
                        { token: 'string.quoted.single.html', fontStyle: '', foreground: 'CE9178' }
                    ],
                    colors: {}
                });

                // Create Monaco Editor instance with enhanced VS Code features
                editor = monaco.editor.create(document.getElementById('editor'), {
                    value: <?= json_encode($exercise['exerciseInitialCode']) ?>,
                    language: 'html',
                    theme: 'vs-dark-no-underline',
                    fontSize: 14,
                    fontFamily: 'Consolas, "Courier New", monospace',
                    automaticLayout: true,
                    minimap: { enabled: true, maxColumn: 120 },
                    scrollBeyondLastLine: false,
                    wordWrap: 'on',
                    lineNumbers: 'on',
                    renderWhitespace: 'selection',
                    cursorBlinking: 'blink',
                    cursorSmoothCaretAnimation: true,
                    smoothScrolling: true,
                    mouseWheelZoom: true,
                    folding: true,
                    foldingStrategy: 'auto',
                    showFoldingControls: 'always',
                    bracketPairColorization: { enabled: true },
                    guides: {
                        bracketPairs: true,
                        bracketPairsHorizontal: true,
                        highlightActiveBracketPair: true,
                        indentation: true
                    },
                    // Enhanced autocompletion and IntelliSense
                    quickSuggestions: {
                        other: true,
                        comments: true,
                        strings: true
                    },
                    suggestOnTriggerCharacters: true,
                    acceptSuggestionOnEnter: 'off',
                    acceptSuggestionOnCommitCharacter: true,
                    tabCompletion: 'on',
                    wordBasedSuggestions: true,
                    parameterHints: { 
                        enabled: true,
                        cycle: true 
                    },
                    // Code formatting and editing
                    autoIndent: 'full',
                    formatOnPaste: true,
                    formatOnType: true,
                    autoClosingBrackets: 'always',
                    autoClosingQuotes: 'always',
                    autoSurround: 'languageDefined',
                    // Enhanced editing features
                    multiCursorModifier: 'ctrlCmd',
                    wordSeparators: '`~!@#$%^&*()-=+[{]}\\|;:\'",.<>/?',
                    links: false,
                    colorDecorators: true,
                    lightbulb: { enabled: true },
                    // Disable various decorations that could show underlines
                    occurrencesHighlight: false,
                    selectionHighlight: false,
                    renderLineHighlight: 'none',
                    codeActionsOnSave: {},
                    // Selection and find features
                    find: {
                        seedSearchStringFromSelection: 'always',
                        autoFindInSelection: 'never'
                    },
                    // Scrollbar settings
                    scrollbar: {
                        vertical: 'auto',
                        horizontal: 'auto',
                        verticalScrollbarSize: 10,
                        horizontalScrollbarSize: 10
                    }
                });

                // Add custom HTML/CSS/JS snippets and Emmet-like completion for better autocompletion
                monaco.languages.registerCompletionItemProvider('html', {
                    triggerCharacters: ['>', '*', '.', '#', '[', '(', '{'],
                    provideCompletionItems: function(model, position) {
                        // Get the current line up to the cursor
                        const textUntilPosition = model.getValueInRange({
                            startLineNumber: position.lineNumber,
                            startColumn: 1,
                            endLineNumber: position.lineNumber,
                            endColumn: position.column
                        });
                        const prefix = textUntilPosition.trim().toLowerCase();

                        // Input snippet
                        const inputSnippet = {
                            label: 'input',
                            kind: monaco.languages.CompletionItemKind.Snippet,
                            insertText: '<input type="text" name="${1:name}" placeholder="${2:Enter text}">',
                            insertTextRules: monaco.languages.CompletionItemInsertTextRule.InsertAsSnippet,
                            documentation: 'Input field (text)'
                        };

                        const otherSnippets = [
                            {
                                label: 'html5-template',
                                kind: monaco.languages.CompletionItemKind.Snippet,
                                insertText: [
                                    '<!DOCTYPE html>',
                                    '<html lang="en">',
                                    '<head>',
                                    '    <meta charset="UTF-8">',
                                    '    <meta name="viewport" content="width=device-width, initial-scale=1.0">',
                                    '    <title>${1:Document}</title>',
                                    '</head>',
                                    '<body>',
                                    '    ${2}',
                                    '</body>',
                                    '</html>'
                                ].join('\n'),
                                insertTextRules: monaco.languages.CompletionItemInsertTextRule.InsertAsSnippet,
                                documentation: 'HTML5 template structure'
                            },
                            {
                                label: 'div-class',
                                kind: monaco.languages.CompletionItemKind.Snippet,
                                insertText: '<div class="${1:className}">\n    ${2}\n</div>',
                                insertTextRules: monaco.languages.CompletionItemInsertTextRule.InsertAsSnippet,
                                documentation: 'Div with class attribute'
                            },
                            {
                                label: 'button-onclick',
                                kind: monaco.languages.CompletionItemKind.Snippet,
                                insertText: '<button onclick="${1:function}()">${2:Button Text}</button>',
                                insertTextRules: monaco.languages.CompletionItemInsertTextRule.InsertAsSnippet,
                                documentation: 'Button with onclick event'
                            },
                            {
                                label: 'form-basic',
                                kind: monaco.languages.CompletionItemKind.Snippet,
                                insertText: [
                                    '<form action="${1:#}" method="${2:post}">',
                                    '    <input type="${3:text}" name="${4:name}" placeholder="${5:Enter text}">',
                                    '    <button type="submit">${6:Submit}</button>',
                                    '</form>'
                                ].join('\n'),
                                insertTextRules: monaco.languages.CompletionItemInsertTextRule.InsertAsSnippet,
                                documentation: 'Basic form structure'
                            }
                        ];

                        // Show input snippet for <i, <in, <input, i, in, input
                        const inputPrefixes = ['<i', '<in', '<input', 'i', 'in', 'input'];
                        let suggestions = [];
                        if (inputPrefixes.some(p => prefix.startsWith(p))) {
                            suggestions.push(inputSnippet);
                        }

                        // Always add all other snippets
                        suggestions = suggestions.concat(otherSnippets);

                        // If not matching input, still show input snippet at the end for discoverability
                        if (!suggestions.includes(inputSnippet)) {
                            suggestions.push(inputSnippet);
                        }

                        // Emmet-like abbreviation detection (no < required)
                        const emmetPattern = /^[a-zA-Z0-9\.\#\[\]>\*\(\)\{\}\$\-]+$/;
                        if (emmetPattern.test(prefix)) {
                            suggestions.unshift({
                                label: `Emmet: Expand '${textUntilPosition.trim()}'`,
                                kind: monaco.languages.CompletionItemKind.Snippet,
                                insertText: textUntilPosition.trim(),
                                insertTextRules: monaco.languages.CompletionItemInsertTextRule.InsertAsSnippet,
                                documentation: 'Expand Emmet abbreviation',
                                command: {
                                    id: 'editor.emmet.action.expandAbbreviation',
                                    arguments: []
                                }
                            });
                        }

                        return { suggestions: suggestions };
                    }
                });

                // Initialize Emmet for HTML/CSS abbreviation expansion
                if (typeof emmetMonaco !== 'undefined') {
                    emmetMonaco.emmetHTML(monaco, ['html', 'php']);
                    emmetMonaco.emmetCSS(monaco, ['css']);
                    

                    // Only initialize Emmet for HTML and CSS
                    // Tab will use Monaco's default: autocomplete, snippet, or Emmet

                    // Add more Emmet shortcuts
                    editor.addAction({
                        id: 'emmet-wrap-with-abbreviation',
                        label: 'Emmet: Wrap with Abbreviation',
                        keybindings: [monaco.KeyMod.Shift | monaco.KeyMod.CtrlCmd | monaco.KeyCode.KeyA],
                        run: function(ed) {
                            const action = ed.getAction('editor.emmet.action.wrapWithAbbreviation');
                            if (action) action.run();
                        }
                    });

                    editor.addAction({
                        id: 'emmet-balance-outward',
                        label: 'Emmet: Balance Outward',
                        keybindings: [monaco.KeyMod.CtrlCmd | monaco.KeyCode.KeyD],
                        run: function(ed) {
                            const action = ed.getAction('editor.emmet.action.balanceOut');
                            if (action) action.run();
                        }
                    });

                    console.log('Emmet initialized successfully for Monaco Editor');
                } else {
                    console.warn('Emmet library not loaded');
                }

                // Enhanced error detection and validation
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

                // Add CSS to remove underlines after Monaco is fully loaded
                setTimeout(() => {
                    const style = document.createElement('style');
                    style.textContent = `
                        /* CRITICAL: Override Monaco's internal .mtku class that creates underlines */
                        .monaco-editor .mtku,
                        .monaco-editor span.mtku,
                        .monaco-editor .view-line span.mtku,
                        .monaco-editor .view-lines .view-line span.mtku,
                        .mtku {
                            text-decoration: none !important;
                            text-underline-position: initial !important;
                        }
                        
                        /* Also override the combined classes like mtks.mtku */
                        .monaco-editor .mtks.mtku,
                        .monaco-editor span.mtks.mtku,
                        .mtks.mtku {
                            text-decoration: line-through !important;
                            text-underline-position: initial !important;
                        }
                        
                        /* Ultra-aggressive underline removal - target all possible sources */
                        .monaco-editor .mtk8,
                        .monaco-editor .mtk4,
                        .monaco-editor .mtk10,
                        .monaco-editor .mtk1,
                        .monaco-editor .mtk5,
                        .monaco-editor .mtk7,
                        .monaco-editor .mtk9,
                        .monaco-editor .mtk6,
                        .monaco-editor span[class*="mtk"],
                        .monaco-editor .view-line span,
                        .monaco-editor .view-lines .view-line span {
                            text-decoration: none !important;
                            text-decoration-line: none !important;
                            text-decoration-color: transparent !important;
                            text-decoration-style: none !important;
                            text-decoration-thickness: 0px !important;
                            text-underline-position: initial !important;
                            border-bottom: none !important;
                            box-shadow: none !important;
                            background-image: none !important;
                        }
                        
                        /* Target Monaco's potential pseudo-elements */
                        .monaco-editor span[class*="mtk"]:before,
                        .monaco-editor span[class*="mtk"]:after,
                        .monaco-editor .view-line span:before,
                        .monaco-editor .view-line span:after {
                            content: none !important;
                            display: none !important;
                            border-bottom: none !important;
                            text-decoration: none !important;
                        }
                        
                        /* Remove any possible Monaco decorations */
                        .monaco-editor span[class*="mtku"],
                        .monaco-editor .token.mtku,
                        .monaco-editor .detected-link,
                        .monaco-editor .goto-definition-link {
                            text-decoration: none !important;
                            text-decoration-line: none !important;
                            text-decoration-color: transparent !important;
                            border-bottom: none !important;
                            box-shadow: none !important;
                            background-image: none !important;
                        }
                        
                        /* Override any inline styles */
                        .monaco-editor [style*="text-decoration"],
                        .monaco-editor [style*="underline"] {
                            text-decoration: none !important;
                            text-decoration-line: none !important;
                            text-decoration-color: transparent !important;
                        }
                        
                        /* Force override browser default link styling */
                        .monaco-editor * {
                            text-decoration: none !important;
                        }
                        
                        /* Specific override for the blue color elements that might show underlines */
                        .monaco-editor span[style*="rgb(86, 156, 214)"],
                        .monaco-editor span[style*="rgb(156, 220, 254)"] {
                            text-decoration: none !important;
                            text-decoration-line: none !important;
                            text-decoration-color: transparent !important;
                            border-bottom: none !important;
                        }
                    `;
                    document.head.appendChild(style);
                    
                    // More aggressive DOM manipulation
                    setTimeout(() => {
                        if (editor) {
                            // Force layout and re-render
                            editor.layout();
                            
                            // Find and directly modify any elements that might have underlines
                            const allSpans = document.querySelectorAll('.monaco-editor .view-line span');
                            allSpans.forEach(span => {
                                span.style.setProperty('text-decoration', 'none', 'important');
                                span.style.setProperty('text-decoration-line', 'none', 'important');
                                span.style.setProperty('text-decoration-color', 'transparent', 'important');
                                span.style.setProperty('text-underline-position', 'initial', 'important');
                                span.style.setProperty('border-bottom', 'none', 'important');
                                span.style.setProperty('box-shadow', 'none', 'important');
                            });
                            
                            // Specifically target any .mtku elements and remove the class or override
                            const mtkuElements = document.querySelectorAll('.monaco-editor .mtku');
                            mtkuElements.forEach(el => {
                                el.style.setProperty('text-decoration', 'none', 'important');
                                el.style.setProperty('text-underline-position', 'initial', 'important');
                            });
                            
                            // Trigger a content refresh to apply changes
                            const currentValue = editor.getValue();
                            editor.setValue(currentValue);
                        }
                    }, 100);
                }, 1000);

                // Robustly disable ALL paste (keyboard, context menu, etc)
                // 1. Override Monaco's paste action
                editor.addAction({
                    id: 'disable-paste',
                    label: 'Disable Paste',
                    keybindings: [
                        monaco.KeyMod.CtrlCmd | monaco.KeyCode.KeyV,
                        monaco.KeyMod.Shift | monaco.KeyCode.Insert
                    ],
                    run: function() {
                        // Show a subtle notification instead of silent failure
                        monaco.editor.setModelMarkers(editor.getModel(), 'paste-blocked', [{
                            startLineNumber: 1,
                            startColumn: 1,
                            endLineNumber: 1,
                            endColumn: 1,
                            message: 'Paste operation is disabled for this exercise',
                            severity: monaco.MarkerSeverity.Info
                        }]);
                        // Show paste disabled banner
                        var banner = document.getElementById('paste-banner');
                        if (banner) {
                            // Show as block and add margin to push content
                            banner.style.display = 'block';
                            // Force reflow to allow transition
                            void banner.offsetWidth;
                            banner.style.opacity = '1';
                            clearTimeout(banner._hideTimeout);
                            banner._hideTimeout = setTimeout(function() {
                                banner.style.opacity = '0';
                                // Wait for transition to finish before hiding
                                setTimeout(function() {
                                    banner.style.display = 'none';
                                }, 500);
                            }, 3500);
                        }
                        // Clear the marker after 3 seconds
                        setTimeout(() => {
                            monaco.editor.setModelMarkers(editor.getModel(), 'paste-blocked', []);
                        }, 3000);
                        return null;
                    }
                });
    // Show paste banner on global Ctrl+V or Shift+Insert
    document.addEventListener('keydown', function(e) {
        // Ctrl+V or Cmd+V
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
        // Shift+Insert
        if (e.shiftKey && e.key === 'Insert') {
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

                // 2. Add helpful shortcuts and commands
                editor.addAction({
                    id: 'format-document',
                    label: 'Format Document',
                    keybindings: [
                        monaco.KeyMod.Shift | monaco.KeyMod.Alt | monaco.KeyCode.KeyF
                    ],
                    run: function() {
                        editor.getAction('editor.action.formatDocument').run();
                    }
                });

                editor.addAction({
                    id: 'toggle-word-wrap',
                    label: 'Toggle Word Wrap',
                    keybindings: [
                        monaco.KeyMod.Alt | monaco.KeyCode.KeyZ
                    ],
                    run: function() {
                        const currentWrap = editor.getOption(monaco.editor.EditorOption.wordWrap);
                        editor.updateOptions({ 
                            wordWrap: currentWrap === 'on' ? 'off' : 'on' 
                        });
                    }
                });

                // 3. Enhanced paste protection at DOM level
                const editorDom = editor.getDomNode();
                if (editorDom) {
                    // Block all paste events with detailed logging
                    editorDom.addEventListener('paste', function(e) {
                        console.log('Paste attempt blocked at DOM level');
                        e.preventDefault();
                        e.stopPropagation();
                        e.stopImmediatePropagation();
                        // Show paste disabled banner
                        var banner = document.getElementById('paste-banner');
                        if (banner) {
                            banner.style.display = 'block';
                            clearTimeout(banner._hideTimeout);
                            banner._hideTimeout = setTimeout(function() {
                                banner.style.display = 'none';
                            }, 3500);
                        }
                        return false;
                    }, true);

                    // Block context menu completely
                    editorDom.addEventListener('contextmenu', function(e) {
                        console.log('Context menu blocked');
                        e.preventDefault();
                        e.stopPropagation();
                        return false;
                    }, true);

                    // Block drag and drop operations
                    ['dragover', 'dragenter', 'drop'].forEach(eventType => {
                        editorDom.addEventListener(eventType, function(e) {
                            console.log(`${eventType} blocked`);
                            e.preventDefault();
                            e.stopPropagation();
                            return false;
                        }, true);
                    });
                }

                // 4. Advanced clipboard API blocking
                if (navigator.clipboard) {
                    const originalReadText = navigator.clipboard.readText;
                    const originalRead = navigator.clipboard.read;
                    
                    navigator.clipboard.readText = function() {
                        console.log('Clipboard readText blocked');
                        return Promise.reject(new Error('Clipboard access blocked'));
                    };
                    
                    navigator.clipboard.read = function() {
                        console.log('Clipboard read blocked');
                        return Promise.reject(new Error('Clipboard access blocked'));
                    };
                }

                // 5. Intelligent content monitoring with advanced detection
                let lastContent = editor.getValue();
                let lastChangeTime = Date.now();
                let isReverting = false; // Flag to prevent infinite loop
                
                editor.onDidChangeModelContent(function(e) {
                    // Skip processing if we're currently reverting
                    if (isReverting) {
                        return;
                    }
                    
                    const currentTime = Date.now();
                    const timeDiff = currentTime - lastChangeTime;
                    const currentContent = editor.getValue();
                    
                    // Advanced paste detection algorithms
                    const changes = e.changes;
                    let suspiciousActivity = false;
                    
                    for (let change of changes) {
                        // Skip detection for single character changes (like Enter key)
                        if (change.text.length <= 1) {
                            continue;
                        }
                        
                        // Skip detection for auto-formatting changes (Enter + indentation)
                        if (change.text.includes('\n') && change.text.length < 10) {
                            continue;
                        }
                        
                        // Detect large text insertions (potential paste)
                        if (change.text.length > 50) {
                            suspiciousActivity = true;
                            break;
                        }
                        
                        // Detect multiple rapid changes (potential paste) - but allow short formatting
                        if (timeDiff < 100 && change.text.length > 20) {
                            suspiciousActivity = true;
                            break;
                        }
                        
                        // Detect formatted content (potential paste from rich sources) - be more lenient
                        if (change.text.includes('\t\t\t') || change.text.includes('        ')) {
                            const indentCount = (change.text.match(/\t\t\t/g) || []).length + 
                                              (change.text.match(/        /g) || []).length;
                            if (indentCount > 2) {
                                suspiciousActivity = true;
                                break;
                            }
                        }
                    }
                    
                    if (suspiciousActivity) {
                        console.log('Suspicious paste activity detected, reverting');
                        
                        // Set flag to prevent infinite loop
                        isReverting = true;
                        
                        // Revert to last content
                        editor.setValue(lastContent);
                        
                        // Show notification
                        monaco.editor.setModelMarkers(editor.getModel(), 'paste-detected', [{
                            startLineNumber: 1,
                            startColumn: 1,
                            endLineNumber: 1,
                            endColumn: 1,
                            message: 'Large text insertion detected and reverted. Please type your code manually.',
                            severity: monaco.MarkerSeverity.Warning
                        }]);
                        
                        setTimeout(() => {
                            monaco.editor.setModelMarkers(editor.getModel(), 'paste-detected', []);
                            // Reset flag after revert is complete
                            isReverting = false;
                        }, 5000);
                        
                        return;
                    }
                    
                    lastContent = currentContent;
                    lastChangeTime = currentTime;
                    updatePreview();
                });

                // 6. Add live code validation and hints
                editor.onDidChangeModelContent(function() {
                    setTimeout(() => {
                        const model = editor.getModel();
                        const content = model.getValue();
                        const markers = [];
                        
                        // Basic HTML validation
                        if (content.includes('<script>') && !content.includes('<\/script>')) {
                            markers.push({
                                startLineNumber: 1,
                                startColumn: 1,
                                endLineNumber: 1,
                                endColumn: 1,
                                message: 'Unclosed script tag detected',
                                severity: monaco.MarkerSeverity.Warning
                            });
                        }
                        
                        // Check for common mistakes
                        if (content.includes('<img') && !content.includes('alt=')) {
                            markers.push({
                                startLineNumber: 1,
                                startColumn: 1,
                                endLineNumber: 1,
                                endColumn: 1,
                                message: 'Consider adding alt attribute to images for accessibility',
                                severity: monaco.MarkerSeverity.Info
                            });
                        }
                        
                        monaco.editor.setModelMarkers(model, 'html-validation', markers);
                    }, 500);
                });

                // Enhanced Debug: Comprehensive Monaco styling inspection
                setTimeout(() => {
                    console.log('=== ENHANCED MONACO DEBUG ===');
                    
                    // 1. Check Monaco's internal theme and token definitions
                    const monacoTheme = monaco.editor.getTheme ? monaco.editor.getTheme('vs-dark-no-underline') : null;
                    console.log('Monaco theme definition:', monacoTheme);
                    
                    // 2. Inspect all Monaco token classes
                    const allTokens = document.querySelectorAll('.monaco-editor .view-line span[class*="mtk"]');
                    console.log('Total Monaco tokens found:', allTokens.length);
                    
                    // Group tokens by class
                    const tokensByClass = {};
                    allTokens.forEach(token => {
                        const className = token.className;
                        if (!tokensByClass[className]) {
                            tokensByClass[className] = [];
                        }
                        tokensByClass[className].push(token);
                    });
                    
                    console.log('Token classes found:', Object.keys(tokensByClass));
                    
                    // 3. Check for specific HTML tag tokens and their styling
                    Object.keys(tokensByClass).forEach(className => {
                        const tokens = tokensByClass[className];
                        const firstToken = tokens[0];
                        const style = getComputedStyle(firstToken);
                        
                        // Log tokens that might be HTML tags
                        if (firstToken.textContent && /^[a-z]+$/i.test(firstToken.textContent.trim())) {
                            console.log(`Class ${className} (${tokens.length} tokens):`, {
                                text: firstToken.textContent,
                                color: style.color,
                                textDecoration: style.textDecoration,
                                borderBottom: style.borderBottom,
                                textUnderlinePosition: style.textUnderlinePosition,
                                element: firstToken
                            });
                        }
                    });
                    
                    // 4. Look for CSS pseudo-elements that might create underlines
                    const editorContainer = document.querySelector('.monaco-editor');
                    if (editorContainer) {
                        const afterStyle = getComputedStyle(editorContainer, '::after');
                        const beforeStyle = getComputedStyle(editorContainer, '::before');
                        console.log('Editor ::after pseudo:', afterStyle.content, afterStyle.borderBottom);
                        console.log('Editor ::before pseudo:', beforeStyle.content, beforeStyle.borderBottom);
                    }
                    
                    // 5. Check for Monaco's link decorations
                    const linkDecorations = document.querySelectorAll('.monaco-editor .detected-link, .monaco-editor .goto-definition-link');
                    console.log('Link decorations found:', linkDecorations.length);
                    linkDecorations.forEach((link, i) => {
                        console.log(`Link decoration ${i}:`, link, getComputedStyle(link));
                    });
                    
                    // 6. Check Monaco editor configuration for link-related settings
                    const editorOptions = editor.getOptions();
                    console.log('Editor links option:', editorOptions.links);
                    console.log('Editor hover options:', editorOptions.hover);
                    
                    // 7. Enhanced element inspector
                    window.inspectElement = function(element) {
                        const style = getComputedStyle(element);
                        console.log('=== ELEMENT INSPECTION ===');
                        console.log('Element:', element);
                        console.log('Tag name:', element.tagName);
                        console.log('Classes:', element.className);
                        console.log('Text content:', element.textContent);
                        console.log('All CSS properties:');
                        
                        // Log all computed styles that might affect appearance
                        const relevantProps = [
                            'textDecoration', 'textUnderlinePosition', 'textDecorationColor',
                            'textDecorationStyle', 'textDecorationThickness', 'borderBottom',
                            'borderBottomColor', 'borderBottomStyle', 'borderBottomWidth',
                            'color', 'backgroundColor', 'boxShadow', 'outline'
                        ];
                        
                        relevantProps.forEach(prop => {
                            const value = style[prop];
                            if (value && value !== 'none' && value !== 'initial' && value !== '0px') {
                                console.log(`  ${prop}: ${value}`);
                            }
                        });
                        
                        // Check for any Monaco-specific attributes
                        console.log('Data attributes:', [...element.attributes].filter(attr => attr.name.startsWith('data-')));
                        
                        console.log('=== END INSPECTION ===');
                    };
                    
                    // 8. Add click handler to automatically inspect clicked elements
                    document.querySelector('.monaco-editor').addEventListener('click', function(e) {
                        if (e.target.tagName === 'SPAN' && e.target.className.includes('mtk')) {
                            console.log('Auto-inspecting clicked token:', e.target.textContent);
                            window.inspectElement(e.target);
                        }
                    });
                    
                    console.log('=== ENHANCED DEBUG COMPLETE ===');
                    console.log('Instructions: Click on any tag in Monaco to auto-inspect, or right-click and use inspectElement($0)');
                }, 2000);

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

