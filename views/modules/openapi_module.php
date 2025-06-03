<?php if (!$isStudent && $assignment['assignmentInvolvesOpenApi']): ?>
    <button type="button" class="btn btn-sm btn-info ms-2" id="openApiButton" onclick="openSwaggerModal()">OpenAPI</button>
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
