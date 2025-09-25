<!-- Include Ace editor library -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/ace/1.4.12/ace.js"></script>

<div class="container">
    <div class="custom-container" style="max-width:1300px;margin:0 auto;">
        <div class="row align-items-center">
            <div class="col">
                <h1>Ülesanded</h1>
            </div>
            <div class="col-auto">
                <button id="add-new-exercise" class="btn btn-success">Lisa uus ülesanne</button>
            </div>
        </div>
    <div class="exercises-container">
        <?php foreach ($exercises as $exercise): ?>
            <section class="exercise-card">
                <header>
                    <div class="input-group mb-3">
                        <span class="input-group-text" id="basic-addon1"><?= $exercise['exerciseId'] ?></span>
                        <input type="text" id="exercise-name-<?= $exercise['exerciseId'] ?>" class="form-control"
                               aria-label="name" aria-describedby="exercise-name"
                               value="<?= htmlspecialchars($exercise['exerciseName']) ?>"
                               placeholder="Enter exercise name">
                    </div>
                </header>

                <div class="exercise-body">
                    <!-- Instructions Section: Editor and Preview Side-by-Side -->
                    <div class="editor-preview-container horizontal-layout">
                        <div class="editor-container half-width">
                            <div id="editor-instructions-<?= $exercise['exerciseId'] ?>" class="ace-editor"></div>
                        </div>
                        <div class="preview-container">
                            <iframe id="preview-instructions-<?= $exercise['exerciseId'] ?>"
                                    class="preview-iframe"></iframe>
                        </div>
                    </div>

                    <!-- Code Section: Editor and Preview Side-by-Side -->
                    <div class="editor-preview-container horizontal-layout">
                        <div class="editor-container half-width">
                            <div id="editor-initial-code-<?= $exercise['exerciseId'] ?>" class="ace-editor"></div>
                        </div>
                        <div class="preview-container">
                            <iframe id="preview-<?= $exercise['exerciseId'] ?>" class="preview-iframe"></iframe>
                        </div>
                    </div>

                    <!-- Validation Function Section: Full Width -->
                    <div class="editor-container full-width">
                        <div id="editor-validation-function-<?= $exercise['exerciseId'] ?>" class="ace-editor"></div>
                    </div>
                </div>

                <footer class="exercise-footer">
                    <a href="#" class="btn btn-danger delete-exercise" data-id="<?= $exercise['exerciseId'] ?>">
                        Kustuta
                    </a>
                    <button class="btn btn-primary save-button" data-id="<?= $exercise['exerciseId'] ?>">Salvesta</button>
                    <!--
                    To use the per-user detail view, you need to provide a userId. Example below assumes you have $user['userId'] available in context:
                    <a href="/views/admin/admin_exercise_detail.php?userId=<?= $user['userId'] ?>" target="_blank" class="btn btn-info ms-2">Vaata</a>
                    -->
                </footer>
            </section>
        <?php endforeach; ?>
    </div>

</div>


<!-- Initialize Ace editor for each div -->
<script>


    document.addEventListener('DOMContentLoaded', function () {

        const editorInitialCodes = {};
        const editorValidationFunctions = {};
        const editorInstructions = {};
        const titleChanged = {}; // Track changes to the title

        <?php foreach ($exercises as $exercise): ?>
        initializeExercise(
            '<?= $exercise['exerciseId'] ?>',
            `<?= addcslashes(trim($exercise['exerciseInstructions']), "`\"'\\") ?>`,
            `<?= addcslashes(trim($exercise['exerciseInitialCode']), "`\"'\\") ?>`,
            `<?= addcslashes(trim($exercise['exerciseValidationFunction']), "`\"'\\") ?>`);
        <?php endforeach; ?>

        document.getElementById('add-new-exercise').addEventListener('click', function () {
            const newExerciseId = 'new-' + Date.now();
            const newExerciseHTML = `
            <section class="exercise-card" id="exercise-card-${newExerciseId}">
                <header>
                    <div class="input-group mb-3">
                        <span class="input-group-text" id="basic-addon1">New</span>
                        <input type="text" id="exercise-name-${newExerciseId}" class="form-control"
                               aria-label="name" aria-describedby="exercise-name"
                               value=""
                               placeholder="Enter exercise name">
                    </div>
                </header>
                <div class="exercise-body">
                    <!-- Instructions Section: Editor and Preview Side-by-Side -->
                    <div class="editor-preview-container horizontal-layout">
                        <div class="editor-container half-width">
                            <div id="editor-instructions-${newExerciseId}" class="ace-editor"></div>
                        </div>
                        <div class="preview-container">
                            <iframe id="preview-instructions-${newExerciseId}" class="preview-iframe"></iframe>
                        </div>
                    </div>
                    <!-- Code Section: Editor and Preview Side-by-Side -->
                    <div class="editor-preview-container horizontal-layout">
                        <div class="editor-container half-width">
                            <div id="editor-initial-code-${newExerciseId}" class="ace-editor"></div>
                        </div>
                        <div class="preview-container">
                            <iframe id="preview-${newExerciseId}" class="preview-iframe"></iframe>
                        </div>
                    </div>
                    <!-- Validation Function Section: Full Width -->
                    <div class="editor-container full-width">
                        <div id="editor-validation-function-${newExerciseId}" class="ace-editor"></div>
                    </div>
                </div>
                <footer class="exercise-footer">
                    <a href="#" class="btn btn-danger delete-exercise" data-id="${newExerciseId}">
                        Kustuta
                    </a>
                    <button class="btn btn-primary save-button" data-id="${newExerciseId}">Salvesta</button>
                </footer>
            </section>
        `;

            const exercisesContainer = document.querySelector('.exercises-container');
            exercisesContainer.insertAdjacentHTML('beforeend', newExerciseHTML);
            initializeExercise(newExerciseId, '', '', '');

            const newExerciseElement = document.getElementById('exercise-card-' + newExerciseId);

            // Scroll to the new exercise card
            newExerciseElement.scrollIntoView({behavior: 'smooth'});

            // Add highlight effect
            newExerciseElement.classList.add('new-card-highlight');

            // Remove the highlight after a delay
            setTimeout(() => {
                newExerciseElement.classList.remove('new-card-highlight');
            }, 4000);
        });

        function initializeExercise(exerciseId, instructions, initialCode, validationFunction) {
            // Use default validation function if it's not provided
            const defaultValidationFunction = `function validate(){\n    return false;\n}`;
            validationFunction = validationFunction || defaultValidationFunction;

            // Initialize the instructions editor with content
            editorInstructions[exerciseId] = ace.edit("editor-instructions-" + exerciseId);
            initializeAceEditor(editorInstructions[exerciseId], 'html', instructions, 'preview-instructions-' + exerciseId);

            // Initialize the initial code editor with content
            editorInitialCodes[exerciseId] = ace.edit("editor-initial-code-" + exerciseId);
            initializeAceEditor(editorInitialCodes[exerciseId], 'html', initialCode, 'preview-' + exerciseId);

            // Initialize the validation function editor with content
            editorValidationFunctions[exerciseId] = ace.edit("editor-validation-function-" + exerciseId);
            initializeAceEditor(editorValidationFunctions[exerciseId], 'javascript', validationFunction);

            // Track changes for the title
            titleChanged[exerciseId] = false;
            document.getElementById('exercise-name-' + exerciseId).addEventListener('input', function () {
                if (!titleChanged[exerciseId]) {
                    titleChanged[exerciseId] = true;
                    this.classList.add('editor-changed');
                    enableSaveButton(this.closest('.exercise-card').querySelector('.save-button'));
                }
            });

            // Track validation on the initial code editor, passing the correct previewFrameId
            trackValidation(editorInitialCodes[exerciseId], editorValidationFunctions[exerciseId], 'preview-' + exerciseId);

            // Disable the save button initially
            document.querySelector('.save-button[data-id="' + exerciseId + '"]').disabled = true;

            // Attach event listener to save button
            document.querySelector('.save-button[data-id="' + exerciseId + '"]').addEventListener('click', function () {
                saveContent(exerciseId, editorInstructions[exerciseId], editorInitialCodes[exerciseId], editorValidationFunctions[exerciseId], titleChanged);
            });

            document.querySelector('.delete-exercise[data-id="' + exerciseId + '"]').addEventListener('click', function (event) {
                event.preventDefault();
                const exerciseId = this.getAttribute('data-id');
                if (confirm('Oled kindel, et soovid kustutada ülesande ' + exerciseId + '?')) {
                    ajaxDeleteExercise(exerciseId, this.closest('.exercise-card'));
                }
            });
        }
    });


    function ajaxDeleteExercise(exerciseId, exerciseCard) {
        ajax('admin/exercises/delete', {id: exerciseId}, function (res) {
            if (res.status === 200) {
                exerciseCard.remove();
            } else {
                alert('Failed to delete. Please try again.');
            }
        }, function (res) {
            alert('An error occurred. Please try again.');
            console.log(res);
        });
    }

    // Function to initialize an Ace editor
    function initializeAceEditor(editor, mode, content, previewFrameId = null) {
        editor.setTheme("ace/theme/monokai");
        editor.session.setMode("ace/mode/" + mode);
        editor.setOptions({
            fontSize: "14px",
            showPrintMargin: false,
            wrap: true,
            useWorker: false,  // Disable the syntax checker if it's causing issues
        });

        // Set the content after initialization
        editor.setValue(content, 1);

        // Adjust initial height based on content (with a minimum of 3 rows)
        adjustEditorHeight(editor, 3);

        // Track changes in the editor to show red border
        trackChanges(editor);

        // Render the preview if previewFrameId is provided
        if (previewFrameId) {
            document.getElementById(previewFrameId).srcdoc = editor.getValue();

            setTimeout(() => {
                syncHeights(editor, previewFrameId, 3);
            }, 100); // Increased delay to ensure preview is fully rendered
        }

        // Adjust height dynamically based on content changes
        editor.getSession().on('change', function () {
            if (previewFrameId) {
                document.getElementById(previewFrameId).srcdoc = editor.getValue();

                setTimeout(() => {
                    syncHeights(editor, previewFrameId, 3);
                }, 100); // Increased delay to ensure preview is fully rendered
            } else {
                adjustEditorHeight(editor, 3);
            }
        });

        // Also adjust height dynamically whenever the content changes
        editor.on('input', function () {
            adjustEditorHeight(editor, 3);
        });

        // Adjust height on window resize
        window.addEventListener('resize', function () {
            adjustEditorHeight(editor, 3);
        });
    }

    // Function to evaluate the initial code with the validation function
    function evaluateInitialCodeWithValidation(initialCode, validationFunctionEditor, previewFrameId) {
        try {
            // Get the validation function code
            const validationFunctionCode = validationFunctionEditor.getValue();

            // Get the iframe's content window and document
            const iframe = document.getElementById(previewFrameId);
            const iframeWindow = iframe.contentWindow;
            const iframeDocument = iframe.contentDocument || iframeWindow.document;

            // Inject the code into the iframe
            iframeDocument.open();
            iframeDocument.write(initialCode);
            iframeDocument.close();

            // Create a function that runs in the iframe's context
            const validateInIframe = new Function('window', 'document', validationFunctionCode + '; return validate();');

            // Execute the validation function within the iframe's context
            const isValid = validateInIframe(iframeWindow, iframeDocument);

            // Debug: Log the validation function and result
            console.log("Validation function executed in iframe context:", validationFunctionCode);
            console.log("Validation result:", isValid);

            return isValid;
        } catch (error) {
            console.error("Validation function execution error:", error);
            return false;
        }
    }

    // Function to track changes in the initial code editor and validate against the validation function
    function trackValidation(editorInitialCode, editorValidationFunction, previewFrameId) {
        editorInitialCode.getSession().on('change', function () {
            const initialCode = editorInitialCode.getValue();
            const isValid = evaluateInitialCodeWithValidation(initialCode, editorValidationFunction, previewFrameId);

            const validationEditorContainer = editorValidationFunction.container;

            // Apply green border if valid, otherwise remove it
            if (isValid) {
                validationEditorContainer.classList.add('validation-passed');
            } else {
                validationEditorContainer.classList.remove('validation-passed');
            }
        });
    }

    // Function to track changes in the editor and apply the red border
    function trackChanges(editor) {
        // Always set changed to true on every change
        editor._changed = false;
        editor.getSession().on('change', function () {
            editor._changed = true;
            editor.container.classList.add('editor-changed');
            enableSaveButton(editor.container.closest('.exercise-card').querySelector('.save-button'));
        });
        editor.changed = function () {
            return editor._changed;
        };
        editor.resetChanged = function () {
            editor._changed = false;
        };
    }

    // Function to adjust the editor height based on the number of visual lines
    function adjustEditorHeight(editor, minLines) {
        const session = editor.getSession();
        const renderer = editor.renderer;
        const lineHeight = renderer.lineHeight; // Get the actual line height from the Ace editor renderer
        const contentLength = session.getScreenLength(); // Get the number of visual lines in the session
        const lines = Math.max(contentLength, minLines); // Ensure we have at least minLines

        const newHeight = lines * lineHeight;
        editor.container.style.height = `${newHeight}px`;
        editor.resize();
    }

    // Function to synchronize the heights between the editor and preview
    function syncHeights(editor, previewFrameId, minLines = 3) {
        const editorHeight = parseInt(editor.container.style.height, 10);
        let previewHeight = 0;

        if (previewFrameId) {
            const previewFrame = document.getElementById(previewFrameId);

            if (previewFrame) {
                // Add an event listener to ensure the iframe is fully loaded
                previewFrame.addEventListener('load', function () {
                    // Access the iframe's content window and document after loading
                    const iframeDocument = previewFrame.contentDocument || previewFrame.contentWindow.document;

                    // Make sure iframeDocument exists and can be accessed
                    if (iframeDocument && iframeDocument.documentElement) {
                        previewHeight = iframeDocument.documentElement.scrollHeight || 0;

                        // Ensure the editor shows all lines without scrolling
                        adjustEditorHeight(editor, minLines);

                        const adjustedEditorHeight = parseInt(editor.container.style.height, 10);

                        // If preview height is greater than the adjusted editor height, increase the editor's height
                        if (previewHeight > adjustedEditorHeight) {
                            editor.container.style.height = `${previewHeight}px`;
                            editor.resize();
                        } else if (previewHeight < adjustedEditorHeight) {
                            // Adjust preview height if it's smaller than the editor height
                            adjustPreviewHeight(previewFrameId, editor.container.style.height);
                        }
                    } else {
                        console.error("Preview frame or its content is not accessible.");
                    }
                });
            } else {
                console.error("Preview frame not found.");
            }
        }
    }

    // Function to adjust the preview height to match the editor height
    function adjustPreviewHeight(previewFrameId, newHeight) {
        if (previewFrameId) {
            const previewFrame = document.getElementById(previewFrameId);
            if (previewFrame) {
                previewFrame.style.height = newHeight;
            }
        }
    }

    // Function to enable the save button
    function enableSaveButton(button) {
        button.disabled = false;
    }

    // Function to save content via AJAX
    function saveContent(exerciseId, instructionsEditor, initialCodeEditor, validationFunctionEditor, titleChanged) {
        const isNewExercise = exerciseId.startsWith('new-');
        const data = {
            id: isNewExercise ? null : exerciseId
        };
        const url = `admin/exercises/${isNewExercise ? 'create' : 'edit'}`;

        const titleElement = document.getElementById('exercise-name-' + exerciseId);
        if (titleChanged[exerciseId] && titleElement?.value.trim()) {
            data.exercise_name = titleElement.value.trim();
        }

        if (instructionsEditor.changed()) data.instructions = instructionsEditor.getValue();
        if (initialCodeEditor.changed()) data.initial_code = initialCodeEditor.getValue();
        if (validationFunctionEditor.changed()) data.validation_function = validationFunctionEditor.getValue();

        ajax(url, data, function (res) {
            if (res.status === 200) {
                if (isNewExercise) {
                    const newId = res.data.id;
                    const exerciseCard = document.getElementById('exercise-card-' + exerciseId);
                    exerciseCard.querySelector('.input-group-text').textContent = newId;
                    exerciseCard.id = 'exercise-card-' + newId;
                    updateEditorIds(exerciseId, newId);
                    exerciseId = newId; // Update scope
                }
                indicateSuccess(instructionsEditor, initialCodeEditor, validationFunctionEditor, exerciseId, titleChanged);
            } else {
                alert('Failed to save. Please try again.');
            }
        }, function (res) {
            alert('An error occurred. Please try again.');
            console.log(res);
        });
    }
    // Function to indicate successful save
    function indicateSuccess(instructionsEditor, initialCodeEditor, validationFunctionEditor, exerciseId, titleChanged) {
        const saveButton = document.querySelector('.save-button[data-id="' + exerciseId + '"]');

        if (instructionsEditor.changed()) {
            clearChangeIndicator(instructionsEditor);
            if (typeof instructionsEditor.resetChanged === 'function') instructionsEditor.resetChanged();
        }
        if (initialCodeEditor.changed()) {
            clearChangeIndicator(initialCodeEditor);
            if (typeof initialCodeEditor.resetChanged === 'function') initialCodeEditor.resetChanged();
        }
        if (validationFunctionEditor.changed()) {
            clearChangeIndicator(validationFunctionEditor);
            if (typeof validationFunctionEditor.resetChanged === 'function') validationFunctionEditor.resetChanged();
        }

        // Clear title change indicator if the title was changed
        if (titleChanged[exerciseId]) {
            const titleElement = document.getElementById('exercise-name-' + exerciseId);
            titleElement.classList.remove('editor-changed'); // Remove the red border
            titleChanged[exerciseId] = false; // Reset title changed status
        }

        // Disable the save button after a successful save
        if (saveButton) {
            saveButton.disabled = true;
        }

        // Safely check for the new exercise element
        const newExerciseElement = document.getElementById('exercise-card-' + exerciseId);
        if (newExerciseElement) {
            newExerciseElement.classList.remove('new-card-highlight');
        }
    }

    // Function to clear the red border after a successful save
    function clearChangeIndicator(editor) {
        editor.container.classList.remove('editor-changed');
    }

    function updateEditorIds(oldId, newId) {
        const exerciseCard = document.getElementById('exercise-card-' + newId);
        exerciseCard.querySelectorAll('.ace-editor').forEach((editorContainer) => {
            editorContainer.id = editorContainer.id.replace(oldId, newId);
        });
        exerciseCard.querySelectorAll('.preview-iframe').forEach((iframe) => {
            iframe.id = iframe.id.replace(oldId, newId);
        });
        const nameInput = exerciseCard.querySelector('input.form-control');
        nameInput.id = nameInput.id.replace(oldId, newId);
        const saveButton = exerciseCard.querySelector('.save-button');
        saveButton.dataset.id = newId;
        const deleteLink = exerciseCard.querySelector('.delete-exercise');
        deleteLink.dataset.id = newId;
    }

</script>


<!-- Add some CSS to style the Ace editor divs -->
<style>
    /* Card Container */
    .exercises-container {
        display: flex;
        flex-direction: column;
        gap: 20px; /* Space between each card */
    }

    /* Each Exercise Card */
    .exercise-card {
        display: flex;
        flex-direction: column;
        background-color: #99999926;
        border: 1px solid #ccc;
        border-radius: 8px;
        padding: 20px;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }

    /* Header */
    .exercise-header {
        margin-bottom: 20px;
    }

    .exercise-header .form-control {
        width: 100%;
    }

    /* Body Section: Flex Container */
    .exercise-body {
        display: flex;
        flex-direction: column;
        gap: 20px;
    }

    /* Editor and Preview Horizontal Layout */
    .horizontal-layout {
        display: flex;
        flex-direction: row;
        gap: 10px; /* Gap between editor and preview */
    }

    /* Editor Container */
    .editor-container {
        position: relative;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
    }

    /* Half-width Container for Side-by-Side Layout */
    .half-width {
        flex: 1; /* Both the editor and preview take equal space */
    }

    /* Full-width Container for the Validation Function */
    .full-width {
        width: 100%; /* Spans the full width of the card */
    }

    /* ACE Editor Styling */
    .ace-editor {
        height: 200px;
        width: 100%;
        margin: 0;
        padding: 10px;
        box-sizing: border-box;
    }

    /* Preview Container Styling */
    .preview-container {
        flex: 1;
        border: 1px solid #ccc;
        background-color: #f9f9f9;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .preview-iframe {
        width: 100%;
        height: 100%;
        border: none;
    }

    /* Save Button and Trashcan Styling */
    .exercise-footer {
        display: flex;
        justify-content: flex-end;
        align-items: center;
        margin-top: 20px;
        gap: 10px; /* Space between the save button and trash icon */
    }

    .save-button {
        padding: 6px 12px;
        background-color: #007bff; /* Bootstrap primary button color */
        color: #fff; /* Text color */
        border: 1px solid #007bff; /* Border color */
        border-radius: 4px; /* Border radius */
        font-size: 14px; /* Font size */
        cursor: pointer; /* Pointer cursor on hover */
        transition: background-color 0.3s ease; /* Smooth hover transition */
    }

    .save-button:hover {
        background-color: #0056b3; /* Darker blue on hover */
    }

    .text-danger {
        color: #dc3545;
        font-size: 1.2em;
    }

    /* Ensure Save Button and Trashcan are aligned correctly */
    .exercise-footer {
        display: flex;
        justify-content: flex-end;
        align-items: center;
        margin-top: 20px;
        gap: 10px; /* Space between the save button and trash icon */
    }

    /* Style for editors or fields that have changed */
    .editor-changed {
        border: 2px solid red !important; /* Draw a red border */
    }

    /* Constrain content width and center it horizontally */
    #container {
        max-width: 1300px; /* Increased width for courses pages */
        padding-left: 0;
        padding-right: 0;
        margin-left: auto;
        margin-right: auto;
    }

    .custom-container {
        margin-left: auto;
        margin-right: auto;
        padding-left: 0;
        padding-right: 0;
        max-width: 1300px;
        box-sizing: border-box;
    }

    .validation-passed {
        background-color: #0c4a1a;
    }

    .new-card-highlight {
        animation: highlightAnimation 4s ease;
    }

    @keyframes highlightAnimation {
        from {
            background-color: #ffffcc; /* Highlight color */
        }
        to {
            background-color: transparent;
        }
    }

</style>


</div>

