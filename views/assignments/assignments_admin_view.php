<!-- Bootstrap Icons -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">

<style>

    .red-cell {
        background-color: rgb(255, 180, 176) !important;
    }

    .yellow-cell {
        background-color: #fff8b3 !important;
    }

    .modal-body {
        word-wrap: break-word;
        word-break: break-word;
    }

    #solutionUrl {
        display: inline-block;
        max-width: 100%;
        overflow-wrap: break-word;
        word-break: break-all;
    }

    .modal-dialog {
        max-width: 800px;
        width: 100%;
    }

    .modal-content {
        padding: 15px;
    }

    .text-center {
        text-align: center;
    }

    .context-menu {
        display: none;
        position: absolute;
        z-index: 1000;
        width: 250px;
        background-color: white;
        border: 1px solid #ccc;
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
        padding: 10px;
        overflow-x: auto;
    }

    .context-menu textarea {
        width: 100%;
        height: 60px;
        resize: none;
        border: 1px solid #ccc;
        padding: 5px;
        font-size: 12px;
        margin-bottom: 10px;
    }

    .context-menu button {
        background-color: #007bff;
        color: white;
        border: none;
        padding: 5px 10px;
        cursor: pointer;
        font-size: 12px;
        border-radius: 3px;
    }

    .context-menu button:hover {
        background-color: #0056b3;
    }

    .comments-section {
        margin-top: 1rem;
        padding: 1rem;
        background-color: #f8f9fa;
        border-radius: 0.375rem;
        border: 1px solid #dee2e6;
    }

    .comment-item {
        margin-bottom: 1rem;
        padding: 0.75rem;
        background-color: white;
        border-radius: 0.25rem;
        border-left: 4px solid #007bff;
    }

    .comment-header {
        font-weight: bold;
        color: #495057;
        margin-bottom: 0.5rem;
        font-size: 0.9rem;
    }

    .comment-content {
        color: #6c757d;
        line-height: 1.4;
    }

    .messages-section {
        margin-top: 2rem;
        padding: 1rem;
        background-color: #f8f9fa;
        border-radius: 0.375rem;
        border: 1px solid #dee2e6;
    }

    .message-item {
        margin-bottom: 1rem;
        padding: 0.75rem;
        background-color: white;
        border-radius: 0.25rem;
        border-left: 4px solid #28a745;
    }

    .message-header {
        font-weight: bold;
        color: #495057;
        margin-bottom: 0.5rem;
        font-size: 0.9rem;
    }

    .message-content {
        color: #6c757d;
        line-height: 1.4;
        white-space: pre-wrap;
    }

    .notification-message {
        border-left-color: #ffc107 !important;
        background-color: #fff9e6;
    }

    .message-form {
        margin-top: 1rem;
        padding: 1rem;
        background-color: white;
        border-radius: 0.25rem;
        border: 1px solid #dee2e6;
    }

    .message-form textarea {
        width: 100%;
        min-height: 80px;
        resize: vertical;
        border: 1px solid #ced4da;
        border-radius: 0.25rem;
        padding: 0.5rem;
        font-family: inherit;
    }

    .message-form button {
        margin-top: 0.5rem;
    }

    .image-container {
        margin: 10px 0;
        text-align: center;
    }

    .message-image {
        max-width: 100%;
        max-height: 400px;
        border-radius: 8px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        cursor: pointer;
        transition: transform 0.2s ease;
    }

    .message-image:hover {
        transform: scale(1.02);
    }

    .image-modal {
        display: none;
        position: fixed;
        z-index: 2000;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0,0,0,0.8);
        cursor: pointer;
    }

    .image-modal-content {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        max-width: 90%;
        max-height: 90%;
        border-radius: 8px;
    }

</style>

<h2>
    <?= htmlspecialchars($assignment['assignmentName']) ?>
    <a href="#" class="edit-assignment-btn" style="text-decoration: none; margin-left: 10px;" onclick="editAssignment()">
        <i class="bi bi-pencil-square" style="font-size: 0.8em; color: #007bff;"></i>
    </a>
</h2>

<?php if (!empty($assignment['assignmentInstructions'])): ?>
    <div class="alert alert-info">
        <?= nl2br(htmlspecialchars($assignment['assignmentInstructions'])) ?>
    </div>
<?php endif; ?>

<?php if (!empty($assignment['assignmentDueAt'])): ?>
    <p><strong>Tähtaeg:</strong> <?= date('d.m.Y H:i', strtotime($assignment['assignmentDueAt'])) ?></p>
<?php endif; ?>

<!-- Admin View: Show assignment overview and messages -->
<div class="row">
    <div class="col-md-8">
        <?php if (!empty($assignment['students'])): ?>
            <h4>Õpilased</h4>
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Õpilane</th>
                            <th>Staatus</th>
                            <th>Hinne</th>
                            <th>Kriteeriumid</th>
                            <th>Lahendus</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($assignment['students'] as $student): ?>
                            <tr class="<?= $student['class'] ?>">
                                <td><?= htmlspecialchars($student['studentName']) ?></td>
                                <td><?= htmlspecialchars($student['assignmentStatusName']) ?></td>
                                <td><?= htmlspecialchars($student['grade']) ?></td>
                                <td>
                                    <?= $student['userDoneCriteriaCount'] ?>/<?= count($assignment['criteria']) ?>
                                </td>
                                <td>
                                    <?php if (!empty($student['solutionUrl'])): ?>
                                        <a href="<?= htmlspecialchars($student['solutionUrl']) ?>" target="_blank" 
                                           class="btn btn-sm btn-outline-primary">
                                            <i class="bi bi-link-45deg"></i> Vaata
                                        </a>
                                    <?php else: ?>
                                        <span class="text-muted">Esitamata</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="alert alert-warning">Selle ülesande jaoks ei leitud õpilasi.</div>
        <?php endif; ?>
    </div>
    
    <div class="col-md-4">
        <h4>Kriteeriumid</h4>
        <?php if (!empty($assignment['criteria'])): ?>
            <ul class="list-group">
                <?php foreach ($assignment['criteria'] as $criterion): ?>
                    <li class="list-group-item">
                        <?= htmlspecialchars($criterion['criteriaName']) ?>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php else: ?>
            <div class="alert alert-info">Kriteeriumeid pole määratud.</div>
        <?php endif; ?>
    </div>
</div>

<!-- Messages Section (Always visible for admins) -->
<div class="messages-section">
    <h5>Vestlus</h5>
    
    <?php if (!empty($assignment['messages'])): ?>
        <div id="messages-list">
            <?php foreach ($assignment['messages'] as $message): ?>
                <div class="message-item <?= $message['isNotification'] ? 'notification-message' : '' ?>">
                    <div class="message-header">
                        <?= htmlspecialchars($message['userName']) ?> • <?= $message['createdAt'] ?>
                        <?php if ($message['isNotification']): ?>
                            <span class="badge bg-warning ms-2">Süsteemi teade</span>
                        <?php endif; ?>
                    </div>
                    <div class="message-content">
                        <?= displayMessageWithImages($message['content']) ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="alert alert-info">Sõnumeid pole veel lisatud.</div>
    <?php endif; ?>
    
    <!-- Message Form for Admins -->
    <div class="message-form">
        <form id="message-form">
            <div class="mb-3">
                <label for="message-content" class="form-label">Lisa sõnum:</label>
                <textarea id="message-content" name="content" class="form-control" 
                         placeholder="Kirjuta sõnum..." required></textarea>
            </div>
            <button type="submit" class="btn btn-primary">
                <i class="bi bi-send"></i> Saada sõnum
            </button>
        </form>
    </div>
</div>

<!-- Image Modal -->
<div id="image-modal" class="image-modal">
    <img id="modal-image" class="image-modal-content">
</div>

<!-- Edit Assignment Modal -->
<div class="modal fade" id="editAssignmentModal" tabindex="-1" aria-labelledby="editAssignmentModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editAssignmentModalLabel">Muuta ülesanne</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="editAssignmentForm">
                    <div class="mb-3">
                        <label for="assignmentName" class="form-label">Pealkiri</label>
                        <input type="text" class="form-control" id="assignmentName" name="assignmentName"
                               value="<?= htmlspecialchars($assignment['assignmentName']) ?>">
                    </div>
                    <div class="mb-3">
                        <label for="assignmentInstructions" class="form-label">Instruktsioon</label>
                        <textarea class="form-control" id="assignmentInstructions" name="assignmentInstructions"
                                  rows="3"><?= htmlspecialchars($assignment['assignmentInstructions']) ?></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="assignmentDueAt" class="form-label">Tähtaeg</label>
                        <input type="date" class="form-control" id="assignmentDueAt" name="assignmentDueAt"
                               value="<?= (!empty($assignment['assignmentDueAt']) && strtotime($assignment['assignmentDueAt']) > 0) ? date('Y-m-d', strtotime($assignment['assignmentDueAt'])) : "" ?>">
                    </div>

                    <div class="mb-3 form-check">
                        <input type="checkbox" class="form-check-input" id="assignmentInvolvesOpenApi" name="assignmentInvolvesOpenApi"
                               <?= isset($assignment['assignmentInvolvesOpenApi']) && $assignment['assignmentInvolvesOpenApi'] ? 'checked' : '' ?>>
                        <label class="form-check-label" for="assignmentInvolvesOpenApi">Ülesandel on OpenAPI</label>
                    </div>

                    <!-- Block for criteria management -->
                    <div class="mb-3">
                        <h5>Kriteeriumid</h5>
                        <div id="editCriteriaContainer">
                            <?php foreach ($assignment['criteria'] as $criterion): ?>
                                <div class="criteria-row mb-2 d-flex align-items-center">
                                    <div class="form-check flex-grow-1">
                                        <input class="form-check-input" type="checkbox"
                                               id="edit_criterion_<?= $criterion['criteriaId'] ?>" checked disabled>
                                        <label class="form-check-label"
                                               for="edit_criterion_<?= $criterion['criteriaId'] ?>">
                                            <?= htmlspecialchars($criterion['criteriaName'], ENT_QUOTES, 'UTF-8') ?>
                                        </label>
                                    </div>
                                    <button type="button" class="btn btn-danger btn-sm ms-2"
                                            onclick="removeOldCriterion(<?= $criterion['criteriaId'] ?>)">X</button>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <button type="button" class="btn btn-primary mt-2" id="addCriterionButton">Lisa kriteerium</button>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" onclick="saveEditedAssignment()">Salvesta</button>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tühista</button>
            </div>
        </div>
    </div>
</div>

<script>
// Image display function
function displayMessageImage(imageId) {
    return `<div class="image-container">
        <img src="images/${imageId}" alt="Message Image" class="message-image" onclick="openImageModal('images/${imageId}')">
    </div>`;
}

// Image modal functionality
function openImageModal(imageSrc) {
    document.getElementById('modal-image').src = imageSrc;
    document.getElementById('image-modal').style.display = 'block';
}

// Close modal when clicking outside the image
document.getElementById('image-modal').addEventListener('click', function() {
    this.style.display = 'none';
});

// Message form submission
document.getElementById('message-form').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const content = document.getElementById('message-content').value.trim();
    if (!content) return;
    
    // Send message via AJAX
    fetch('assignments/ajax_saveMessage', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: new URLSearchParams({
            assignmentId: <?= $assignment['assignmentId'] ?>,
            userId: <?= $this->auth->userId ?>,
            content: content,
            teacherId: <?= $assignment['teacherId'] ?? 'null' ?>
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Reload the page to show the new message
            window.location.reload();
        } else {
            alert('Viga sõnumi saatmisel: ' + (data.message || 'Tundmatu viga'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Viga sõnumi saatmisel');
    });
});
</script>

<?php
// Helper function to display messages with images
function displayMessageWithImages($content) {
    // Handle both markdown-style images and [image:ID] format
    // Pattern for markdown: ![alt](http://localhost:8080/images/ID)
    $markdownPattern = '/!\[([^\]]*)\]\(http:\/\/localhost:8080\/images\/(\d+)\)/';
    
    // Pattern for [image:ID] format
    $tagPattern = '/\[image:(\d+)\]/';
    
    // Split by both patterns
    $patterns = array($markdownPattern, $tagPattern);
    $allMatches = array();
    
    foreach ($patterns as $pattern) {
        if (preg_match_all($pattern, $content, $matches, PREG_OFFSET_CAPTURE)) {
            foreach ($matches[0] as $index => $match) {
                $allMatches[] = array(
                    'match' => $match[0],
                    'offset' => $match[1],
                    'imageId' => $matches[count($matches)-1][$index][0], // Last capture group is always image ID
                    'length' => strlen($match[0])
                );
            }
        }
    }
    
    // Sort matches by offset (position in string)
    usort($allMatches, function($a, $b) {
        return $a['offset'] - $b['offset'];
    });
    
    $result = '';
    $lastOffset = 0;
    
    foreach ($allMatches as $match) {
        // Add text before this image
        $textBefore = substr($content, $lastOffset, $match['offset'] - $lastOffset);
        $result .= nl2br(htmlspecialchars($textBefore, ENT_QUOTES, 'UTF-8'));
        
        // Add the image
        $imageId = $match['imageId'];
        $result .= '<div class="image-container">
            <img src="images/' . $imageId . '" alt="Message Image" class="message-image" onclick="openImageModal(\'images/' . $imageId . '\')">
        </div>';
        
        $lastOffset = $match['offset'] + $match['length'];
    }
    
    // Add any remaining text after the last image
    $textAfter = substr($content, $lastOffset);
    $result .= nl2br(htmlspecialchars($textAfter, ENT_QUOTES, 'UTF-8'));
    
    return $result;
}
?>
