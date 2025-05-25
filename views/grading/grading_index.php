<style>
    /* Add a light gradient background to the page */
    body {
        background: linear-gradient(135deg, #f5f7fa 0%, #e4e9f2 100%);
        min-height: 100vh;
    }

    /* Ensure container has a transparent background to show the gradient */
    #container {
        background-color: transparent;
    }

    /* Make sure table cells have white background to stand out */
    #grading-table td {
        background-color: white;
    }

    #grading-table th {
        background-color: #f2f2f2;
    }

    /* Add subtle shadow to tables for better contrast with background */
    #grading-table {
        background-color: transparent;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.07);
    }

    /* Ensure the table-responsive container is also transparent */
    .table-responsive {
        background-color: transparent;
    }

    /* More subtle cell borders */
    #grading-table td,
    #grading-table th {
        border-color: #d8d8d8 !important;
    }

    /* Ensure table layout is fixed for consistent column widths but allows scaling */
    #grading-table {
        table-layout: fixed !important;
        border: 1px solid #d8d8d8 !important; /* More subtle border color */
        border-collapse: collapse !important; /* Ensure borders collapse properly */
        width: 100% !important; /* Use full width */
    }

    /* Set width for the position column (first column) */
    #grading-table td:first-child,
    #grading-table th:first-child {
        width: 60px;
        min-width: 60px;
        max-width: 60px;
        text-align: center;
        vertical-align: middle;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        font-weight: bold;
        color: #666;
    }

    /* Set width for the timestamp column (second column) */
    #grading-table td:nth-child(2),
    #grading-table th:nth-child(2) {
        width: 130px;
        min-width: 130px;
        max-width: 130px;
        text-align: center;
        vertical-align: middle;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    /* Set width for the age column (third column) */
    #grading-table td:nth-child(3),
    #grading-table th:nth-child(3) {
        width: 60px;
        min-width: 60px;
        max-width: 60px;
        text-align: center;
        vertical-align: middle;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        font-weight: normal;
        color: #666;
    }

    /* Set width for the student name column (fourth column) - made narrower */
    #grading-table td:nth-child(4),
    #grading-table th:nth-child(4) {
        width: 140px;
        min-width: 140px;
        max-width: 140px;
        vertical-align: middle;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    /* Set width for the combined subject/assignment column (fifth column) */
    #grading-table td:nth-child(5),
    #grading-table th:nth-child(5) {
        vertical-align: middle;
        overflow: hidden;
        text-overflow: ellipsis;
        line-height: 1.3;
    }

    /* Style for assignment name (bold) */
    .assignment-name {
        font-weight: bold;
        display: inline;
        margin-right: 8px;
        text-decoration: none;
        color: inherit;
    }

    /* Ensure assignment name links maintain styling on hover */
    .assignment-name:hover {
        text-decoration: underline;
        color: inherit;
    }

    /* Style for subject name badge - keep neutral gray */
    .subject-name {
        display: inline-block;
        background-color: #e9ecef;
        color: #495057;
        padding: 2px 8px;
        border-radius: 12px;
        font-size: 0.85em;
        font-weight: normal;
        vertical-align: middle;
    }

    /* Rainbow colors for table rows - more visible tints */
    .subject-row-0 { background-color: rgba(231, 76, 60, 0.25) !important; } /* Red */
    .subject-row-1 { background-color: rgba(230, 126, 34, 0.25) !important; } /* Orange */
    .subject-row-2 { background-color: rgba(241, 196, 15, 0.3) !important; } /* Yellow */
    .subject-row-3 { background-color: rgba(46, 204, 113, 0.25) !important; } /* Green */
    .subject-row-4 { background-color: rgba(52, 152, 219, 0.25) !important; } /* Blue */
    .subject-row-5 { background-color: rgba(155, 89, 182, 0.25) !important; } /* Purple */
    .subject-row-6 { background-color: rgba(233, 30, 99, 0.25) !important; } /* Pink */
    .subject-row-7 { background-color: rgba(0, 188, 212, 0.25) !important; } /* Cyan */
    .subject-row-8 { background-color: rgba(255, 152, 0, 0.25) !important; } /* Deep Orange */
    .subject-row-9 { background-color: rgba(96, 125, 139, 0.25) !important; } /* Blue Grey */
    .subject-row-10 { background-color: rgba(139, 195, 74, 0.25) !important; } /* Light Green */
    .subject-row-11 { background-color: rgba(255, 87, 34, 0.25) !important; } /* Deep Orange Red */
    .subject-row-12 { background-color: rgba(121, 85, 72, 0.25) !important; } /* Brown */
    .subject-row-13 { background-color: rgba(0, 150, 136, 0.25) !important; } /* Teal */
    .subject-row-14 { background-color: rgba(103, 58, 183, 0.25) !important; } /* Deep Purple */
    .subject-row-15 { background-color: rgba(255, 64, 129, 0.25) !important; } /* Pink Accent */
    .subject-row-16 { background-color: rgba(76, 175, 80, 0.25) !important; } /* Material Green */
    .subject-row-17 { background-color: rgba(33, 150, 243, 0.25) !important; } /* Material Blue */
    .subject-row-18 { background-color: rgba(255, 111, 0, 0.3) !important; } /* Amber */
    .subject-row-19 { background-color: rgba(55, 71, 79, 0.25) !important; } /* Dark Blue Grey */
    .subject-row-20 { background-color: rgba(211, 47, 47, 0.25) !important; } /* Dark Red */
    .subject-row-21 { background-color: rgba(123, 31, 162, 0.25) !important; } /* Dark Purple */
    .subject-row-22 { background-color: rgba(56, 142, 60, 0.25) !important; } /* Dark Green */
    .subject-row-23 { background-color: rgba(25, 118, 210, 0.25) !important; } /* Dark Blue */

    /* Ensure table cells maintain white background within colored rows */
    .subject-row-0 td, .subject-row-1 td, .subject-row-2 td, .subject-row-3 td,
    .subject-row-4 td, .subject-row-5 td, .subject-row-6 td, .subject-row-7 td,
    .subject-row-8 td, .subject-row-9 td, .subject-row-10 td, .subject-row-11 td,
    .subject-row-12 td, .subject-row-13 td, .subject-row-14 td, .subject-row-15 td,
    .subject-row-16 td, .subject-row-17 td, .subject-row-18 td, .subject-row-19 td,
    .subject-row-20 td, .subject-row-21 td, .subject-row-22 td, .subject-row-23 td {
        background-color: transparent !important;
    }

    /* Make table rows clickable */
    #grading-table tbody tr {
        cursor: pointer;
        transition: opacity 0.2s ease;
    }

    #grading-table tbody tr:hover {
        opacity: 0.8;
    }

    /* Modal styling */
    .modal-header {
        background-color: #f8f9fa;
        border-bottom: 1px solid #dee2e6;
    }

    .modal-body {
        max-height: 70vh;
        overflow-y: auto;
    }

    .message-container {
        max-height: 300px;
        overflow-y: auto;
        padding: 0;
        background-color: transparent;
    }

    .message-item {
        padding: 0.75rem 0;
        margin-bottom: 0.75rem;
        border-bottom: 1px solid #e9ecef;
    }

    .message-item:last-child {
        margin-bottom: 0;
        border-bottom: none;
    }

    .message-author {
        font-weight: bold;
        color: #495057;
        font-size: 0.9em;
    }

    .message-time {
        color: #6c757d;
        font-size: 0.8em;
    }

    .message-content {
        margin-top: 0.5rem;
        color: #212529;
    }

    .loading-spinner {
        display: inline-block;
        width: 1rem;
        height: 1rem;
        border: 2px solid #f3f3f3;
        border-top: 2px solid #007bff;
        border-radius: 50%;
        animation: spin 1s linear infinite;
    }

    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }

    /* Solution URL styling */
    #solutionUrlInput {
        font-size: 0.85em;
        background-color: #f8f9fa;
    }

    /* Markdown content styling */
    .markdown-content {
        line-height: 1.6;
    }

    .markdown-content h1, .markdown-content h2, .markdown-content h3,
    .markdown-content h4, .markdown-content h5, .markdown-content h6 {
        margin-top: 1rem;
        margin-bottom: 0.5rem;
        font-weight: 600;
    }

    .markdown-content h1 { font-size: 1.5rem; }
    .markdown-content h2 { font-size: 1.3rem; }
    .markdown-content h3 { font-size: 1.1rem; }
    .markdown-content h4, .markdown-content h5, .markdown-content h6 { font-size: 1rem; }

    .markdown-content ul, .markdown-content ol {
        padding-left: 1.5rem;
        margin-bottom: 1rem;
    }

    .markdown-content li {
        margin-bottom: 0.25rem;
    }

    .markdown-content code {
        background-color: #f1f3f4;
        padding: 0.125rem 0.25rem;
        border-radius: 0.25rem;
        font-family: 'Courier New', monospace;
        font-size: 0.9em;
    }

    .markdown-content pre {
        background-color: #f8f9fa;
        border: 1px solid #e9ecef;
        border-radius: 0.375rem;
        padding: 1rem;
        overflow-x: auto;
        margin-bottom: 1rem;
    }

    .markdown-content pre code {
        background-color: transparent;
        padding: 0;
    }

    .markdown-content blockquote {
        border-left: 4px solid #dee2e6;
        padding-left: 1rem;
        margin-left: 0;
        margin-bottom: 1rem;
        color: #6c757d;
    }

    .markdown-content p {
        margin-bottom: 1rem;
    }

    .markdown-content strong {
        font-weight: 600;
    }

    .markdown-content em {
        font-style: italic;
    }

    /* Style for timestamp badge */
    .id-badge {
        background-color: #e9ecef;
        padding: 2px 6px;
        border-radius: 3px;
        font-size: 0.85em;
        color: #495057;
    }

    /* Comment bubble styling */
    .comment-bubble {
        display: inline-flex;
        align-items: center;
        background-color: #007bff;
        color: white;
        border-radius: 12px;
        padding: 2px 8px;
        font-size: 0.75em;
        margin-left: 8px;
        vertical-align: middle;
        min-width: 24px;
        justify-content: center;
    }

    .comment-bubble i {
        font-size: 0.8em;
        margin-right: 3px;
    }

    .comment-count {
        font-weight: bold;
        font-size: 0.9em;
    }

    /* Hover effect for comment bubble */
    .comment-bubble:hover {
        background-color: #0056b3;
        transform: scale(1.05);
        transition: all 0.2s ease;
    }
</style>

<div class="row">
    <div class="col-12">
        <h1>Hindamine</h1>
        <div class="table-responsive" style="background-color: transparent;">
            <table id="grading-table" class="table table-bordered" style="background-color: transparent; table-layout: fixed !important;">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Aeg</th>
                        <th>Vanus</th>
                        <th>Õpilane</th>
                        <th>Ülesanne / Aine</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($this->submissions as $index => $submission): ?>
                        <tr class="subject-row-<?= $this->subjectColors[$submission['subjectId']] ?> grading-row"
                            data-assignment-id="<?= $submission['assignmentId'] ?>"
                            data-user-id="<?= $submission['userId'] ?>"
                            data-assignment-name="<?= htmlspecialchars($submission['Ülesanne']) ?>"
                            data-student-name="<?= htmlspecialchars($submission['Õpilane']) ?>"
                            data-solution-url="<?= htmlspecialchars($submission['solutionUrl'] ?? '') ?>"
                            data-assignment-instructions="<?= htmlspecialchars($submission['assignmentInstructions'] ?? '') ?>"
                            data-comments="<?= htmlspecialchars($submission['comments'] ?? '[]') ?>">
                            <td><?= $index + 1 ?></td>
                            <td><?= $submission['Aeg'] ? '<span class="id-badge"><strong>' . (new DateTime($submission['Aeg']))->format('d.m.y') . '</strong> ' . (new DateTime($submission['Aeg']))->format('H:i') . '</span>' : '' ?></td>
                            <td><?= $submission['Vanus'] ?></td>
                            <td><?= $submission['Õpilane'] ?></td>
                            <td>
                                <span class="subject-name"><?= $submission['Aine'] ?></span>
                                <a href="grading/assignments/<?= $submission['assignmentId'] ?>/students/<?= $submission['userId'] ?>" onclick="event.preventDefault(); openGradingModal(this.closest('tr')); return false;" class="assignment-name" style="text-decoration: none; color: inherit;"><?= $submission['Ülesanne'] ?></a>
                                <?php if ($submission['commentCount'] > 0): ?>
                                    <span class="comment-bubble">
                                        <i class="fas fa-comment"></i>
                                        <span class="comment-count"><?= $submission['commentCount'] ?></span>
                                    </span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Grading Modal -->
<div class="modal fade" id="gradingModal" tabindex="-1" aria-labelledby="gradingModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-fluid" style="max-width: 95%; width: 95%;">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="gradingModalLabel">Ülesanne</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <!-- Student Info and Solution URL - Compact Layout -->
                <div class="row mb-3">
                    <div class="col-md-2">
                        <h6>Õpilane</h6>
                        <p id="studentName" class="text-muted mb-0"></p>
                    </div>
                    <div class="col-md-10" id="solutionUrlSection">
                        <h6>Lahenduse URL</h6>
                        <div class="mt-2 d-none" id="solutionUrlDetails">

                            <div class="input-group">
                                <input type="text" class="form-control form-control-sm font-monospace" id="solutionUrlInput" readonly>
                                <button class="btn btn-outline-secondary btn-sm" type="button" id="copySolutionUrl" title="Kopeeri URL">
                                    <i class="fas fa-copy"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Assignment Instructions -->
                <div class="mb-3" id="instructionsSection">
                    <h6>Ülesande kirjeldus</h6>
                    <div id="assignmentInstructions" class="border rounded p-3 bg-light markdown-content">
                        <p class="text-muted">Kirjeldus puudub</p>
                    </div>
                </div>

                <!-- Comments Thread -->
                <div class="mb-3">
                    <h6>Vestlus</h6>
                    <div id="messagesContainer" class="message-container border-top pt-2">
                        <div class="text-center">
                            <span class="loading-spinner"></span>
                            <span class="ms-2">Laen sõnumeid...</span>
                        </div>
                    </div>
                </div>

                <!-- New Message Form -->
                <div class="mb-3">
                    <label for="newMessageContent" class="form-label">Lisa kommentaar</label>
                    <textarea class="form-control" id="newMessageContent" rows="3" placeholder="Kirjuta kommentaar õpilasele..."></textarea>
                    <div class="invalid-feedback" id="messageError"></div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Sulge</button>
                <button type="button" class="btn btn-primary" id="sendMessageBtn">
                    <span id="sendBtnText">Saada kommentaar</span>
                    <span id="sendBtnSpinner" class="loading-spinner d-none ms-2"></span>
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Initialize tooltips
        [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
            .map(el => new bootstrap.Tooltip(el));

        // Add click handlers to grading rows
        const gradingRows = document.querySelectorAll('.grading-row');
        gradingRows.forEach(row => {
            row.addEventListener('click', function() {
                openGradingModal(this);
            });
        });

        // Send message button handler
        document.getElementById('sendMessageBtn').addEventListener('click', function() {
            sendMessage();
        });

        // Copy solution URL button handler
        document.getElementById('copySolutionUrl').addEventListener('click', function() {
            copySolutionUrl();
        });
    });

    let currentAssignmentId = null;
    let currentUserId = null;

    function openGradingModal(row) {
        // Extract data from row
        const assignmentId = row.dataset.assignmentId;
        const userId = row.dataset.userId;
        const assignmentName = row.dataset.assignmentName;
        const studentName = row.dataset.studentName;
        const solutionUrl = row.dataset.solutionUrl;
        const assignmentInstructions = row.dataset.assignmentInstructions;
        const comments = row.dataset.comments;

        // Store current IDs
        currentAssignmentId = assignmentId;
        currentUserId = userId;

        // Update modal content
        document.getElementById('gradingModalLabel').textContent = assignmentName;
        document.getElementById('studentName').textContent = studentName;

        // Update solution URL section
        const solutionUrlContainer = document.getElementById('solutionUrlContainer');
        const solutionUrlDetails = document.getElementById('solutionUrlDetails');
        const solutionUrlInput = document.getElementById('solutionUrlInput');

        if (solutionUrl && solutionUrl.trim() !== '') {

            solutionUrlInput.value = solutionUrl;
            solutionUrlDetails.classList.remove('d-none');
        } else {

            solutionUrlDetails.classList.add('d-none');
        }

        // Update instructions section with Markdown rendering
        const instructionsDiv = document.getElementById('assignmentInstructions');
        if (assignmentInstructions && assignmentInstructions.trim() !== '') {
            instructionsDiv.innerHTML = parseMarkdown(assignmentInstructions);
        } else {
            instructionsDiv.innerHTML = '<p class="text-muted">Kirjeldus puudub</p>';
        }

        // Load messages
        loadMessages(assignmentId, userId);

        // Clear new message form
        document.getElementById('newMessageContent').value = '';
        document.getElementById('messageError').textContent = '';

        // Show modal
        const modal = new bootstrap.Modal(document.getElementById('gradingModal'));
        modal.show();
    }

    function loadMessages(assignmentId, studentId) {
        const messagesContainer = document.getElementById('messagesContainer');
        messagesContainer.innerHTML = `
            <div class="text-center">
                <span class="loading-spinner"></span>
                <span class="ms-2">Laen sõnumeid...</span>
            </div>
        `;

        // Make AJAX request to load messages
        fetch('<?= BASE_URL ?>grading/getMessages', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `assignmentId=${assignmentId}&studentId=${studentId}`
        })
        .then(response => {
            console.log('Response status:', response.status);
            return response.json();
        })
        .then(data => {
            console.log('Response data:', data);
            if (data.status === 200) {
                displayMessages(data.data);
            } else {
                messagesContainer.innerHTML = `<p class="text-danger">Viga sõnumite laadimisel: ${data.message || 'Tundmatu viga'}</p>`;
            }
        })
        .catch(error => {
            console.error('Error loading messages:', error);
            messagesContainer.innerHTML = `<p class="text-danger">Viga sõnumite laadimisel: ${error.message}</p>`;
        });
    }

    function displayMessages(messages) {
        const messagesContainer = document.getElementById('messagesContainer');

        if (messages.length === 0) {
            messagesContainer.innerHTML = '<p class="text-muted">Sõnumeid pole veel</p>';
            return;
        }

        let messagesHtml = '';
        messages.forEach(message => {
            if (!message.isNotification) {
                const messageDate = new Date(message.createdAt).toLocaleString('et-EE');
                messagesHtml += `
                    <div class="message-item">
                        <div class="d-flex justify-content-between">
                            <span class="message-author">${message.userName || 'Tundmatu'}</span>
                            <span class="message-time">${messageDate}</span>
                        </div>
                        <div class="message-content">${message.content.replace(/\n/g, '<br>')}</div>
                    </div>
                `;
            }
        });

        messagesContainer.innerHTML = messagesHtml || '<p class="text-muted">Sõnumeid pole veel</p>';

        // Scroll to bottom
        messagesContainer.scrollTop = messagesContainer.scrollHeight;
    }

    function sendMessage() {
        const content = document.getElementById('newMessageContent').value.trim();
        const messageError = document.getElementById('messageError');
        const sendBtn = document.getElementById('sendMessageBtn');
        const sendBtnText = document.getElementById('sendBtnText');
        const sendBtnSpinner = document.getElementById('sendBtnSpinner');

        // Clear previous errors
        messageError.textContent = '';
        document.getElementById('newMessageContent').classList.remove('is-invalid');

        // Validate content
        if (!content) {
            messageError.textContent = 'Palun sisesta kommentaar';
            document.getElementById('newMessageContent').classList.add('is-invalid');
            return;
        }

        // Show loading state
        sendBtn.disabled = true;
        sendBtnText.textContent = 'Saadan...';
        sendBtnSpinner.classList.remove('d-none');

        // Send message
        fetch('<?= BASE_URL ?>grading/saveMessage', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `assignmentId=${currentAssignmentId}&content=${encodeURIComponent(content)}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 200) {
                // Clear form and reload messages
                document.getElementById('newMessageContent').value = '';
                loadMessages(currentAssignmentId, currentUserId);
            } else {
                messageError.textContent = 'Viga sõnumi saatmisel';
                document.getElementById('newMessageContent').classList.add('is-invalid');
            }
        })
        .catch(error => {
            console.error('Error sending message:', error);
            messageError.textContent = 'Viga sõnumi saatmisel';
            document.getElementById('newMessageContent').classList.add('is-invalid');
        })
        .finally(() => {
            // Reset button state
            sendBtn.disabled = false;
            sendBtnText.textContent = 'Saada kommentaar';
            sendBtnSpinner.classList.add('d-none');
        });
    }

    function copySolutionUrl() {
        const solutionUrlInput = document.getElementById('solutionUrlInput');
        solutionUrlInput.select();
        solutionUrlInput.setSelectionRange(0, 99999); // For mobile devices

        try {
            document.execCommand('copy');
            // Show temporary feedback
            const copyBtn = document.getElementById('copySolutionUrl');
            const originalHtml = copyBtn.innerHTML;
            copyBtn.innerHTML = '<i class="fas fa-check text-success"></i>';
            setTimeout(() => {
                copyBtn.innerHTML = originalHtml;
            }, 2000);
        } catch (err) {
            console.error('Failed to copy URL:', err);
        }
    }

    function parseMarkdown(text) {
        if (!text) return '';

        // Simple Markdown parser for basic formatting
        let html = text;

        // Headers
        html = html.replace(/^#### (.*$)/gim, '<h4>$1</h4>');
        html = html.replace(/^### (.*$)/gim, '<h3>$1</h3>');
        html = html.replace(/^## (.*$)/gim, '<h2>$1</h2>');
        html = html.replace(/^# (.*$)/gim, '<h1>$1</h1>');

        // Bold
        html = html.replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>');
        html = html.replace(/__(.*?)__/g, '<strong>$1</strong>');

        // Italic
        html = html.replace(/\*(.*?)\*/g, '<em>$1</em>');
        html = html.replace(/_(.*?)_/g, '<em>$1</em>');

        // Code blocks
        html = html.replace(/```([\s\S]*?)```/g, '<pre><code>$1</code></pre>');

        // Inline code
        html = html.replace(/`(.*?)`/g, '<code>$1</code>');

        // Links
        html = html.replace(/\[([^\]]+)\]\(([^)]+)\)/g, '<a href="$2" target="_blank">$1</a>');

        // Unordered lists
        html = html.replace(/^\* (.+)$/gm, '<li>$1</li>');
        html = html.replace(/(<li>.*<\/li>)/s, '<ul>$1</ul>');

        // Ordered lists
        html = html.replace(/^\d+\. (.+)$/gm, '<li>$1</li>');
        html = html.replace(/(<li>.*<\/li>)/s, function(match) {
            if (match.includes('<ul>')) return match;
            return '<ol>' + match + '</ol>';
        });

        // Blockquotes
        html = html.replace(/^> (.+)$/gm, '<blockquote>$1</blockquote>');

        // Line breaks and paragraphs
        html = html.replace(/\n\n/g, '</p><p>');
        html = html.replace(/\n/g, '<br>');

        // Wrap in paragraphs if not already wrapped
        if (!html.startsWith('<')) {
            html = '<p>' + html + '</p>';
        }

        return html;
    }
</script>
