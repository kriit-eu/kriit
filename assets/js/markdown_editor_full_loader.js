// Loader for markdown-it and plugins, then initializes the editor
function loadMarkdownEditorFull(callback) {
  const scripts = [
    "/assets/js/markdown-it.min.js",
    "/assets/js/markdown-it-footnote.min.js",
    "/assets/js/markdown-it-deflist.min.js",
    "/assets/js/markdown-it-emoji.min.js",
    "/assets/js/markdown-it-sub.min.js",
    "/assets/js/markdown-it-sup.min.js",
    "/assets/js/markdown_editor_full.js",
  ];
  let loaded = 0;
  function next() {
    if (loaded >= scripts.length) {
      if (callback) callback();
      return;
    }
    const script = document.createElement("script");
    script.src = scripts[loaded];
    script.onload = function () {
      loaded++;
      next();
    };
    document.head.appendChild(script);
  }
  next();
}
