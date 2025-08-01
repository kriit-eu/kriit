<?php
/**
 * Reusable Markdown editor + live preview partial
 * Usage: include with required field IDs and names
 * Example: include 'templates/partials/markdown_editor.php';
 * Required variables:
 *   $editorId, $previewId, $fieldName, $labelText, $initialValue
 */
?>
<!-- Image upload progress and controls -->
<div id="<?= htmlspecialchars($editorId) ?>_imageUploadProgress" class="mt-2 d-none">
    <div class="card border-primary">
        <div class="card-body p-3">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <h6 class="mb-0">Pildi üleslaadimine</h6>
                <button type="button" class="btn btn-sm btn-outline-danger" id="<?= htmlspecialchars($editorId) ?>_cancelUpload">
                    <i class="fas fa-times"></i> Tühista
                </button>
            </div>
            <div class="progress mb-2" style="height: 8px;">
                <div class="progress-bar progress-bar-striped progress-bar-animated" 
                     id="<?= htmlspecialchars($editorId) ?>_uploadProgressBar" role="progressbar" style="width: 0%"></div>
            </div>
            <div class="d-flex align-items-center">
                <div class="spinner-border spinner-border-sm me-2" role="status">
                    <span class="visually-hidden">Laadimine...</span>
                </div>
                <small class="text-muted" id="<?= htmlspecialchars($editorId) ?>_uploadStatusText">Alustamine...</small>
            </div>
            <div id="<?= htmlspecialchars($editorId) ?>_uploadResults" class="mt-2"></div>
        </div>
    </div>
</div>
<!-- Image upload controls and tip below editor/preview row -->

<!-- Import markdown-it and plugins for full-featured Markdown preview -->
<script src="/assets/js/markdown-it.min.js"></script>
<script src="/assets/js/markdown-it-footnote.min.js"></script>
<script src="/assets/js/markdown-it-emoji.min.js"></script>
<script src="/assets/js/markdown-it-sub.min.js"></script>
<script src="/assets/js/markdown-it-deflist.min.js"></script>
<script src="/assets/js/markdown-it-sup.min.js"></script>
<style>
/* Markdown table base styles */
.markdown-content table {
    width: auto;
    border-collapse: collapse;
    margin-bottom: 1em;
}
.markdown-content th,
.markdown-content td {
    border: 1px solid #dee2e6;
    padding: 0.5em 0.75em;
    background: #fff;
}
/* Zebra striping for Markdown tables in preview (GitHub style) */
.markdown-content table tr:nth-child(even) td {
    background-color: #f6f8fa;
}
/* Make preview boxes expand to fit content */
.markdown-content,
.form-control.markdown-content {
    min-height: 40px;
    height: auto !important;
    max-height: none !important;
    overflow-y: visible !important;
    resize: none;
    box-sizing: border-box;
}
</style>
<div class="mb-3" id="<?= htmlspecialchars($editorId) ?>_container">
    <label for="<?= htmlspecialchars($editorId) ?>" class="form-label fw-bold"><?= htmlspecialchars($labelText) ?></label>
    <!-- Preview-only mode (default) -->
    <div class="row" id="<?= htmlspecialchars($editorId) ?>_previewOnlyRow">
        <div class="col-12">
            <div class="preview-wrapper">
                <div class="preview-header">
                    <small class="text-muted"><i class="fas fa-eye"></i> Eelvaade</small>
                </div>
                <div id="<?= htmlspecialchars($previewId) ?>_full" class="form-control markdown-content"
                     style="min-height: 200px; background-color: #f8f9fa; overflow-y: hidden; word-wrap: break-word;">
                    <div class="text-muted text-center p-3">
                        <i class="fas fa-eye-slash"></i><br>
                        Eelvaade ilmub siia...
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Split view (edit mode) -->
    <div class="row" id="<?= htmlspecialchars($editorId) ?>_splitRow" style="display:none;">
        <!-- Editor -->
        <div class="col-md-6">
            <div class="editor-wrapper">
                <div class="editor-header">
                    <small class="text-muted"><i class="fas fa-edit"></i> Redaktor</small>
                </div>
                <textarea class="form-control" id="<?= htmlspecialchars($editorId) ?>" name="<?= htmlspecialchars($fieldName) ?>" rows="8"
                          placeholder="Kirjuta ülesande juhend... (Markdown, pildid Ctrl+V)"
                          style="resize: none; min-height: 200px; overflow: hidden;"><?= htmlspecialchars($initialValue) ?></textarea>
            </div>
        </div>
        <!-- Preview -->
        <div class="col-md-6">
            <div class="preview-wrapper">
                <div class="preview-header">
                    <small class="text-muted"><i class="fas fa-eye"></i> Eelvaade</small>
                </div>
                <div id="<?= htmlspecialchars($previewId) ?>" class="form-control markdown-content"
                     style="min-height: 200px; background-color: #f8f9fa; overflow-y: hidden; word-wrap: break-word;">
                    <div class="text-muted text-center p-3">
                        <i class="fas fa-eye-slash"></i><br>
                        Eelvaade ilmub siia...
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Bottom left action buttons: Muuda/Eelvaade -->
    <div class="row mt-2">
        <div class="col-12">
            <div id="<?= htmlspecialchars($editorId) ?>_actionBtns" class="d-flex flex-row align-items-center" style="gap: 0.5em;">
                <button type="button" class="btn btn-outline-secondary btn-sm" id="<?= htmlspecialchars($editorId) ?>_editBtn" style="display:none;">
                    <i class="fas fa-edit"></i> Muuda
                </button>
                <button type="button" class="btn btn-outline-secondary btn-sm" id="<?= htmlspecialchars($editorId) ?>_doneBtn" style="display:none;">
                    <i class="fas fa-eye"></i> Eelvaade
                </button>
            </div>
        </div>
    </div>
    <!-- Image paste tip and upload button below editor/preview row (only in edit mode) -->
    <div class="row mt-2 align-items-center" id="<?= htmlspecialchars($editorId) ?>_editControls" style="display:none;">
        <div class="col-md-8">
            <div class="form-text">
                <small class="text-muted">
                    💡 <strong>Näpunäide:</strong> Kopeeri ükskõik milline pilt ja kleebi see otse redaktorisse (Ctrl+V)! Pildid lisatakse automaatselt Markdown-vormingus.
                </small>
            </div>
        </div>
        <div class="col-md-4 text-end">
            <button type="button" class="btn btn-outline-primary btn-sm" id="<?= htmlspecialchars($editorId) ?>_selectImagesBtn">
                <i class="fas fa-image"></i> Vali pildid
            </button>
            <input type="file" id="<?= htmlspecialchars($editorId) ?>_imageFileInput" multiple accept="image/*" style="display: none;">
        </div>
    </div>
</div>
<script>

// --- Markdown editor mode toggle logic ---
(function() {
    if (!window.markdownit) return;
    var md = window.markdownit({
        html: true,
        linkify: true,
        typographer: true,
        breaks: true
    })
        .use(window.markdownitFootnote)
        .use(window.markdownitDeflist)
        .use(window.markdownitEmoji)
        .use(window.markdownitSub)
        .use(window.markdownitSup);
    var editorId = '<?= addslashes($editorId) ?>';
    var previewId = '<?= addslashes($previewId) ?>';
    var textarea = document.getElementById(editorId);
    var preview = document.getElementById(previewId);
    var previewFull = document.getElementById(previewId + '_full');
    var container = document.getElementById(editorId + '_container');
    var previewOnlyRow = document.getElementById(editorId + '_previewOnlyRow');
    var splitRow = document.getElementById(editorId + '_splitRow');
    var editControls = document.getElementById(editorId + '_editControls');
    var editBtn = document.getElementById(editorId + '_editBtn');
    var doneBtn = document.getElementById(editorId + '_doneBtn');

    // State: edit mode or not
    var isEditMode = false;

    function showEditMode(editing) {
        isEditMode = editing;
        if (editing) {
            if (previewOnlyRow) previewOnlyRow.style.display = 'none';
            if (splitRow) splitRow.style.display = '';
            if (editControls) editControls.style.display = '';
            if (editBtn) editBtn.style.display = 'none';
            if (doneBtn) doneBtn.style.display = '';
            setTimeout(function() { if (textarea) textarea.focus(); }, 100);
        } else {
            if (previewOnlyRow) previewOnlyRow.style.display = '';
            if (splitRow) splitRow.style.display = 'none';
            if (editControls) editControls.style.display = 'none';
            if (editBtn) editBtn.style.display = '';
            if (doneBtn) doneBtn.style.display = 'none';
        }
        updatePreview();
    }

    if (editBtn) editBtn.addEventListener('click', function() { showEditMode(true); });
    if (doneBtn) doneBtn.addEventListener('click', function() { showEditMode(false); });

    function autoExpand() {
        if (textarea) {
            textarea.style.height = 'auto';
            if (textarea.value.trim() === '') {
                textarea.style.height = '200px';
            } else {
                textarea.style.height = (textarea.scrollHeight + 2) + 'px';
            }
        }
        // Always expand preview to fit content
        [preview, previewFull].forEach(function(box) {
            if (!box) return;
            box.style.height = 'auto';
            // Use scrollHeight for actual content, fallback to min height for empty
            if (box.innerText.trim() === '' || box.innerHTML.indexOf('Eelvaade ilmub siia') !== -1) {
                box.style.height = '200px';
            } else {
                box.style.height = (box.scrollHeight + 2) + 'px';
            }
        });
    }

    function updatePreview() {
        var content = textarea ? textarea.value : '';
        var emptyHtml = '<div class="text-muted text-center p-3"><i class="fas fa-eye-slash"></i><br>Eelvaade ilmub siia...</div>';
        if (preview) {
            if (content.trim() === '') {
                preview.innerHTML = emptyHtml;
                preview.style.overflowY = 'hidden';
            } else {
                preview.innerHTML = md.render(content);
            }
        }
        if (previewFull) {
            if (content.trim() === '') {
                previewFull.innerHTML = emptyHtml;
                previewFull.style.overflowY = 'hidden';
            } else {
                previewFull.innerHTML = md.render(content);
            }
        }
        autoExpand();
    }

    if (textarea) {
        textarea.addEventListener('input', updatePreview);
        textarea.addEventListener('paste', function() {
            setTimeout(updatePreview, 10);
            setTimeout(updatePreview, 800);
        });
        textarea.addEventListener('mouseup', function() { autoExpand(); });
        textarea.addEventListener('focus', function() { autoExpand(); });
    }
    setTimeout(autoExpand, 0);
    updatePreview();

    // --- Image upload/paste/drag-drop logic ---
    // IDs for this instance
    var uploadProgress = document.getElementById('<?= htmlspecialchars($editorId) ?>_imageUploadProgress');
    var uploadProgressBar = document.getElementById('<?= htmlspecialchars($editorId) ?>_uploadProgressBar');
    var uploadStatusText = document.getElementById('<?= htmlspecialchars($editorId) ?>_uploadStatusText');
    var uploadResults = document.getElementById('<?= htmlspecialchars($editorId) ?>_uploadResults');
    var cancelUploadBtn = document.getElementById('<?= htmlspecialchars($editorId) ?>_cancelUpload');
    var selectImagesBtn = document.getElementById('<?= htmlspecialchars($editorId) ?>_selectImagesBtn');
    var imageFileInput = document.getElementById('<?= htmlspecialchars($editorId) ?>_imageFileInput');
    var currentUploads = [];
    var uploadCounter = 0;
    var supportedTypes = [
        'image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp', 'image/avif', 'image/bmp', 'image/tiff'
    ];
    if (selectImagesBtn && imageFileInput) {
        selectImagesBtn.addEventListener('click', function() {
            imageFileInput.click();
        });
        imageFileInput.addEventListener('change', function(e) {
            var files = Array.from(e.target.files);
            if (files.length > 0) handleMultipleFiles(files);
            e.target.value = '';
        });
    }
    if (textarea) {
        textarea.addEventListener('paste', function(e) {
            var items = (e.clipboardData || e.originalEvent.clipboardData).items;
            var imageFiles = [];
            for (var i = 0; i < items.length; i++) {
                if (items[i].type.indexOf('image') !== -1) {
                    imageFiles.push(items[i].getAsFile());
                }
            }
            if (imageFiles.length > 0) {
                e.preventDefault();
                handleMultipleFiles(imageFiles);
            }
        });
        textarea.addEventListener('dragover', function(e) {
            e.preventDefault(); e.stopPropagation(); textarea.classList.add('image-paste-active');
        });
        textarea.addEventListener('dragenter', function(e) {
            e.preventDefault(); e.stopPropagation(); textarea.classList.add('image-paste-active');
        });
        textarea.addEventListener('dragleave', function(e) {
            e.preventDefault(); e.stopPropagation(); if (!textarea.contains(e.relatedTarget)) textarea.classList.remove('image-paste-active');
        });
        textarea.addEventListener('drop', function(e) {
            e.preventDefault(); e.stopPropagation(); textarea.classList.remove('image-paste-active');
            var files = Array.from(e.dataTransfer.files).filter(function(file) { return file.type.indexOf('image') !== -1; });
            if (files.length > 0) handleMultipleFiles(files);
        });
    }
    if (cancelUploadBtn) {
        cancelUploadBtn.addEventListener('click', function() { cancelAllUploads(); });
    }
    function validateFile(file) {
        var errors = [];
        if (supportedTypes.indexOf(file.type) === -1) errors.push('Toetamata failitüüp: ' + file.type);
        if (file.size > 10 * 1024 * 1024) errors.push('Fail on liiga suur: ' + (file.size / (1024 * 1024)).toFixed(1) + 'MB (max 10MB)');
        return errors;
    }
    function formatFileSize(bytes) {
        if (bytes === 0) return '0 B';
        var k = 1024, sizes = ['B', 'KB', 'MB', 'GB'];
        var i = Math.floor(Math.log(bytes) / Math.log(k));
        return parseFloat((bytes / Math.pow(k, i)).toFixed(1)) + ' ' + sizes[i];
    }
    function handleMultipleFiles(files) {
        if (files.length === 0) return;
        if (uploadProgress) uploadProgress.classList.remove('d-none');
        if (uploadResults) uploadResults.innerHTML = '';
        if (uploadProgressBar) uploadProgressBar.style.width = '0%';
        if (uploadStatusText) uploadStatusText.textContent = 'Kontrollin ' + files.length + ' faili...';
        var validFiles = [], invalidFiles = [];
        files.forEach(function(file) {
            var errors = validateFile(file);
            if (errors.length === 0) validFiles.push(file); else invalidFiles.push({file: file, errors: errors});
        });
        if (invalidFiles.length > 0 && uploadResults) {
            invalidFiles.forEach(function(obj) {
                var errorDiv = document.createElement('div');
                errorDiv.className = 'upload-item error';
                errorDiv.innerHTML = '<div class="d-flex justify-content-between"><span><i class="fas fa-times"></i> ' + obj.file.name + '</span><span class="file-info">' + formatFileSize(obj.file.size) + '</span></div><div class="text-danger small mt-1">' + obj.errors.join(', ') + '</div>';
                uploadResults.appendChild(errorDiv);
            });
        }
        if (validFiles.length === 0) {
            if (uploadStatusText) uploadStatusText.textContent = 'Ühtegi kehtivat pilti ei leitud';
            setTimeout(function() { if (uploadProgress) uploadProgress.classList.add('d-none'); }, 3000);
            return;
        }
        if (uploadStatusText) uploadStatusText.textContent = 'Laen üles ' + validFiles.length + ' pilti...';
        uploadFilesSequentially(validFiles);
    }
    async function uploadFilesSequentially(files) {
        var totalFiles = files.length, completedFiles = 0;
        for (var i = 0; i < files.length; i++) {
            var file = files[i], uploadId = ++uploadCounter;
            var uploadItem = document.createElement('div');
            uploadItem.className = 'upload-item';
            uploadItem.id = 'upload-' + uploadId;
            uploadItem.innerHTML = '<div class="d-flex justify-content-between align-items-center"><span><i class="fas fa-spinner fa-spin"></i> ' + file.name + '</span><span class="file-info">' + formatFileSize(file.size) + '</span></div><div class="progress mt-2" style="height: 4px;"><div class="progress-bar" id="progress-' + uploadId + '" style="width: 0%"></div></div>';
            if (uploadResults) uploadResults.appendChild(uploadItem);
            try {
                var result = await uploadSingleFile(file, uploadId);
                uploadItem.className = 'upload-item success';
                uploadItem.innerHTML = '<div class="d-flex justify-content-between align-items-center"><span><i class="fas fa-check"></i> ' + file.name + '</span><span class="file-info">' + formatFileSize(result.processedSize || file.size) + '</span></div>' + (result.compressionSavings ? '<div class="text-success small mt-1"><i class="fas fa-compress-arrows-alt"></i> Kompressioon: ' + result.compressionSavings + '% väiksem</div>' : '');
                insertImageMarkdown(result.imageId, file.name);
            } catch (error) {
                uploadItem.className = 'upload-item error';
                uploadItem.innerHTML = '<div class="d-flex justify-content-between"><span><i class="fas fa-times"></i> ' + file.name + '</span><span class="file-info">' + formatFileSize(file.size) + '</span></div><div class="text-danger small mt-1">' + error.message + '</div>';
            }
            completedFiles++;
            var overallProgress = (completedFiles / totalFiles) * 100;
            if (uploadProgressBar) uploadProgressBar.style.width = overallProgress + '%';
            if (uploadStatusText) uploadStatusText.textContent = completedFiles + '/' + totalFiles + ' pilti valmis';
        }
        setTimeout(function() { if (uploadProgress) uploadProgress.classList.add('d-none'); }, 3000);
    }
    function uploadSingleFile(file, uploadId) {
        return new Promise(function(resolve, reject) {
            var formData = new FormData();
            formData.append('image', file);
            var xhr = new XMLHttpRequest();
            currentUploads.push(xhr);
            xhr.upload.addEventListener('progress', function(e) {
                if (e.lengthComputable) {
                    var progress = (e.loaded / e.total) * 100;
                    var progressBar = document.getElementById('progress-' + uploadId);
                    if (progressBar) progressBar.style.width = progress + '%';
                }
            });
            xhr.onload = function() {
                currentUploads = currentUploads.filter(function(u) { return u !== xhr; });
                if (xhr.status === 200) {
                    try {
                        var response = JSON.parse(xhr.responseText);
                        if (response.status === 200) {
                            var originalSize = file.size;
                            var processedSize = response.data.processedSize;
                            var savings = originalSize > processedSize ? Math.round((1 - processedSize / originalSize) * 100) : 0;
                            resolve({
                                imageId: response.data.imageId,
                                processedSize: processedSize,
                                compressionSavings: savings > 5 ? savings : null
                            });
                        } else {
                            reject(new Error(response.message || 'Upload failed'));
                        }
                    } catch (e) {
                        reject(new Error('Invalid server response'));
                    }
                } else {
                    reject(new Error('Server error: ' + xhr.status));
                }
            };
            xhr.onerror = function() {
                currentUploads = currentUploads.filter(function(u) { return u !== xhr; });
                reject(new Error('Network error'));
            };
            xhr.onabort = function() {
                currentUploads = currentUploads.filter(function(u) { return u !== xhr; });
                reject(new Error('Upload cancelled'));
            };
            xhr.open('POST', '/images/upload');
            xhr.send(formData);
        });
    }
    function insertImageMarkdown(imageId, fileName) {
        if (!textarea) return;
        var imageMarkdown = '![' + fileName + '](images/' + imageId + ')';
        var cursorPos = textarea.selectionStart;
        var textBefore = textarea.value.substring(0, cursorPos);
        var textAfter = textarea.value.substring(cursorPos);
        var needsNewlineBefore = textBefore.length > 0 && !textBefore.endsWith('\n');
        var needsNewlineAfter = textAfter.length > 0 && !textAfter.startsWith('\n');
        var finalMarkdown = (needsNewlineBefore ? '\n' : '') + imageMarkdown + (needsNewlineAfter ? '\n' : '');
        textarea.value = textBefore + finalMarkdown + textAfter;
        var newCursorPos = cursorPos + finalMarkdown.length;
        textarea.selectionStart = textarea.selectionEnd = newCursorPos;
        textarea.focus();
        textarea.dispatchEvent(new Event('input'));
    }
    function cancelAllUploads() {
        currentUploads.forEach(function(xhr) { try { xhr.abort(); } catch (e) {} });
        currentUploads = [];
        if (uploadStatusText) uploadStatusText.textContent = 'Üleslaadimine tühistatud';
        if (uploadProgressBar) uploadProgressBar.style.width = '0%';
        setTimeout(function() { if (uploadProgress) uploadProgress.classList.add('d-none'); }, 2000);
    }

    // Start in preview-only mode, show only editBtn
    showEditMode(false);

})();
</script>
