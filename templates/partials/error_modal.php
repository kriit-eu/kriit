<div class="modal" id="error-modal" tabindex="-1" aria-labelledby="errorModalLabel" aria-hidden="true" style="background: rgba(0, 0, 0, 0.2); z-index: 1060;">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 90vw; width: fit-content; min-width: 300px;">
        <div class="modal-content" style="box-shadow: 0 0 20px rgba(0, 0, 0, 0.5);">
            <div class="modal-header bg-danger text-white border-bottom-0">
                <h5 class="modal-title d-flex align-items-center gap-2" id="errorModalLabel">
                    <i class="fas fa-exclamation-triangle"></i>
                    Application Error
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="errorModalBody">
                    <?= __('An error occurred') ?>
                </div>
            </div>
        </div>
    </div>
</div>