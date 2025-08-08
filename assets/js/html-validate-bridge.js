import { HtmlValidate } from "html-validate/browser";

export async function validateHtmlString(html) {
  const htmlvalidate = new HtmlValidate({
    extends: ["html-validate:recommended"],
    elements: ["html5"],
  });
  const report = await htmlvalidate.validateString(html);
  return report;
}
