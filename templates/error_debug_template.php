<?php require 'templates/partials/master_header.php'; ?>
<body>
<div class="container">
    <div class="error-debug-container mt-4">
        <h2 class="text-danger mb-4">Application Error</h2>

        <div class="card mb-4">
            <div class="card-header bg-danger text-white">
                Error
            </div>
            <div class="card-body">
                <p class="card-text"><?= htmlspecialchars($errorMessage) ?></p>

                <p class="card-text d-flex align-items-center">
                    <?= htmlspecialchars($relativePath) ?><strong><?= htmlspecialchars($pathInfo['basename']) ?></strong>:<strong><?= $errorLine ?></strong>
                    <button class="btn btn-sm btn-outline-secondary ms-2 copy-btn"
                            data-clipboard="<?= $relativeFullPath ?>"
                            title="Copy path">
                        <i class="bi bi-clipboard"></i>
                    </button>
                </p>
            </div>
        </div>

        <?php if (!empty($snippet)): ?>
            <div id="code-context" class="card mb-4">
                <div class="card-header">
                    Code Context
                </div>
                <div class="card-body">
                    <pre class="line-numbers"><code class="language-php"><?php
                            foreach ($snippet as $line => $code) {
                                $isErrorLine = $line === $errorLine;
                                $code = rtrim($code, "\r\n");
                                echo '<div class="line">';
                                echo '<span class="line-number' . ($isErrorLine ? ' error-line' : '') . '">' . $line . '</span>';
                                echo '<span' . ($isErrorLine ? ' class="error-line-content"' : '') . '>' . htmlspecialchars(rtrim($code, "\r\n")) . '</span>';
                                echo '</div>';
                            }
                            ?></code></pre>
                </div>
            </div>
        <?php endif; ?>

        <div id="local-variables" class="card mb-4">
            <div class="card-header">
                Local Variables
            </div>
            <div class="card-body">
                <?php if (!empty($localVariables)): ?>
                    <div class="alert alert-warning">
                        <strong>NB!</strong> Debug-info on nähtav ainult arenduskeskkonnas.
                    </div>
                    <table class="table table-hover variables-table">
                        <thead>
                        <tr>
                            <th scope="col">Variable</th>
                            <th scope="col">Value</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($localVariables as $varName => $varValue): ?>
                            <tr>
                                <td class="variable-name"><?= htmlspecialchars($varName) ?></td>
                                <td class="variable-value">
                                    <pre><?= htmlspecialchars(json_encode($varValue, JSON_PRETTY_PRINT)) ?></pre>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <h5 class="alert-heading">Kohalikud muutujad pole saadaval</h5>
                    <p>Et näha kohalikke muutujaid, lisa järgmine rida koodi, vahetult enne vea tekkimise kohta:</p>
                    <pre class="mt-2 p-2 bg-light"><code>$GLOBALS['vars'] = get_defined_vars();</code></pre>
                <?php endif; ?>
            </div>
        </div>

        <div id="stack-trace" class="card mb-4">
            <div class="card-header">
                Stack Trace
            </div>
            <div class="card-body">
                <table class="table table-hover stack-trace-table">
                    <thead>
                        <tr>
                            <th scope="col" width="50">#</th>
                            <th scope="col">Function Call</th>
                            <th scope="col">Location</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($stackTrace as $index => $trace): ?>
                            <tr>
                                <td class="frame-number">#<?= $index ?></td>
                                <td>
                                    <div class="<?= str_contains($trace['callString'], '::') ? 'frame-class' : 'frame-function' ?>">
                                        <?= htmlspecialchars($trace['callString']) ?>
                                    </div>
                                </td>
                                <td class="frame-file">
                                    <?php if (isset($trace['file'])): ?>
                                        <?= htmlspecialchars($trace['file']) ?>:<?= $trace['line'] ?>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
    document.querySelectorAll('.copy-btn').forEach(button => {
        button.addEventListener('click', function () {
            const textToCopy = this.getAttribute('data-clipboard');
            navigator.clipboard.writeText(textToCopy).then(() => {
                // Optional: Visual feedback
                const originalText = this.innerHTML;
                this.innerHTML = '<i class="bi bi-check"></i>';
                setTimeout(() => {
                    this.innerHTML = originalText;
                }, 1000);
            });
        });
    });
</script>

<style>
    .error-debug-container {
        margin-bottom: 50px;
    }

    .line-numbers, .stack-trace {
        background-color: #f8f9fa;
        padding: 0;
        margin: 0;
        font-family: monospace;
        font-size: 0.9rem;
    }

    .line-number {
        display: inline-block;
        width: 40px;
        color: #6c757d;
        user-select: none;
    }

    .error-line {
        background-color: #ffd7d7;
        color: #dc3545;
    }

    .error-line-content {
        background-color: #ffd7d7;
        padding: 2px;
    }

    pre {
        margin: 0;
        white-space: pre-wrap;
        word-wrap: break-word;
    }

    .stack-trace-table {
        margin-bottom: 0;
        font-size: 0.9rem;
    }

    .stack-trace-table td {
        vertical-align: middle;
    }

    .stack-trace-table .frame-number {
        color: #6c757d;
        font-family: monospace;
    }

    .stack-trace-table .frame-file {
        color: #6c757d;
        font-size: 0.85rem;
        font-family: monospace;
    }

    .variables-table {
        margin-bottom: 0;
    }

    .variables-table .variable-name {
        color: #0d6efd;
        font-weight: bold;
        font-family: monospace;
        white-space: nowrap;
        width: 200px;
        vertical-align: top;
        border-right: 1px solid #dee2e6;
    }

    .variables-table .variable-value {
        font-family: monospace;
        font-size: 0.9rem;
        overflow-x: auto;
        vertical-align: top;
    }

    .variable-value pre {
        margin: 0;
        white-space: pre-wrap;
        word-break: break-all;
    }

    .copy-btn {
        padding: 0.25rem 0.5rem;
        font-size: 0.875rem;
    }

    .copy-btn:hover {
        background-color: #e9ecef;
    }

    #code-context .card-body {
        background-color: #f8f9fa;
    }
</style>

</body>
</html>