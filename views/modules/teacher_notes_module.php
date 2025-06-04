<script>
// Teacher Notes functionality (moved from grading_index.php)
function loadTeacherNotes(assignmentId, studentId) {
    const notesContent = document.getElementById('teacherNotesContent');
    const notesStatus = document.getElementById('teacherNotesStatus');
    
    // Show loading state
    notesStatus.textContent = 'Laen märkmeid...';
    notesContent.disabled = true;

    // Make AJAX request to load teacher notes
    fetch('<?= BASE_URL ?>api/teachernotes', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `assignmentId=${assignmentId}&studentId=${studentId}`
    })
        .then(response => response.json())
        .then(data => {
            if (data.status === 200) {
                notesContent.value = data.data.notes || '';
                
                // Update status with last updated info
                if (data.data.updatedAt) {
                    const updatedDate = new Date(data.data.updatedAt);
                    notesStatus.textContent = `Viimati muudetud: ${updatedDate.toLocaleString('et-EE')}`;
                } else {
                    notesStatus.textContent = 'Märkmeid pole veel lisatud';
                }
            } else {
                notesStatus.textContent = 'Viga märkmete laadimisel';
                console.error('Error loading teacher notes:', data);
            }
        })
        .catch(error => {
            notesStatus.textContent = 'Viga märkmete laadimisel';
            console.error('Error loading teacher notes:', error);
        })
        .finally(() => {
            notesContent.disabled = false;
        });
}

function saveTeacherNotes() {
    const notesContent = document.getElementById('teacherNotesContent');
    const saveNotesBtn = document.getElementById('saveNotesBtn');
    const saveNotesBtnText = document.getElementById('saveNotesBtnText');
    const saveNotesBtnSpinner = document.getElementById('saveNotesBtnSpinner');
    const notesStatus = document.getElementById('teacherNotesStatus');

    // Show loading state
    saveNotesBtn.disabled = true;
    saveNotesBtnText.textContent = 'Salvestab...';
    saveNotesBtnSpinner.classList.remove('d-none');

    // Make AJAX request to save teacher notes
    fetch('<?= BASE_URL ?>api/teachernotes/save', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `assignmentId=${currentAssignmentId}&studentId=${currentUserId}&noteContent=${encodeURIComponent(notesContent.value)}`
    })
        .then(response => response.json())
        .then(data => {
            if (data.status === 200) {
                notesStatus.textContent = `Salvestatud: ${new Date().toLocaleString('et-EE')}`;
                notesStatus.classList.remove('text-danger');
                notesStatus.classList.add('text-success');
                
                // Reset status color after 3 seconds
                setTimeout(() => {
                    notesStatus.classList.remove('text-success');
                }, 3000);
            } else {
                notesStatus.textContent = 'Viga märkmete salvestamisel';
                notesStatus.classList.remove('text-success');
                notesStatus.classList.add('text-danger');
                console.error('Error saving teacher notes:', data);
            }
        })
        .catch(error => {
            notesStatus.textContent = 'Viga märkmete salvestamisel';
            notesStatus.classList.remove('text-success');
            notesStatus.classList.add('text-danger');
            console.error('Error saving teacher notes:', error);
        })
        .finally(() => {
            // Reset button state
            saveNotesBtn.disabled = false;
            saveNotesBtnText.textContent = 'Salvesta märkmed';
            saveNotesBtnSpinner.classList.add('d-none');
        });
}

// Initialize teacher notes functionality
function initializeTeacherNotes() {
    // Teacher notes save button handler
    const saveNotesBtn = document.getElementById('saveNotesBtn');
    if (saveNotesBtn) {
        saveNotesBtn.addEventListener('click', function () {
            saveTeacherNotes();
        });
    }
}

// Auto-initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    initializeTeacherNotes();
});
</script>

<style>
/* Teacher notes styling */
#teacherNotesContent {
    background-color: #fff9e6;
    border: 1px solid #ffd700;
    font-size: 0.9em;
}

#teacherNotesContent:focus {
    background-color: #ffffff;
    border-color: #007bff;
    box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
}

#teacherNotesStatus {
    font-size: 0.8em;
}

#saveNotesBtn {
    font-size: 0.85em;
    padding: 0.25rem 0.75rem;
}
</style>

<?php if ($this->auth->userIsAdmin || $this->auth->userIsTeacher): ?>
<!-- Teacher Private Notes Section -->
<div class="mb-3" id="teacherNotesSection">
    <h6>Privaatsed märkmed <small class="text-muted">(ainult õpetajale nähtav)</small></h6>
    <textarea class="form-control" id="teacherNotesContent" rows="3"
              placeholder="Lisa privaatseid märkmeid selle töö kohta..."></textarea>
    <div class="d-flex justify-content-between align-items-center mt-2">
        <small class="text-muted" id="teacherNotesStatus"></small>
        <button type="button" class="btn btn-sm btn-outline-secondary" id="saveNotesBtn">
            <span id="saveNotesBtnText">Salvesta märkmed</span>
            <span id="saveNotesBtnSpinner" class="loading-spinner d-none ms-1"></span>
        </button>
    </div>
</div>
<?php endif; ?>
