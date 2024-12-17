<?php if ($auth->userId): ?>
    <button type="button" class="btn btn-secondary settings-btn" data-bs-toggle="modal" data-bs-target="#settings-modal">
        <i class="fas fa-cog"></i>
    </button>

    <!-- Toast Container -->
    <div class="toast-container position-fixed top-0 end-0 p-3">
        <!-- Success Toast -->
        <div id="settingsToast" class="toast align-items-center text-white bg-success border-0" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="d-flex">
                <div class="toast-body"></div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
        </div>
        <!-- Error Toast -->
        <div id="errorToast" class="toast align-items-center text-white bg-danger border-0" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="d-flex">
                <div class="toast-body">
                    <i class="fas fa-exclamation-circle me-2"></i>
                    <span id="errorMessage"></span>
                </div>
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
                <div class="modal-body p-0">
                    <!-- Tabs -->
                    <ul class="nav nav-tabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#email-tab" type="button" role="tab">
                                <i class="fas fa-envelope me-2"></i>E-posti seaded
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" data-bs-toggle="tab" data-bs-target="#password-tab" type="button" role="tab">
                                <i class="fas fa-key me-2"></i>Parooli seaded
                            </button>
                        </li>
                    </ul>

                    <!-- Tab Content -->
                    <div class="tab-content p-4">
                        <!-- Email Tab -->
                        <div class="tab-pane fade show active" id="email-tab" role="tabpanel">
                            <form id="emailForm" onsubmit="return false;">
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
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save me-2"></i>Salvesta
                                </button>
                            </form>
                        </div>

                        <!-- Password Tab -->
                        <div class="tab-pane fade" id="password-tab" role="tabpanel">
                            <form id="passwordForm" onsubmit="return false;">
                                <div class="mb-3">
                                    <label for="oldPassword" class="form-label">Praegune parool</label>
                                    <input type="password" class="form-control" id="oldPassword" name="current_password" required>
                                </div>
                                <div class="mb-3">
                                    <label for="newPassword" class="form-label">Uus parool</label>
                                    <input type="password" class="form-control" id="newPassword" name="new_password" minlength="8" required>
                                    <div class="form-text text-muted">
                                        <i class="fas fa-info-circle me-1"></i>
                                        Parool peab olema vähemalt 8 tähemärki pikk
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label for="confirmPassword" class="form-label">Kinnita uus parool</label>
                                    <input type="password" class="form-control" id="confirmPassword" name="confirm_password" minlength="8" required>
                                    <div id="passwordMatchFeedback" class="invalid-feedback">
                                        <i class="fas fa-exclamation-circle me-1"></i>
                                        Paroolid ei kattu
                                    </div>
                                </div>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save me-2"></i>Salvesta
                                </button>
                            </form>
                        </div>
                    </div>
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

        .invalid-feedback {
            display: none;
        }

        .is-invalid {
            border-color: #dc3545;
        }

        .is-invalid ~ .invalid-feedback {
            display: block;
        }

        /* Tab styling */
        .nav-tabs {
            border-bottom: none;
            background-color: #f8f9fa;
            padding: 0.5rem 1rem 0;
        }

        .nav-tabs .nav-link {
            border: none;
            color: #6c757d;
            padding: 0.75rem 1.25rem;
            font-weight: 500;
            border-radius: 0;
        }

        .nav-tabs .nav-link:hover {
            border: none;
            color: #495057;
        }

        .nav-tabs .nav-link.active {
            border: none;
            border-bottom: 2px solid #0d6efd;
            color: #0d6efd;
            background-color: transparent;
        }

        .tab-content {
            background-color: #fff;
        }

        .modal-header {
            background-color: #f8f9fa;
            border-bottom: none;
        }

        .modal-body {
            background-color: #f8f9fa;
        }

        .tab-pane {
            background-color: #fff;
            border-radius: 0.5rem;
            padding: 2rem;
        }
    </style>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const successToast = new bootstrap.Toast(document.getElementById('settingsToast'));
            const errorToast = new bootstrap.Toast(document.getElementById('errorToast'));
            const errorMessageEl = document.getElementById('errorMessage');

            // Email form submission
            document.getElementById('emailForm').addEventListener('submit', async function(e) {
                e.preventDefault();
                const formData = new FormData(this);

                try {
                    const response = await fetch('/api/settings/email', {
                        method: 'POST',
                        body: formData
                    });

                    const data = await response.json();

                    if (data.success) {
                        document.getElementById('settingsToast').querySelector('.toast-body').textContent = 'E-posti aadress on muudetud';
                        successToast.show();
                        document.getElementById('currentEmail').textContent = formData.get('email');
                        this.reset();
                    } else {
                        if (data === 'vale parool') {
                            errorMessageEl.textContent = 'Vale parool';
                        } else {
                            errorMessageEl.textContent = 'Midagi läks valesti';
                        }
                        errorToast.show();
                    }
                } catch (error) {
                    errorMessageEl.textContent = 'Midagi läks valesti';
                    errorToast.show();
                }
            });

            // ... rest of your existing code ...
        });
    </script>
<?php endif; ?> 