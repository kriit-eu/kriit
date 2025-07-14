// Reusable Markdown editor + preview logic
// Usage: markdownEditorInit({ editorId, previewId })
function markdownEditorInit({ editorId, previewId }) {
  const textarea = document.getElementById(editorId);
  const preview = document.getElementById(previewId);
  if (!textarea || !preview) return;
  function parseMarkdown(text) {
    if (!text) return "";
    let html = text;
    html = html.replace(/^#### (.*$)/gim, "<h4>$1</h4>");
    html = html.replace(/^### (.*$)/gim, "<h3>$1</h3>");
    html = html.replace(/^## (.*$)/gim, "<h2>$1</h2>");
    html = html.replace(/^# (.*$)/gim, "<h1>$1</h1>");
    html = html.replace(/\*\*(.*?)\*\*/g, "<strong>$1</strong>");
    html = html.replace(/__(.*?)__/g, "<strong>$1</strong>");
    html = html.replace(/\*(.*?)\*/g, "<em>$1</em>");
    html = html.replace(/_(.*?)_/g, "<em>$1</em>");
    html = html.replace(/```([\s\S]*?)```/g, "<pre><code>$1</code></pre>");
    html = html.replace(/`(.*?)`/g, "<code>$1</code>");
    html = html.replace(
      /!\[([^\]]*)\]\(([^)]+)\)/g,
      function (match, alt, src) {
        return (
          '<img src="' +
          src +
          '" alt="' +
          alt +
          '" class="message-image img-fluid rounded" style="max-height: 300px; cursor: pointer;" onclick="window.open(this.src, \'_blank\')">'
        );
      }
    );
    html = html.replace(
      /\[([^\]]+)\]\(([^)]+)\)/g,
      '<a href="$2" target="_blank">$1</a>'
    );
    html = html.replace(/^\* (.+)$/gm, "<li>$1</li>");
    html = html.replace(/(<li>.*<\/li>)/s, "<ul>$1</ul>");
    html = html.replace(/^\d+\. (.+)$/gm, "<li>$1</li>");
    html = html.replace(/(<li>.*<\/li>)/s, function (match) {
      if (match.includes("<ul>")) return match;
      return "<ol>" + match + "</ol>";
    });
    html = html.replace(/^> (.+)$/gm, "<blockquote>$1</blockquote>");
    html = html.replace(/^---+$/gm, "<hr>");
    html = html.replace(/\n{3,}/g, "\n\n");
    html = html.replace(/([^>])\n([^<])/g, "$1<br>$2");
    return html;
  }
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
    const content = textarea.value.trim();
    if (content === "") {
      preview.innerHTML = `<div class="text-muted text-center p-3"><i class="fas fa-eye-slash"></i><br>Eelvaade ilmub siia...</div>`;
      preview.style.overflowY = "hidden";
    } else {
      preview.innerHTML = parseMarkdown(content);
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
