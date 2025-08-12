<?php
/**
 * Shared grading modal component
 * Usage: include this file in pages that need grading functionality
 * Requires: $isStudent variable to be set (defaults to false if not set)
 */

$isStudent = $isStudent ?? false;
?>

<!-- Load grading modal CSS -->
<link rel="stylesheet" href="<?= BASE_URL ?>templates/partials/grading_modal/grading_modal.css">

<!-- Load grading modal JavaScript -->
<script>
    // Make current user ID available to JavaScript
    window.authUserId = <?= json_encode($this->auth->userId ?? null) ?>;
</script>
<script src="<?= BASE_URL ?>templates/partials/grading_modal/grading_modal.js"></script>

<!-- Grading Modal -->
<div class="modal fade" id="gradingModal" tabindex="-1" aria-labelledby="gradingModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-fluid" style="max-width: 95%; width: 95%;">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="gradingModalLabel">Ülesanne</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <!-- Solution URL Section -->
                <div class="mb-3" id="solutionUrlSection"><h6>Lahenduse URL</h6>
                    <div class="mt-2 d-none" id="solutionUrlDetails">
                        <div class="input-group">
                            <input type="text" class="form-control form-control-sm font-monospace" id="solutionUrlInput"
                                   readonly>
                            <button class="btn btn-outline-secondary btn-sm" type="button" id="copySolutionUrl"
                                    title="Kopeeri URL">
                                <i class="fas fa-copy"></i>
                            </button>
                        </div>
                    </div>
                    <div id="openApiSection"><?php 
                        $isStudent = $isStudent; 
                        $assignment = ['assignmentInvolvesOpenApi' => false]; // Will be updated by JS
                        include __DIR__ . '/../../views/modules/openapi_module.php'; 
                    ?></div>
                </div>

                <!-- Assignment Instructions -->
                <div class="mb-3" id="instructionsSection">
                    <h6>Ülesande kirjeldus</h6>
                    <div class="border rounded p-3 bg-white markdown-content">
                        <div id="assignmentInstructionsPreview" style="max-height: 60px; overflow: hidden; position: relative;">
                            <div id="assignmentInstructions">
                                <p class="text-muted">Kirjeldus puudub</p>
                            </div>
                        </div>
                        <button class="btn btn-link p-0 text-primary small" type="button" 
                                id="showMoreInstructions" style="display: none;">
                            Näita rohkem...
                        </button>
                    </div>
                </div>

                <!-- Assignment Criteria Section -->
                <div class="mb-3" id="criteriaSection">
                    <h6>Hindamiskriteeriumid</h6>
                    <div id="criteriaContainer" class="border rounded p-3 bg-light">
                        <p class="text-muted">Kriteeriume pole määratud</p>
                    </div>
                </div>

                <!-- Comments Thread -->
                <div class="mb-3">
                    <h6>Vestlus</h6>
                    <div id="messagesContainer" class="message-container border-top pt-2">
                        <div class="text-center">
                            <span class="loading-spinner"></span>
                            <span class="ms-2">Laen sõnumeid...</span>
                        </div>
                    </div>
                </div>

                <!-- New Message Form -->
                <div class="mb-3">
                    <h6 class="mb-2">
                        <a href="#" class="text-decoration-none" id="toggleCommentSection" aria-expanded="false">
                            <i class="fas fa-chevron-right me-1"></i>Lisa kommentaar
                        </a>
                    </h6>
                    
                    <div id="commentFormSection" class="comment-section-collapsed">
                    
                    <!-- Split view: Editor and Preview -->
                    <div class="row">
                        <!-- Text Editor Column -->
                        <div class="col-md-6">
                            <div class="editor-wrapper">
                                <div class="editor-header">
                                    <small class="text-muted">
                                        <i class="fas fa-edit"></i> Redaktor
                                    </small>
                                </div>
                                <textarea class="form-control" id="newMessageContent" rows="8"
                                          placeholder="Kirjuta kommentaar õpilasele... (pildide kleepimiseks kasuta Ctrl+V)"
                                          style="resize: none; min-height: 200px; overflow: hidden;"></textarea>
                            </div>
                        </div>
                        
                        <!-- Preview Column -->
                        <div class="col-md-6">
                            <div class="preview-wrapper">
                                <div class="preview-header">
                                    <small class="text-muted">
                                        <i class="fas fa-eye"></i> Eelvaade
                                    </small>
                                </div>
                                <div id="messagePreview" class="form-control" 
                                     style="min-height: 200px; background-color: #f8f9fa; overflow-y: hidden; word-wrap: break-word;">
                                    <div class="text-muted text-center p-3">
                                        <i class="fas fa-eye-slash"></i><br>
                                        Eelvaade ilmub siia...
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Image Upload Progress -->
                    <div id="imageUploadProgress" class="mt-2 d-none">
                        <div class="card border-primary">
                            <div class="card-body p-3">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <h6 class="mb-0">Pildi üleslaadimine</h6>
                                    <button type="button" class="btn btn-sm btn-outline-danger" id="cancelUpload">
                                        <i class="fas fa-times"></i> Tühista
                                    </button>
                                </div>
                                <div class="progress mb-2" style="height: 8px;">
                                    <div class="progress-bar progress-bar-striped progress-bar-animated" 
                                         id="uploadProgressBar" role="progressbar" style="width: 0%"></div>
                                </div>
                                <div class="d-flex align-items-center">
                                    <div class="spinner-border spinner-border-sm me-2" role="status">
                                        <span class="visually-hidden">Laadimine...</span>
                                    </div>
                                    <small class="text-muted" id="uploadStatusText">Alustamine...</small>
                                </div>
                                <div id="uploadResults" class="mt-2"></div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Image Upload Zone -->
                    <div class="mt-2">
                        <div class="row">
                            <div class="col-md-8">
                                <div class="form-text">
                                    <small class="text-muted">
                                        💡 <strong>Näpunäide:</strong> Kopeeri ükskõik milline pilt ja kleebi see otse redaktorisse (Ctrl+V)! 
                                        Pildid lisatakse automaatselt Markdown-vormingus.
                                    </small>
                                </div>
                            </div>
                            <div class="col-md-4 text-end">
                                <button type="button" class="btn btn-outline-primary btn-sm" id="selectImagesBtn">
                                    <i class="fas fa-image"></i> Vali pildid
                                </button>
                                <input type="file" id="imageFileInput" multiple accept="image/*" style="display: none;">
                            </div>
                        </div>
                    </div>
                    
                    <div class="invalid-feedback" id="messageError"></div>
                    
                    </div> <!-- End commentFormSection -->
                </div>

                <!-- Teacher Private Notes -->
                <?php include __DIR__ . '/../../views/modules/teacher_notes_module.php'; ?>
            </div>
            <div class="modal-footer">
                <div class="d-flex justify-content-end w-100 align-items-center gap-3">
                    <div class="d-flex align-items-center">
                        <span class="me-3"><strong>Hinne:</strong></span>
                        <div class="btn-group" role="group" aria-label="Hinded" id="gradeButtons">
                            <input type="radio" class="btn-check" name="grade" id="grade2" value="2">
                            <label class="btn btn-outline-danger" for="grade2">2</label>

                            <input type="radio" class="btn-check" name="grade" id="grade3" value="3">
                            <label class="btn btn-outline-warning" for="grade3">3</label>

                            <input type="radio" class="btn-check" name="grade" id="grade4" value="4">
                            <label class="btn btn-outline-success" for="grade4">4</label>

                            <input type="radio" class="btn-check" name="grade" id="grade5" value="5">
                            <label class="btn btn-outline-success" for="grade5">5</label>

                            <input type="radio" class="btn-check" name="grade" id="gradeA" value="A">
                            <label class="btn btn-outline-primary" for="gradeA">A</label>

                            <input type="radio" class="btn-check" name="grade" id="gradeMA" value="MA">
                            <label class="btn btn-outline-secondary" for="gradeMA">MA</label>
                        </div>
                    </div>
                    <div>
                        <button type="button" class="btn btn-primary" id="saveBtn" disabled>
                            <span id="saveBtnText">Salvesta</span>
                            <span id="saveBtnSpinner" class="loading-spinner d-none ms-2"></span>
                        </button>
                    </div>
                </div>
                <div class="invalid-feedback" id="gradeError"></div>
            </div>
        </div>
    </div>
</div>