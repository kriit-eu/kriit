<?php if ($auth->userId): ?>
    <button type="button" class="btn btn-secondary settings-btn" data-bs-toggle="modal" data-bs-target="#settings-modal">
        <i class="fas fa-cog"></i>
    </button>

    <!-- Success Toast -->
    <div class="toast-container position-fixed top-0 end-0 p-3">
        <div id="settingsToast" class="toast align-items-center text-white bg-success border-0" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="d-flex">
                <div class="toast-body"></div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
        </div>
    </div>

    <!-- Settings Modal -->
    <div class="modal fade" id="settings-modal" data-bs-backdrop="static" tabindex="-1" aria-labelledby="settingsModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="settingsModalLabel">Seaded</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="emailForm" class="mb-4" onsubmit="return false;">
                        <div class="mb-3">
                            <label class="form-label">Praegune e-post</label>
                            <div class="form-control-plaintext" id="currentEmail"><?= $auth->userEmail ?></div>
                        </div>
                        <div class="mb-3">
                            <label for="newEmail" class="form-label">Uus e-post</label>
                            <input type="email" class="form-control" id="newEmail" name="email" required>
                        </div>
                        <div class="mb-3">
                            <label for="currentPassword" class="form-label">Praegune parool</label>
                            <input type="password" class="form-control" id="currentPassword" name="password" required>
                        </div>
                        <button type="submit" class="btn btn-primary">Uuenda e-posti</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <style>
        .settings-btn {
            position: absolute;
            top: 10px;
            right: 80px;
            z-index: 1000;
        }

        .settings-btn i {
            margin-right: 0;
        }

        .settings-btn:hover i {
            animation: spin 2s linear infinite;
        }

        @keyframes spin {
            from {
                transform: rotate(0deg);
            }
            to {
                transform: rotate(360deg);
            }
        }

        .form-control-plaintext {
            background-color: #f8f9fa;
            padding: 0.375rem 0.75rem;
            border-radius: 0.25rem;
        }

        .toast-container {
            z-index: 1070;
        }
    </style>
<?php endif; ?> 