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

                <p class="card-text">
                    <?= htmlspecialchars($errorFile) ?>:<?= $errorLine ?>
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

        <div id="stack-trace" class="card mb-4">
            <div class="card-header">
                Stack Trace
            </div>
            <div class="card-body">
                <div class="stack-trace">
                    <?php foreach ($e->getTrace() as $index => $trace): ?>
                        <?php
                        // Skip empty frames
                        if (empty($trace['function'])) continue;

                        // Skip closure frames since they're represented by array_filter
                        //if (str_contains($trace['function'] ?? '', '{closure}')) continue;
                        ?>
                        <div class="stack-frame">
                            <div class="frame-number">#<?= $index ?></div>
                            <div class="frame-content">
                                <div class="frame-call">
                                    <?php
                                    // Build the function call string
                                    $callString = '';
                                    if (isset($trace['class'])) {
                                        $callString .= htmlspecialchars($trace['class']);
                                        $callString .= htmlspecialchars($trace['type']);
                                    }
                                    $callString .= htmlspecialchars($trace['function']);

                                    // Add function arguments if available
                                    if (isset($trace['args'])) {
                                        $args = array_map(function($arg) {
                                            if (is_array($arg)) {
                                                return json_encode($arg);
                                            }
                                            return htmlspecialchars(json_encode($arg));
                                        }, $trace['args']);
                                        $callString .= '(' . implode(', ', $args) . ')';
                                    }
                                    ?>
                                    <div class="<?= isset($trace['class']) ? 'frame-class' : 'frame-function' ?>">
                                        <?= $callString ?>
                                    </div>
                                </div>
                                <?php if (isset($trace['file'])): ?>
                                    <div class="frame-file">
                                        <?= htmlspecialchars($trace['file']) ?>:<?= $trace['line'] ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <?php
        // Capture the function stack using xdebug
        $stack = xdebug_get_function_stack();
        $localVariables = [];

        // Find the frame where the error occurred
        foreach ($stack as $frame) {
            if (isset($frame['function']) && $frame['function'] === $e->getTrace()[0]['function']) {
                $localVariables = $frame['params'] ?? [];
                break;
            }
        }
        ?>

        <div id="local-variables" class="card mb-4">
            <div class="card-header">
                Local Variables
            </div>
            <div class="card-body">
                <?php if (!empty($localVariables)): ?>
                    <ul>
                        <?php foreach ($localVariables as $varName => $varValue): ?>
                            <li><strong><?= htmlspecialchars($varName) ?>:</strong> <?= htmlspecialchars(json_encode($varValue)) ?></li>
                        <?php endforeach; ?>
                    </ul>
                <?php else: ?>
                    <p>No local variables available.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

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

    .stack-frame {
        display: flex;
        padding: 0;
        border-bottom: 1px solid #eee;
    }

    .stack-frame:last-child {
        border-bottom: none;
    }

    .frame-number {
        color: #6c757d;
        min-width: 2.5rem;
    }

    .frame-content {
        flex: 1;
    }

    .frame-call {
        margin-bottom: 0.25rem;
    }

    .frame-class {
        color: #007bff;
    }

    .frame-function {
        color: #28a745;
    }

    .frame-file {
        color: #6c757d;
        font-size: 0.85rem;
    }

    #code-context, #stack-trace {
        background-color: #f8f9fa;
    }
</style>

</body>
</html>
