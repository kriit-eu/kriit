<?php
/**
 * Reusable Markdown editor + live preview partial
 * Usage: include with required field IDs and names
 * Example: include 'templates/partials/markdown_editor.php';
 * Required variables:
 *   $editorId, $previewId, $fieldName, $labelText, $initialValue
 */
?>
<!-- Import markdown-it and plugins for full-featured Markdown preview -->
<script src="/assets/js/markdown-it.min.js"></script>
<script src="/assets/js/markdown-it-footnote.min.js"></script>
<script src="/assets/js/markdown-it-emoji.min.js"></script>
<script src="/assets/js/markdown-it-sub.min.js"></script>
<script src="/assets/js/markdown-it-deflist.min.js"></script>
<script src="/assets/js/markdown-it-sup.min.js"></script>
<style>
.markdown-content table {
  width: auto;
  border-collapse: collapse;
  margin-bottom: 1em;
}
.markdown-content th,
.markdown-content td {
  border: 1px solid #dee2e6;
  padding: 0.5em 0.75em;
  background: #f3f4f6;
}
</style>
<div class="mb-3">
    <label for="<?= htmlspecialchars($editorId) ?>" class="form-label fw-bold"><?= htmlspecialchars($labelText) ?></label>
    <div class="row">
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
</div>
<script>
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
    var textarea = document.getElementById('<?= addslashes($editorId) ?>');
    var preview = document.getElementById('<?= addslashes($previewId) ?>');
    if (!textarea || !preview) return;
    function autoExpand() {
        textarea.style.height = 'auto';
        textarea.style.height = (textarea.scrollHeight + 2) + 'px';
        preview.style.height = 'auto';
        if (preview.innerHTML.trim() !== '') {
            preview.style.height = (preview.scrollHeight + 2) + 'px';
        } else {
            preview.style.height = '200px';
        }
    }
    function updatePreview() {
        var content = textarea.value;
        if (content.trim() === '') {
            preview.innerHTML = `<div class=\"text-muted text-center p-3\"><i class=\"fas fa-eye-slash\"></i><br>Eelvaade ilmub siia...</div>`;
            preview.style.overflowY = 'hidden';
        } else {
            preview.innerHTML = md.render(content);
        }
        autoExpand();
    }
    textarea.addEventListener('input', updatePreview);
    textarea.addEventListener('paste', function() {
        setTimeout(updatePreview, 10);
        setTimeout(updatePreview, 800);
    });
    textarea.addEventListener('mouseup', function() {
        preview.style.height = textarea.style.height;
    });
    setTimeout(autoExpand, 0);
    updatePreview();
})();
</script>
