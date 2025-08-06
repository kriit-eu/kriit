// Loads CSSLint from the local bundle and exposes it as window.CSSLint
// Usage: include this script after csslint.js
if (typeof window !== 'undefined' && typeof CSSLint !== 'undefined') {
    window.CSSLint = CSSLint;
}
