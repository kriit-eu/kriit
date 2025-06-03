</script>
<script>
// OpenAPI functionality (moved from grading_index.php)
function openSwaggerModal() {
    const solutionUrl = document.getElementById('solutionUrlInput').value;
    const swaggerUrlInput = document.getElementById('swaggerUrlInput');
    const promptTextarea = document.getElementById('promptTextarea');

    // Set the default URL by appending /swagger-ui-init.js to the solution URL
    if (solutionUrl && solutionUrl !== '#') {
        let swaggerUrl = solutionUrl;

        // Handle specific SwaggerUI links like https://docs.foo.me/en/#/forms/createForm
        // Extract the base URL (everything before the #)
        if (swaggerUrl.includes('#/')) {
            swaggerUrl = swaggerUrl.split('#/')[0];
        }

        // Make sure the URL ends with a slash before appending swagger-ui-init.js
        if (!swaggerUrl.endsWith('/')) {
            swaggerUrl += '/';
        }
        swaggerUrl += 'swagger-ui-init.js';
        swaggerUrlInput.value = swaggerUrl;
    } else {
        swaggerUrlInput.value = '';
    }

    // Clear previous output
    document.getElementById('swaggerDocOutput').value = '';

    // Load the prompt from settings
    loadPromptFromSettings();

    // Show the modal
    const modalElement = document.getElementById('swaggerModal');
    const modal = new bootstrap.Modal(modalElement);

    // Initialize tooltips when the modal is fully shown
    modalElement.addEventListener('shown.bs.modal', function () {
        // Initialize all tooltips within the modal
        const tooltipTriggerList = [].slice.call(modalElement.querySelectorAll('[data-bs-toggle="tooltip"]'));
        tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
    });

    modal.show();
}

function loadPromptFromSettings() {
    const promptTextarea = document.getElementById('promptTextarea');
    const promptDisplay = document.getElementById('promptDisplay');

    // Use AJAX to fetch the prompt from settings
    ajax('assignments/getOpenApiPrompt', {}, function(response) {
        const promptText = (response.status === 200 && response.data && response.data.prompt !== undefined)
            ? response.data.prompt
            : '';

        // Set the prompt text in the appropriate element
        if (userIsAdmin) {
            // For admins: set the value of the textarea
            promptTextarea.value = promptText;

            // Add event listener to save the prompt when it changes
            promptTextarea.addEventListener('input', function() {
                // Debounce the save operation
                if (promptTextarea.saveTimeout) {
                    clearTimeout(promptTextarea.saveTimeout);
                }
                promptTextarea.saveTimeout = setTimeout(function() {
                    savePromptToSettings(promptTextarea.value);
                }, 1000); // Save after 1 second of inactivity
            });
        } else {
            // For non-admins: set the text content of the pre element and the hidden textarea
            if (promptDisplay) {
                promptDisplay.textContent = promptText;
            }
            promptTextarea.value = promptText; // Still set the hidden textarea for copying
        }
    }, function(error) {
        console.error('Failed to load prompt from settings:', error);
        if (userIsAdmin) {
            promptTextarea.value = '';
        } else if (promptDisplay) {
            promptDisplay.textContent = '';
            promptTextarea.value = ''; // Also clear the hidden textarea
        }
    });
}

function savePromptToSettings(promptText) {
    // Only admins can save the prompt
    if (!userIsAdmin) return;

    ajax('assignments/saveOpenApiPrompt', {
        prompt: promptText
    }, function(response) {
        if (response.status === 200) {
            console.log('Prompt saved successfully');
        } else {
            console.error('Failed to save prompt:', response.error);
        }
    }, function(error) {
        console.error('Failed to save prompt:', error);
    });
}

function fetchSwaggerDoc() {
    const swaggerUrl = document.getElementById('swaggerUrlInput').value.trim();
    const outputTextarea = document.getElementById('swaggerDocOutput');
    const fetchButton = document.getElementById('fetchSwaggerButton');
    const copyButton = document.getElementById('copyButton');
    const loadingSpinner = document.getElementById('swaggerLoadingSpinner');

    if (!swaggerUrl) {
        showError(outputTextarea, 'Palun sisesta kehtiv URL');
        return;
    }

    // Disable the buttons while fetching
    fetchButton.disabled = true;
    copyButton.disabled = true;
    fetchButton.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Loading...';
    outputTextarea.value = '';

    // Show the loading spinner
    loadingSpinner.classList.remove('d-none');

    // Use AJAX to fetch the swagger-ui-init.js file through a PHP proxy
    ajax('assignments/fetchSwaggerDoc', {
        url: swaggerUrl
    }, function(response) {
        // Reset UI elements
        fetchButton.disabled = false;
        fetchButton.innerHTML = 'Hangi OpenAPI spekk';
        loadingSpinner.classList.add('d-none');

        if (response.status === 200 && response.data && response.data.swaggerDoc) {
            // Format the JSON for better readability
            try {
                // Get the base URL from the swagger URL
                const swaggerDocObj = response.data.swaggerDoc;
                const swaggerUrl = document.getElementById('swaggerUrlInput').value.trim();
                const baseUrl = getBaseUrlFromSwaggerUrl(swaggerUrl);

                // Check if we need to modify the servers array
                if (baseUrl) {
                    // If servers array doesn't exist or first server is "/", add/replace with the base URL
                    if (!swaggerDocObj.servers ||
                        !swaggerDocObj.servers.length ||
                        (swaggerDocObj.servers.length > 0 && swaggerDocObj.servers[0].url === '/')) {

                        // Create servers array if it doesn't exist
                        if (!swaggerDocObj.servers) {
                            swaggerDocObj.servers = [];
                        }

                        // Add or replace the first server with the base URL
                        if (swaggerDocObj.servers.length === 0) {
                            swaggerDocObj.servers.push({ url: baseUrl, description: 'API Server' });
                        } else {
                            swaggerDocObj.servers[0] = { url: baseUrl, description: 'API Server' };
                        }
                    }
                }

                const formattedJson = JSON.stringify(swaggerDocObj, null, 2);
                outputTextarea.value = formattedJson;
                copyButton.disabled = false; // Enable the copy button only when we have content
            } catch (e) {
                showError(outputTextarea, 'Viga JSON-i vormindamisel: ' + e.message);
                copyButton.disabled = true;
            }
        } else {
            let errorMessage = 'OpenAPI spetsifikatsiooni hankimine või parsimine ebaõnnestus';
            if (response.error) {
                errorMessage = response.error;
            }
            showError(outputTextarea, errorMessage);
            copyButton.disabled = true;
        }
    }, function(error) {
        // Reset UI elements
        fetchButton.disabled = false;
        fetchButton.innerHTML = 'Hangi OpenAPI spekk';
        loadingSpinner.classList.add('d-none');

        let errorMessage = 'OpenAPI spetsifikatsiooni hankimisel tekkis viga';
        if (error) {
            if (error.includes('404')) {
                errorMessage = 'OpenAPI spetsifikatsiooni faili ei leitud (404). Palun kontrolli URL-i.';
            } else if (error.includes('403')) {
                errorMessage = 'Juurdepääs OpenAPI spetsifikatsioonile on keelatud (403). Sul ei pruugi olla õigusi sellele ressursile jõuda.';
            } else if (error.includes('500')) {
                errorMessage = 'Serveril tekkis viga (500) OpenAPI spetsifikatsiooni hankimisel.';
            } else if (error.includes('timeout')) {
                errorMessage = 'Päring aegus. Server võib olla aeglane või kättesaamatu.';
            } else {
                errorMessage = error;
            }
        }

        showError(outputTextarea, errorMessage);
        copyButton.disabled = true;
    });
}

// Helper function to show formatted error messages
function showError(textarea, message) {
    textarea.value = '⚠️ VIGA: ' + message;
    textarea.style.color = 'red';
    setTimeout(() => {
        textarea.style.color = ''; // Reset color after a delay
    }, 5000);
}

// Helper function to extract the base URL from the swagger-ui-init.js URL
function getBaseUrlFromSwaggerUrl(swaggerUrl) {
    if (!swaggerUrl) return null;

    try {
        // Create a URL object from the swagger URL
        const urlObj = new URL(swaggerUrl);

        // Just return the origin (protocol + hostname) without any path
        // This ensures we get the root of the server (e.g., https://docs.eerovallistu.site)
        return urlObj.origin;
    } catch (e) {
        console.error('Viga URL-i parsimisel:', e);
        return null;
    }
}

function copyPromptAndSpec() {
    const promptTextarea = document.getElementById('promptTextarea');
    const swaggerTextarea = document.getElementById('swaggerDocOutput');
    const copyButton = document.getElementById('copyButton');
    const originalButtonText = copyButton.innerHTML;

    // Only proceed if there's content to copy
    if (!swaggerTextarea.value.trim()) {
        alert('OpenAPI spetsifikatsioon puudub. Palun hangi spetsifikatsioon enne kopeerimist.');
        return;
    }

    // Get the prompt text from the textarea (which exists for both admins and non-admins)
    // For non-admins, this is a hidden textarea that still contains the prompt text
    const promptText = promptTextarea.value;

    // Combine the content from both textareas
    const combinedText = promptText + '\n\n' + swaggerTextarea.value;

    // Use the modern Clipboard API if available
    if (navigator.clipboard && navigator.clipboard.writeText) {
        navigator.clipboard.writeText(combinedText)
            .then(() => {
                // Visual feedback that copy was successful
                copyButton.innerHTML = '<i class="bi bi-check"></i> Kopeeritud!';
                setTimeout(() => {
                    copyButton.innerHTML = originalButtonText;
                }, 1500);
            })
            .catch(err => {
                console.error('Teksti kopeerimine ebaõnnestus: ', err);
                // Fallback to the older method
                fallbackCopyTextToClipboard(combinedText);
            });
    } else {
        // Fallback for browsers that don't support the Clipboard API
        fallbackCopyTextToClipboard(combinedText);
    }

    // Fallback copy method using execCommand
    function fallbackCopyTextToClipboard(text) {
        // Create a temporary textarea
        const tempTextarea = document.createElement('textarea');
        tempTextarea.style.position = 'fixed';
        tempTextarea.style.left = '-9999px';
        tempTextarea.style.top = '0';
        tempTextarea.value = text;
        document.body.appendChild(tempTextarea);

        // Select and copy the text
        tempTextarea.focus();
        tempTextarea.select();

        try {
            const successful = document.execCommand('copy');
            if (successful) {
                // Visual feedback that copy was successful
                copyButton.innerHTML = '<i class="bi bi-check"></i> Kopeeritud!';
                setTimeout(() => {
                    copyButton.innerHTML = originalButtonText;
                }, 1500);
            } else {
                alert('Teksti kopeerimine ebaõnnestus');
            }
        } catch (err) {
            console.error('Teksti kopeerimine ebaõnnestus: ', err);
            alert('Teksti kopeerimine ebaõnnestus: ' + err);
        } finally {
            // Remove the temporary textarea
            document.body.removeChild(tempTextarea);
        }
    }
}
</script>
<?php if (!$isStudent): ?>
    <button type="button" class="btn btn-sm btn-info ms-2" id="openApiButton" onclick="openSwaggerModal()" style="display: none;">OpenAPI</button>
<?php endif; ?>

<!-- OpenAPI Modal -->
<div class="modal fade" id="swaggerModal" tabindex="-1" aria-labelledby="swaggerModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl" style="max-width: 90%;">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="swaggerModalLabel">OpenAPI Dokumentatsioon</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label for="swaggerUrlInput" class="form-label">swagger-ui-init.js URL
                        <i class="bi bi-info-circle" data-bs-toggle="tooltip" data-bs-placement="top"
                           title="Sisesta swagger-ui-init.js faili URL. Vaikimisi genereeritakse see automaatselt lahenduse URL-ist."></i>
                    </label>
                    <input type="text" class="form-control" id="swaggerUrlInput" placeholder="https://näide.ee/swagger-ui-init.js">
                </div>
                <?php if ($this->auth->userIsAdmin): ?>
                <div class="mb-3">
                    <label for="promptTextarea" class="form-label">Prompt
                        <i class="bi bi-info-circle" data-bs-toggle="tooltip" data-bs-placement="top"
                           title="See on prompti tekst, mis kopeeritakse koos OpenAPI spetsifikatsiooniga. Administraatorina saad seda prompti muuta."></i>
                    </label>
                    <textarea class="form-control" id="promptTextarea" rows="5"></textarea>
                </div>
                <?php else: ?>
                <div class="mb-3">
                    <label class="form-label">Prompt
                        <i class="bi bi-info-circle" data-bs-toggle="tooltip" data-bs-placement="top"
                           title="See on prompti tekst, mis kopeeritakse koos OpenAPI spetsifikatsiooniga."></i>
                    </label>
                    <pre id="promptDisplay" class="form-control" style="min-height: 100px; white-space: pre-wrap;"></pre>
                    <!-- Hidden textarea for copying purposes -->
                    <textarea id="promptTextarea" style="display: none;"></textarea>
                </div>
                <?php endif; ?>
                <div class="mb-3">
                    <label for="swaggerDocOutput" class="form-label">OpenAPI Spetsifikatsioon
                        <i class="bi bi-info-circle" data-bs-toggle="tooltip" data-bs-placement="top"
                           title="See on õpilase lahendusest ekstraktitud OpenAPI spetsifikatsioon. See kopeeritakse koos promptiga."></i>
                    </label>
                    <div class="position-relative">
                        <textarea class="form-control" id="swaggerDocOutput" rows="20" readonly></textarea>
                        <div id="swaggerLoadingSpinner" class="position-absolute top-0 start-0 w-100 h-100 d-none" style="background-color: rgba(255,255,255,0.7);">
                            <div class="d-flex justify-content-center align-items-center h-100">
                                <div class="spinner-border text-primary" role="status">
                                    <span class="visually-hidden">Laadimine...</span>
                                </div>
                                <span class="ms-2">Laadin OpenAPI spetsifikatsiooni...</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <!-- 1. Hangi button -->
                <button type="button" class="btn btn-primary" id="fetchSwaggerButton" onclick="fetchSwaggerDoc()"
                        data-bs-toggle="tooltip" data-bs-placement="top" title="Hangi OpenAPI spetsifikatsioon URL-ist">
                    Hangi OpenAPI spekk
                </button>

                <!-- 2. Kopeeri prompt ja spekk button -->
                <button type="button" class="btn btn-success" id="copyButton" onclick="copyPromptAndSpec()" disabled
                        data-bs-toggle="tooltip" data-bs-placement="top" title="Kopeeri nii prompt kui ka OpenAPI spetsifikatsioon lõikelauale">
                    Kopeeri prompt ja spekk
                </button>

                <!-- 3a. Ava ChatGPT button -->
                <button type="button" class="btn btn-info" id="openChatGPTButton" onclick="window.open('https://chatgpt.com', '_blank')"
                        data-bs-toggle="tooltip" data-bs-placement="top" title="Ava ChatGPT uues aknas, et kleepida kopeeritud sisu">
                    Ava ChatGPT <i class="bi bi-box-arrow-up-right" aria-hidden="true"></i>
                </button>

                <!-- 3b. Ava Claude button -->
                <button type="button" class="btn btn-warning" id="openClaudeButton" onclick="window.open('https://claude.ai', '_blank')"
                        data-bs-toggle="tooltip" data-bs-placement="top" title="Ava Claude uues aknas, et kleepida kopeeritud sisu">
                    Ava Claude <i class="bi bi-box-arrow-up-right" aria-hidden="true"></i>
                </button>

                <!-- 4. Sulge button -->
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"
                        data-bs-toggle="tooltip" data-bs-placement="top" title="Sulge see aken">
                    Sulge
                </button>
            </div>
        </div>
    </div>
</div>
