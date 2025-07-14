// Full-featured Markdown editor using markdown-it and plugins
// Usage: markdownEditorInitFull({ editorId, previewId })
function markdownEditorInitFull({ editorId, previewId }) {
  // Load markdown-it and plugins
  const md = window
    .markdownit({
      html: true,
      linkify: true,
      typographer: true,
      breaks: true,
    })
    .use(window.markdownitFootnote)
    .use(window.markdownitDeflist)
    .use(window.markdownitEmoji)
    .use(window.markdownitSub)
    .use(window.markdownitSup);

  const textarea = document.getElementById(editorId);
  const preview = document.getElementById(previewId);
  if (!textarea || !preview) return;
  function autoExpand() {
    textarea.style.height = "auto";
    textarea.style.height = textarea.scrollHeight + 2 + "px";
    preview.style.height = "auto";
    if (preview.innerHTML.trim() !== "") {
      preview.style.height = preview.scrollHeight + 2 + "px";
    } else {
      preview.style.height = "200px";
    }
  }
  function updatePreview() {
    const content = textarea.value;
    if (content.trim() === "") {
      preview.innerHTML = `<div class="text-muted text-center p-3"><i class="fas fa-eye-slash"></i><br>Eelvaade ilmub siia...</div>`;
      preview.style.overflowY = "hidden";
    } else {
      preview.innerHTML = md.render(content);
    }
    autoExpand();
  }
  textarea.addEventListener("input", updatePreview);
  textarea.addEventListener("paste", function () {
    setTimeout(updatePreview, 10);
    setTimeout(updatePreview, 800);
  });
  textarea.addEventListener("mouseup", function () {
    preview.style.height = textarea.style.height;
  });
  setTimeout(autoExpand, 0);
  updatePreview();
}
