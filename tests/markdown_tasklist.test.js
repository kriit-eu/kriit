import { test, expect } from "bun:test";
import fs from "fs";
import { JSDOM } from "jsdom";

// Load markdown-it from the bundled minified file into the JSDOM window
const mdSrc = fs.readFileSync("assets/js/markdown-it.min.js", "utf8");

test("enhanceTaskLists maps task items to source lines and toggles update textarea", async () => {
  const dom = new JSDOM(`<!doctype html><html><body></body></html>`, {
    runScripts: "outside-only",
  });
  const window = dom.window;
  const document = window.document;

  // Evaluate markdown-it in the JSDOM window so window.markdownit is available
  window.eval(mdSrc);
  const md = window.markdownit
    ? window.markdownit({
        html: true,
        linkify: true,
        typographer: true,
        breaks: true,
      })
    : null;
  expect(md).not.toBeNull();

  // Copy the enhanceTaskLists implementation (same logic as in the PHP partial)
  function enhanceTaskLists(renderedHtml, containerEl, textareaEl, mdInstance) {
    try {
      var tmp = document.createElement("div");
      tmp.innerHTML = renderedHtml;

      var content = textareaEl ? textareaEl.value : "";
      var sourceLines = content.split("\n");
      var tokens = mdInstance.parse ? mdInstance.parse(content, {}) : [];

      var taskItems = [];
      for (var ti = 0; ti < tokens.length; ti++) {
        var tok = tokens[ti];
        if (tok && tok.type === "list_item_open" && Array.isArray(tok.map)) {
          var start = tok.map[0];
          var end = tok.map[1];
          for (var s = start; s < Math.min(end, sourceLines.length); s++) {
            var line = sourceLines[s] || "";
            var m = line.match(/^\s*(?:[-*+]\s*)?\[([ xX])\]\s*(.*)$/);
            if (m) {
              taskItems.push({
                lineIndex: s,
                checked: m[1].toLowerCase() === "x",
                text: m[2],
              });
              break;
            }
          }
        }
      }

      var liEls = tmp.querySelectorAll("li");
      var taskIdx = 0;
      for (var j = 0; j < liEls.length && taskIdx < taskItems.length; j++) {
        var li = liEls[j];
        var item = taskItems[taskIdx];
        var textPreview = (li.textContent || "").trim();
        if (!textPreview.match(/^[\s\-\*\+]*\[[ xX]\]/)) {
          continue;
        }

        li.innerHTML = "";
        var cb = document.createElement("input");
        cb.type = "checkbox";
        cb.checked = !!item.checked;
        cb.className = "task-checkbox me-2";
        cb.setAttribute("data-source-line", String(item.lineIndex));
        li.appendChild(cb);
        var span = document.createElement("span");
        try {
          span.innerHTML = mdInstance.renderInline(item.text || "");
        } catch (e) {
          span.textContent = item.text || "";
        }
        li.appendChild(span);

        (function (cbRef, srcIndex) {
          cbRef.addEventListener("change", function () {
            try {
              var lines = textareaEl.value.split("\n");
              if (typeof lines[srcIndex] !== "undefined") {
                lines[srcIndex] = lines[srcIndex].replace(
                  /\[[ xX]\]/,
                  cbRef.checked ? "[x]" : "[ ]"
                );
                textareaEl.value = lines.join("\n");
                textareaEl.dispatchEvent(new window.Event("input"));
              }
            } catch (err) {
              // swallow for test
            }
          });
        })(cb, item.lineIndex);

        taskIdx++;
      }

      // Move nodes into containerEl
      try {
        while (containerEl.firstChild)
          containerEl.removeChild(containerEl.firstChild);
        while (tmp.firstChild) containerEl.appendChild(tmp.firstChild);
      } catch (e) {
        containerEl.innerHTML = tmp.innerHTML;
      }
    } catch (err) {
      try {
        containerEl.innerHTML = renderedHtml;
      } catch (e) {}
    }
  }

  // Complex markdown sample that includes multiple edge cases
  const sample = [
    "- [ ] Task with a link: [md](https://example.com)",
    "- [x] Task with inline code: `code()`",
    "- [ ] Task with image: ![alt](image.png)",
    "-  [ ] Extra spaces before bracket",
    "- [ ] Task with trailing spaces  ",
    "  - [ ] Nested task level 2",
    "* [ ] Mixed marker star",
    "+ [x] Plus marker checked",
  ].join("\n");

  const textarea = document.createElement("textarea");
  textarea.value = sample;
  const container = document.createElement("div");

  const rendered = md.render(textarea.value);
  enhanceTaskLists(rendered, container, textarea, md);

  // Collect checkboxes and source-line mapping
  const boxes = Array.from(container.querySelectorAll("input.task-checkbox"));
  expect(boxes.length).toBeGreaterThan(0);

  // Compute token-derived task items (same logic as enhanceTaskLists)
  const lines = sample.split("\n");
  const tokenTaskLines = [];
  const tokens = md.parse ? md.parse(sample, {}) : [];
  for (let ti = 0; ti < tokens.length; ti++) {
    const tok = tokens[ti];
    if (tok && tok.type === "list_item_open" && Array.isArray(tok.map)) {
      const start = tok.map[0];
      const end = tok.map[1];
      for (let s = start; s < Math.min(end, lines.length); s++) {
        const line = lines[s] || "";
        const m = line.match(/^\s*(?:[-*+]\s*)?\[([ xX])\]\s*(.*)$/);
        if (m) {
          tokenTaskLines.push({
            lineIndex: s,
            checked: m[1].toLowerCase() === "x",
          });
          break;
        }
      }
    }
  }

  // Ensure there is at least one rendered checkbox
  expect(boxes.length).toBeGreaterThan(0);
  // Ensure each rendered checkbox corresponds to a token-derived task line
  const tokenLineIndices = tokenTaskLines.map((t) => t.lineIndex);
  boxes.forEach((cb) => {
    const src = Number(cb.getAttribute("data-source-line"));
    expect(tokenLineIndices.includes(src)).toBe(true);
    const tokenItem = tokenTaskLines.find((t) => t.lineIndex === src);
    if (tokenItem) expect(cb.checked).toBe(tokenItem.checked);
  });

  // Toggle the first rendered checkbox (simulate user checking an unchecked item)
  const first = boxes[0];
  const firstLineIndex = Number(first.getAttribute("data-source-line"));
  // Ensure initial state matches textarea
  const initialLine = textarea.value.split("\n")[firstLineIndex];
  const initiallyChecked = /\[[xX]\]/.test(initialLine);
  expect(first.checked).toBe(initiallyChecked);
  // Flip the checkbox and dispatch change
  first.checked = !first.checked;
  first.dispatchEvent(new window.Event("change", { bubbles: true }));
  const updatedLine = textarea.value.split("\n")[firstLineIndex];
  expect(/\[[xX]\]/.test(updatedLine)).toBe(first.checked);
});
