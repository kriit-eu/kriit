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
    /* Sticky header that contains both the label and action buttons */
    .md-editor-sticky-header {
        position: -webkit-sticky; /* Safari */
        position: sticky;
        top: 0; /* flush to top of the scroll container */
        z-index: 30;
        background: #ffffff;
        border: 1px solid rgba(0,0,0,0.06);
        padding: 0.5rem 0.75rem;
        border-radius: 6px;
        box-shadow: 0 8px 24px rgba(0,0,0,0.06);
        margin-bottom: 0.75rem;
    }
    .md-editor-sticky-header .form-label {
        margin: 0;
    }
    .md-editor-sticky-header .btn {
        white-space: nowrap;
    }
    /* When inside a Bootstrap modal-body that has its own padding,
       sticky headers can appear with an awkward gap. Use transform to
       visually pull the header up by the modal's padding amount. */
    .modal .modal-body .md-editor-sticky-header {
        /* Pull up by 0.5rem (matches previous top gap) */
        transform: translateY(-1rem);
        /* Extend the header width so rounded corners don't clip in modal */
        margin-left: -0.5rem;
        margin-right: -0.5rem;
        border-radius: 6px;
    }
    /* On very small viewports, reduce the pull so layout isn't broken */
    @media (max-width: 576px) {
        .modal .modal-body .md-editor-sticky-header {
            transform: translateY(-0.25rem);
            margin-left: -0.25rem;
            margin-right: -0.25rem;
        }
    }
</style>
<div class="mb-3" id="<?= htmlspecialchars($editorId) ?>_container">
    <div class="md-editor-sticky-header d-flex justify-content-between align-items-center">
        <label for="<?= htmlspecialchars($editorId) ?>" class="form-label fw-bold mb-0"><?= htmlspecialchars($labelText) ?></label>
        <div class="d-flex" style="gap:0.5em;">
            <button type="button" class="btn btn-outline-secondary btn-sm" id="<?= htmlspecialchars($editorId) ?>_editBtn" style="display:none;">
                <i class="fas fa-edit"></i> Muuda
            </button>
            <button type="button" class="btn btn-outline-secondary btn-sm" id="<?= htmlspecialchars($editorId) ?>_doneBtn" style="display:none;">
                <i class="fas fa-eye"></i> Vaata
            </button>
        </div>
    </div>
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
    <!-- Bottom left action buttons: removed (buttons moved to top) -->
    <div class="row mt-2">
        <div class="col-12">
            <div id="<?= htmlspecialchars($editorId) ?>_actionBtns" class="d-flex flex-row align-items-center" style="gap: 0.5em;">
                <!-- Controls moved to top-right of the label for compact layout -->
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
        // Prefer scoping lookups to this partial's container so multiple instances or other elements with same IDs won't collide
        var container = document.getElementById(editorId + '_container');
        var textarea = container ? container.querySelector('#' + editorId) : document.getElementById(editorId);
        var preview = container ? container.querySelector('#' + previewId) : document.getElementById(previewId);
        var previewFull = container ? container.querySelector('#' + previewId + '_full') : document.getElementById(previewId + '_full');
        var previewOnlyRow = document.getElementById(editorId + '_previewOnlyRow');
        var splitRow = document.getElementById(editorId + '_splitRow');
        var editControls = document.getElementById(editorId + '_editControls');
        var editBtn = document.getElementById(editorId + '_editBtn');
        var doneBtn = document.getElementById(editorId + '_doneBtn');

        // Debounce and cache for preview rendering to avoid flashing while typing
        var _previewTimer = null;
        var _lastRenderedPreview = '';
        var _debounceMs = 150; // adjust for responsiveness vs flicker

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
                // ensure listeners are attached when entering edit mode
                try {
                    attachTextareaListeners();
                } catch (e) {
                    console.error('attach on showEditMode failed', e);
                }
                setTimeout(function() {
                    var ta = document.getElementById(editorId);
                    if (ta) {
                        try {
                            // modern browsers: prevent scrolling when focusing
                            ta.focus({preventScroll: true});
                        } catch (e) {
                            // fallback: preserve nearest scroll container's position
                            try {
                                var root = container || document.getElementById(editorId + '_container');
                                var scrollParent = root && root.closest ? root.closest('.modal-body') : null;
                                if (!scrollParent) scrollParent = document.scrollingElement || document.documentElement || document.body;
                                var prevScroll = scrollParent.scrollTop;
                                ta.focus();
                                // restore after a tick
                                setTimeout(function() {
                                    try { scrollParent.scrollTop = prevScroll; } catch (e) {}
                                }, 0);
                            } catch (err) {
                                try { ta.focus(); } catch (e) {}
                            }
                        }
                    }
                }, 100);
            } else {
                if (previewOnlyRow) previewOnlyRow.style.display = '';
                if (splitRow) splitRow.style.display = 'none';
                if (editControls) editControls.style.display = 'none';
                if (editBtn) editBtn.style.display = '';
                if (doneBtn) doneBtn.style.display = 'none';
            }
            updatePreview();
        }

        if (editBtn) editBtn.addEventListener('click', function() {
            showEditMode(true);
        });
        if (doneBtn) doneBtn.addEventListener('click', function() {
            showEditMode(false);
        });

        // Helper: when preview or textarea resizes inside a Bootstrap modal, updates
        // can cause the modal to scroll/jump. We save and restore the nearest
        // modal-body scroll position around DOM changes to avoid unexpected jumps.
        function _withPreservedModalScroll(fn) {
            try {
                // find the closest ancestor that is a modal-body (Bootstrap modal)
                var root = container || document.getElementById(editorId + '_container');
                var modalBody = root && root.closest ? root.closest('.modal-body') : null;
                // find other possible scroll containers
                var scrollContainers = [];
                if (modalBody) scrollContainers.push({ el: modalBody, top: modalBody.scrollTop, left: modalBody.scrollLeft });
                // add nearest scrollable ancestor of root
                try {
                    var sc = root;
                    while (sc && sc !== document.body) {
                        var cs = window.getComputedStyle(sc);
                        if (cs && (cs.overflowY === 'auto' || cs.overflowY === 'scroll')) {
                            scrollContainers.push({ el: sc, top: sc.scrollTop, left: sc.scrollLeft });
                            break;
                        }
                        sc = sc.parentElement;
                    }
                } catch (e) {}
                // fallback to document scrolling element
                try { scrollContainers.push({ el: document.scrollingElement || document.documentElement || document.body, top: (window.pageYOffset || document.documentElement.scrollTop), left: (window.pageXOffset || document.documentElement.scrollLeft) }); } catch (e) {}

                // remember active element to avoid focus-caused scrolling
                var prevActive = document.activeElement;

                // perform DOM changes
                fn();

                // Restore scroll positions and active element; run multiple times to counter layout/paint
                var restore = function() {
                    for (var i = 0; i < scrollContainers.length; i++) {
                        try {
                            var obj = scrollContainers[i];
                            if (!obj || !obj.el) continue;
                            if (obj.el === document.scrollingElement || obj.el === document.documentElement || obj.el === document.body) {
                                // window scroll
                                try { window.scrollTo(obj.left || 0, obj.top || 0); } catch (e) {}
                            } else {
                                try { obj.el.scrollTop = obj.top; obj.el.scrollLeft = obj.left || 0; } catch (e) {}
                            }
                        } catch (e) {}
                    }
                    // restore focus without scrolling if possible
                    try {
                        if (prevActive && prevActive.focus) {
                            try { prevActive.focus({preventScroll: true}); } catch (e) { try { prevActive.focus(); } catch (e) {} }
                        }
                    } catch (e) {}
                };
                try { restore(); } catch (e) {}
                setTimeout(function() { try { restore(); } catch (e) {} }, 20);
                setTimeout(function() { try { restore(); } catch (e) {} }, 120);
            } catch (e) {
                try {
                    fn();
                } catch (err) {
                    console.error(err);
                }
            }
        }

        function autoExpand() {
            try {
                // re-query elements in case DOM was changed or hidden/displayed; scope to container when possible
                var ta = container ? container.querySelector('#' + editorId) : document.getElementById(editorId);
                var boxPreview = container ? container.querySelector('#' + previewId) : document.getElementById(previewId);
                var boxPreviewFull = container ? container.querySelector('#' + previewId + '_full') : document.getElementById(previewId + '_full');
                if (ta) {
                    ta.style.height = 'auto';
                    if (ta.value.trim() === '') {
                        ta.style.height = '200px';
                    } else {
                        ta.style.height = (ta.scrollHeight + 2) + 'px';
                    }
                }
                [boxPreview, boxPreviewFull].forEach(function(box) {
                    if (!box) return;
                    // If the element is not visible (display:none), skip size calculations
                    var style = window.getComputedStyle(box);
                    if (style.display === 'none' || style.visibility === 'hidden') return;
                    // Run size update inside preserved-scroll wrapper to avoid
                    // modal jumping when preview height changes.
                    _withPreservedModalScroll(function() {
                        box.style.height = 'auto';
                        if (box.innerText.trim() === '' || box.innerHTML.indexOf('Eelvaade ilmub siia') !== -1) {
                            box.style.height = '200px';
                        } else {
                            box.style.height = (box.scrollHeight + 2) + 'px';
                        }
                    });
                });
            } catch (err) {
                console.error('autoExpand error:', err);
            }
        }

        function updatePreview() {
            try {
                // re-query nodes each call to be resilient when rows are toggled; scope to container when possible
                var ta = container ? container.querySelector('#' + editorId) : document.getElementById(editorId);
                var boxPreview = container ? container.querySelector('#' + previewId) : document.getElementById(previewId);
                var boxPreviewFull = container ? container.querySelector('#' + previewId + '_full') : document.getElementById(previewId + '_full');
                var content = ta ? ta.value : '';
                // Debug: show a small snippet and length so we can see if content changes on typing

                var emptyHtml = '<div class="text-muted text-center p-3"><i class="fas fa-eye-slash"></i><br>Eelvaade ilmub siia...</div>';

                if (boxPreview) {
                    if (content.trim() === '') {
                        _withPreservedModalScroll(function() {
                            boxPreview.innerHTML = emptyHtml;
                            boxPreview.style.overflowY = 'hidden';
                        });
                    } else {
                        _withPreservedModalScroll(function() {
                            // render even if element was previously hidden
                            // post-process rendered HTML to convert task-list markers like "[ ]" and "[x]"
                            var rendered = md.render(content);
                            enhanceTaskLists(rendered, boxPreview, ta);
                        });

                    }
                }
                if (boxPreviewFull) {
                    if (content.trim() === '') {
                        _withPreservedModalScroll(function() {
                            boxPreviewFull.innerHTML = emptyHtml;
                            boxPreviewFull.style.overflowY = 'hidden';
                        });
                    } else {
                        _withPreservedModalScroll(function() {
                            var rendered = md.render(content);
                            enhanceTaskLists(rendered, boxPreviewFull, ta);
                        });

                    }
                }
                // Run autoExpand inside the preserved-scroll wrapper as well so
                // any height adjustments won't change the modal scroll.
                _withPreservedModalScroll(autoExpand);
            } catch (err) {
                console.error('updatePreview error:', err);
                // best-effort fallback: set previewFull so user sees something
                try {
                    var fallback = container ? container.querySelector('#' + previewId + '_full') : document.getElementById(previewId + '_full');
                    if (fallback) fallback.innerHTML = '<div class="text-muted text-center p-3">Eelvaade ei ole saadaval (viga)</div>';
                } catch (e) {}
            }

                // Enhance task lists: derive task items from source lines and map them
                // into the rendered <li> elements. This avoids relying on how markdown-it
                // renders the literal "[x]" marker in the generated HTML.
                function enhanceTaskLists(renderedHtml, containerEl, textareaEl) {
                    try {
                        var tmp = document.createElement('div');
                        tmp.innerHTML = renderedHtml;

                        // Use markdown-it tokens to map rendered list items to source lines.
                        // We'll walk tokens and rendered <li> elements in order and match them
                        // by occurrence. This avoids fragile string heuristics and handles
                        // nested/mixed lists because markdown-it token stream preserves
                        // the logical order of list items.
                        var content = textareaEl ? textareaEl.value : '';
                        var sourceLines = content.split('\n');
                        var tokens = md.parse ? md.parse(content, {}) : [];

                        // Build an array of task descriptors in document order by scanning tokens.
                        var taskItems = [];
                        for (var ti = 0; ti < tokens.length; ti++) {
                            var tok = tokens[ti];
                            if (tok && tok.type === 'list_item_open' && Array.isArray(tok.map)) {
                                var start = tok.map[0];
                                var end = tok.map[1];
                                // Prefer the first non-empty source line in the item's range
                                for (var s = start; s < Math.min(end, sourceLines.length); s++) {
                                    var line = sourceLines[s] || '';
                                    var m = line.match(/^\s*(?:[-*+]\s*)?\[([ xX])\]\s*(.*)$/);
                                    if (m) {
                                        taskItems.push({ lineIndex: s, checked: (m[1].toLowerCase() === 'x'), text: m[2] });
                                        break;
                                    }
                                }
                            }
                        }

                        // Now walk rendered <li> elements and attach checkboxes for taskItems in order.
                        var liEls = tmp.querySelectorAll('li');
                        var taskIdx = 0;
                        for (var j = 0; j < liEls.length && taskIdx < taskItems.length; j++) {
                            var li = liEls[j];
                            var item = taskItems[taskIdx];
                            // Heuristic: ensure this <li> actually corresponds to an item that
                            // contains a task marker by checking its textContent for a [ ] or [x]
                            // early in the rendered text. This guards against non-task <li>s
                            // consuming taskItems in complex nesting situations.
                            var textPreview = (li.textContent || '').trim();
                            if (!textPreview.match(/^[\s\-\*\+]*\[[ xX]\]/)) {
                                // not a task-looking <li>, skip it
                                continue;
                            }

                            // Replace the li contents with a checkbox + rendered inline label
                            li.innerHTML = '';
                            var cb = document.createElement('input');
                            cb.type = 'checkbox';
                            cb.checked = !!item.checked;
                            cb.className = 'task-checkbox me-2';
                            // Add a data attribute to help debug mapping between DOM and source
                            cb.setAttribute('data-source-line', String(item.lineIndex));
                            li.appendChild(cb);
                            var span = document.createElement('span');
                            try {
                                span.innerHTML = md.renderInline(item.text || '');
                            } catch (e) {
                                span.textContent = item.text || '';
                            }
                            li.appendChild(span);

                            // Wire toggle back to the specific source line
                            (function(cbRef, srcIndex) {
                                cbRef.addEventListener('change', function() {
                                    try {
                                        var lines = textareaEl.value.split('\n');
                                        if (typeof lines[srcIndex] !== 'undefined') {
                                            lines[srcIndex] = lines[srcIndex].replace(/\[[ xX]\]/, cbRef.checked ? '[x]' : '[ ]');
                                            textareaEl.value = lines.join('\n');
                                            textareaEl.dispatchEvent(new Event('input'));
                                        }
                                    } catch (err) {
                                        console.error('task checkbox handler failed', err);
                                    }
                                });
                            })(cb, item.lineIndex);

                            taskIdx++;
                        }

                        // Finally set the container content by moving nodes from tmp to containerEl
                        try {
                            while (containerEl.firstChild) containerEl.removeChild(containerEl.firstChild);
                            while (tmp.firstChild) containerEl.appendChild(tmp.firstChild);
                        } catch (e) {
                            containerEl.innerHTML = tmp.innerHTML;
                        }
                    } catch (err) {
                        try { containerEl.innerHTML = renderedHtml; } catch (e) {}
                    }
                }
        }

        // Helper: attach listeners to the textarea (called on init and when entering edit mode)
        function attachTextareaListeners() {
            try {
                var ta = container ? container.querySelector('#' + editorId) : document.getElementById(editorId);
                if (!ta) return;
                // Prevent double-attaching by using a marker
                if (ta._md_listeners_attached) return;
                ta._md_listeners_attached = true;
                ta.addEventListener('input', function() {
                    scheduleUpdatePreview();
                });
                // fallback for typing: ensure key events trigger update
                ta.addEventListener('keyup', function() {
                    try {
                        scheduleUpdatePreview();
                    } catch (e) {}
                });
                ta.addEventListener('paste', function() {
                    setTimeout(function() {
                        scheduleUpdatePreview(true);
                    }, 10);
                    setTimeout(function() {
                        scheduleUpdatePreview(true);
                    }, 800);
                });
                ta.addEventListener('mouseup', function() {
                    scheduleUpdatePreview();
                });
                ta.addEventListener('focus', function() {
                    scheduleUpdatePreview(true);
                });

            } catch (err) {
                console.error('attachTextareaListeners error:', err);
            }
        }

        // Fallback: container-level delegation so input still triggers when listeners missed
        if (container) {
            container.addEventListener('input', function(e) {
                try {
                    if (e && e.target && e.target.id === editorId) scheduleUpdatePreview();
                } catch (err) {}
            });
        }

        // Polling fallback: detect programmatic changes to the textarea.value that may not dispatch events
        (function setupValuePoller() {
            var lastVal = null;
            setInterval(function() {
                try {
                    var ta = container ? container.querySelector('#' + editorId) : document.getElementById(editorId);
                    if (!ta) return;
                    if (ta.value !== lastVal) {
                        lastVal = ta.value;
                        scheduleUpdatePreview();
                    }
                } catch (e) {}
            }, 250);
        })();

        // Initialize listeners and preview after small delay to allow layout
        // scheduleUpdatePreview: debounce + optional immediate
        function scheduleUpdatePreview(immediate) {
            try {
                var ta = container ? container.querySelector('#' + editorId) : document.getElementById(editorId);
                var content = ta ? ta.value : '';
                if (_lastRenderedPreview === content && !immediate) {
                    // nothing changed
                    return;
                }
                if (immediate) {
                    if (_previewTimer) {
                        clearTimeout(_previewTimer);
                        _previewTimer = null;
                    }
                    _lastRenderedPreview = content;
                    updatePreview();
                    return;
                }
                if (_previewTimer) clearTimeout(_previewTimer);
                _previewTimer = setTimeout(function() {
                    try {
                        _lastRenderedPreview = content;
                        updatePreview();
                    } catch (e) {
                        console.error('scheduled updatePreview failed', e);
                    }
                }, _debounceMs);
            } catch (e) {
                try {
                    updatePreview();
                } catch (err) {}
            }
        }

        setTimeout(function() {
            attachTextareaListeners();
            autoExpand();
            scheduleUpdatePreview(true);
        }, 0);

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
                e.preventDefault();
                e.stopPropagation();
                textarea.classList.add('image-paste-active');
            });
            textarea.addEventListener('dragenter', function(e) {
                e.preventDefault();
                e.stopPropagation();
                textarea.classList.add('image-paste-active');
            });
            textarea.addEventListener('dragleave', function(e) {
                e.preventDefault();
                e.stopPropagation();
                if (!textarea.contains(e.relatedTarget)) textarea.classList.remove('image-paste-active');
            });
            textarea.addEventListener('drop', function(e) {
                e.preventDefault();
                e.stopPropagation();
                textarea.classList.remove('image-paste-active');
                var files = Array.from(e.dataTransfer.files).filter(function(file) {
                    return file.type.indexOf('image') !== -1;
                });
                if (files.length > 0) handleMultipleFiles(files);
            });
        }
        if (cancelUploadBtn) {
            cancelUploadBtn.addEventListener('click', function() {
                cancelAllUploads();
            });
        }

        function validateFile(file) {
            var errors = [];
            if (supportedTypes.indexOf(file.type) === -1) errors.push('Toetamata failitüüp: ' + file.type);
            if (file.size > 10 * 1024 * 1024) errors.push('Fail on liiga suur: ' + (file.size / (1024 * 1024)).toFixed(1) + 'MB (max 10MB)');
            return errors;
        }

        function formatFileSize(bytes) {
            if (bytes === 0) return '0 B';
            var k = 1024,
                sizes = ['B', 'KB', 'MB', 'GB'];
            var i = Math.floor(Math.log(bytes) / Math.log(k));
            return parseFloat((bytes / Math.pow(k, i)).toFixed(1)) + ' ' + sizes[i];
        }

        function handleMultipleFiles(files) {
            if (files.length === 0) return;
            if (uploadProgress) uploadProgress.classList.remove('d-none');
            if (uploadResults) uploadResults.innerHTML = '';
            if (uploadProgressBar) uploadProgressBar.style.width = '0%';
            if (uploadStatusText) uploadStatusText.textContent = 'Kontrollin ' + files.length + ' faili...';
            var validFiles = [],
                invalidFiles = [];
            files.forEach(function(file) {
                var errors = validateFile(file);
                if (errors.length === 0) validFiles.push(file);
                else invalidFiles.push({
                    file: file,
                    errors: errors
                });
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
                setTimeout(function() {
                    if (uploadProgress) uploadProgress.classList.add('d-none');
                }, 3000);
                return;
            }
            if (uploadStatusText) uploadStatusText.textContent = 'Laen üles ' + validFiles.length + ' pilti...';
            uploadFilesSequentially(validFiles);
        }
        async function uploadFilesSequentially(files) {
            var totalFiles = files.length,
                completedFiles = 0;
            for (var i = 0; i < files.length; i++) {
                var file = files[i],
                    uploadId = ++uploadCounter;
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
            setTimeout(function() {
                if (uploadProgress) uploadProgress.classList.add('d-none');
            }, 3000);
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
                    currentUploads = currentUploads.filter(function(u) {
                        return u !== xhr;
                    });
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
                    currentUploads = currentUploads.filter(function(u) {
                        return u !== xhr;
                    });
                    reject(new Error('Network error'));
                };
                xhr.onabort = function() {
                    currentUploads = currentUploads.filter(function(u) {
                        return u !== xhr;
                    });
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
            currentUploads.forEach(function(xhr) {
                try {
                    xhr.abort();
                } catch (e) {}
            });
            currentUploads = [];
            if (uploadStatusText) uploadStatusText.textContent = 'Üleslaadimine tühistatud';
            if (uploadProgressBar) uploadProgressBar.style.width = '0%';
            setTimeout(function() {
                if (uploadProgress) uploadProgress.classList.add('d-none');
            }, 2000);
        }

        // Start in preview-only mode, show only editBtn
        showEditMode(false);

    })();
</script>