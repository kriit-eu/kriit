/**
 * Shared grading modal functionality
 * Used by grading page and subjects page
 */

// Global variables
let currentAssignmentId = null;
let currentUserId = null;
let gradingModalInstance = null;
let currentImageId = null; // Track uploaded image ID

/**
 * Open the grading modal with assignment and student data
 * @param {HTMLElement|Object} row - Table row element or data object
 */
function openGradingModal(row) {
    // Extract minimal data needed to start
    let assignmentId, userId, assignmentName, studentName;

    if (row.dataset) {
        // Row is an HTML element
        assignmentId = row.dataset.assignmentId;
        userId = row.dataset.userId || row.dataset.studentId;
        assignmentName = row.dataset.assignmentName;
        studentName = row.dataset.studentName;
    } else {
        // Row is a data object
        assignmentId = row.assignmentId;
        userId = row.userId;
        assignmentName = row.assignmentName;
        studentName = row.studentName;
    }

    if (!assignmentId || !userId) {
        console.error('Missing assignment or student data');
        return;
    }

    // Store current IDs
    currentAssignmentId = assignmentId;
    currentUserId = userId;

    // Update modal title
    const modalTitle = document.getElementById('gradingModalLabel');
    if (modalTitle) {
        modalTitle.innerHTML = `<span class="badge bg-secondary me-2">${assignmentId}</span>${studentName} | ${assignmentName}`;
    }

    // Show loading state for all sections
    const instructionsDiv = document.getElementById('assignmentInstructions');
    const solutionUrlDetails = document.getElementById('solutionUrlDetails');
    const criteriaContainer = document.getElementById('criteriaContainer');
    
    if (instructionsDiv) {
        instructionsDiv.innerHTML = '<p class="text-muted">Laen andmeid...</p>';
    }
    if (solutionUrlDetails) {
        solutionUrlDetails.classList.add('d-none');
    }
    if (criteriaContainer) {
        criteriaContainer.innerHTML = '<p class="text-muted">Laen kriteeriumeid...</p>';
    }
    
    // Clear and reset form elements
    resetModalForm();

    // Show modal first to ensure DOM elements are available
    showModal();
    
    // Wait for modal to be fully shown before loading data
    const modalElement = document.getElementById('gradingModal');
    if (modalElement) {
        modalElement.addEventListener('shown.bs.modal', function onModalShown() {
            // Initialize preview functionality when modal is shown
            initializePreview();
            
            // Ensure all DOM elements are fully rendered before loading data
            requestAnimationFrame(() => {
                setTimeout(() => {
                    loadAssignmentDataAjax(assignmentId, userId);
                }, 50);
            });
            
            // Remove the event listener after use
            modalElement.removeEventListener('shown.bs.modal', onModalShown);
        });
    } else {
        // Fallback if modal element not found
        setTimeout(() => {
            initializePreview();
            setTimeout(() => {
                loadAssignmentDataAjax(assignmentId, userId);
            }, 100);
        }, 300);
    }
}

/**
 * Reset modal form elements
 */
function resetModalForm() {
    // Clear any previously selected grades to force manual selection
    const gradeButtons = document.querySelectorAll('input[name="grade"]');
    gradeButtons.forEach(button => {
        button.checked = false;
    });

    // Ensure save button starts disabled to enforce manual grade selection
    const saveBtn = document.getElementById('saveBtn');
    const saveBtnText = document.getElementById('saveBtnText');
    const saveBtnSpinner = document.getElementById('saveBtnSpinner');

    saveBtn.disabled = true;
    saveBtnText.textContent = 'Salvesta';
    saveBtnSpinner.classList.add('d-none');

    // Clear new message form and preview
    const textarea = document.getElementById('newMessageContent');
    const preview = document.getElementById('messagePreview');
    
    if (textarea) {
        textarea.value = '';
    }
    document.getElementById('messageError').textContent = '';
    
    // Force clear and reset preview content
    if (preview) {
        preview.innerHTML = `
            <div class="text-muted text-center p-3">
                <i class="fas fa-eye-slash"></i><br>
                Eelvaade ilmub siia...
            </div>
        `;
        // Reset preview styling completely
        preview.style.height = '200px';
        preview.style.overflowY = 'hidden';
        preview.style.minHeight = '200px';
    }
    
    // Call global updatePreview if it exists
    if (typeof window.updateGradingPreview === 'function') {
        window.updateGradingPreview();
    }
    
    // Also trigger initial preview update if elements exist
    if (textarea) {
        textarea.dispatchEvent(new Event('input'));
    }
    
    // Clear image tracking
    currentImageId = null;
    
    // Hide any image upload progress elements
    const uploadProgress = document.getElementById('imageUploadProgress');
    if (uploadProgress) {
        uploadProgress.classList.add('d-none');
    }
    
    const uploadResults = document.getElementById('uploadResults');
    if (uploadResults) {
        uploadResults.innerHTML = '';
    }

    // Clear grade error and hide it
    const gradeError = document.getElementById('gradeError');
    gradeError.textContent = '';
    gradeError.style.display = 'none';
}

/**
 * Show the modal
 */
function showModal() {
    // Get modal element
    const modalElement = document.getElementById('gradingModal');

    // Check if modal instance already exists
    if (gradingModalInstance) {
        // Dispose of existing instance to prevent conflicts
        gradingModalInstance.dispose();
    }

    // Create new modal instance
    gradingModalInstance = new bootstrap.Modal(modalElement);

    // Add event listener for proper cleanup when modal is hidden
    modalElement.addEventListener('hidden.bs.modal', function () {
        // Remove any lingering backdrops
        const backdrops = document.querySelectorAll('.modal-backdrop');
        backdrops.forEach(backdrop => backdrop.remove());

        // Restore body scroll
        document.body.classList.remove('modal-open');
        document.body.style.overflow = '';
        document.body.style.paddingRight = '';

        // Reset save button state to ensure clean state for next modal
        const saveBtn = document.getElementById('saveBtn');
        const saveBtnText = document.getElementById('saveBtnText');
        const saveBtnSpinner = document.getElementById('saveBtnSpinner');
        if (saveBtn && saveBtnText && saveBtnSpinner) {
            saveBtn.disabled = true;
            saveBtnText.textContent = 'Salvesta';
            saveBtnSpinner.classList.add('d-none');
        }
    }, {once: true});

    // Show modal
    gradingModalInstance.show();
}

/**
 * Load assignment data via AJAX
 */
function loadAssignmentDataAjax(assignmentId, studentId) {
    fetch(`${window.BASE_URL || '/'}assignments/ajax_getAssignmentDetails?assignmentId=${assignmentId}&studentId=${studentId}`)
        .then(response => response.json())
        .then(data => {
            if (data.status === 200) {
                const assignmentData = data.data;
                
                // Update instructions with retry mechanism
                let instructionsDiv = document.getElementById('assignmentInstructions');
                
                // If not found, try a few more times with delays
                if (!instructionsDiv) {
                    let retryCount = 0;
                    const retryInterval = setInterval(() => {
                        instructionsDiv = document.getElementById('assignmentInstructions');
                        retryCount++;
                        
                        if (instructionsDiv || retryCount >= 5) {
                            clearInterval(retryInterval);
                            if (instructionsDiv) {
                                updateInstructionsContent(instructionsDiv, assignmentData);
                            } else {
                                // Try to create the missing div structure
                                const previewDiv = document.getElementById('assignmentInstructionsPreview');
                                if (previewDiv) {
                                    // Clear any existing content in preview div (like "Eelvaade ilmub siia" placeholder)
                                    previewDiv.innerHTML = '';
                                    
                                    // Reset any incorrect styling that might have been applied
                                    previewDiv.style.height = '';
                                    previewDiv.style.minHeight = '';
                                    previewDiv.style.maxHeight = '60px';
                                    previewDiv.style.overflow = 'hidden';
                                    
                                    const instructionsDiv = document.createElement('div');
                                    instructionsDiv.id = 'assignmentInstructions';
                                    instructionsDiv.innerHTML = '<p class="text-muted">Kirjeldus puudub</p>';
                                    previewDiv.appendChild(instructionsDiv);
                                    
                                    // Now try to update it
                                    updateInstructionsContent(instructionsDiv, assignmentData);
                                }
                            }
                        }
                    }, 50);
                } else {
                    updateInstructionsContent(instructionsDiv, assignmentData);
                }
                
                function updateInstructionsContent(div, data) {
                    const instructions = data.assignmentInstructions || '';
                    div.innerHTML = instructions ? parseMarkdown(instructions) : '<p class="text-muted">Kirjeldus puudub</p>';
                    
                    // Handle instructions preview/expand functionality
                    updateInstructionsDisplay();
                }
                
                // Update solution URL
                const solutionUrlDetails = document.getElementById('solutionUrlDetails');
                const solutionUrlInput = document.getElementById('solutionUrlInput');
                const solutionUrl = assignmentData.solutionUrl || '';
                
                if (solutionUrl && solutionUrl.trim() !== '') {
                    solutionUrlInput.value = solutionUrl;
                    solutionUrlDetails.classList.remove('d-none');
                } else {
                    solutionUrlDetails.classList.add('d-none');
                }
                
                // Update criteria
                loadCriteria(JSON.stringify(assignmentData.criteria || []));
                
                // Update OpenAPI section
                const openApiButton = document.getElementById('openApiButton');
                if (openApiButton) {
                    openApiButton.style.display = assignmentData.assignmentInvolvesOpenApi ? 'inline-block' : 'none';
                }
                
                // Load messages after all basic data is loaded
                loadMessages(assignmentId, studentId);

                // Load teacher notes
                if (typeof loadTeacherNotes === 'function') {
                    loadTeacherNotes(assignmentId, studentId);
                }
            } else {
                console.error('Error loading assignment data - bad status:', data);
                const instructionsDiv = document.getElementById('assignmentInstructions');
                const criteriaContainer = document.getElementById('criteriaContainer');
                
                if (instructionsDiv) {
                    instructionsDiv.innerHTML = '<p class="text-danger">Viga andmete laadimisel</p>';
                } else {
                    console.error('Instructions div not found in error handler');
                }
                if (criteriaContainer) {
                    criteriaContainer.innerHTML = '<p class="text-danger">Viga kriteeriumide laadimisel</p>';
                }
            }
        })
        .catch(error => {
            console.error('Error loading assignment data:', error);
            const instructionsDiv = document.getElementById('assignmentInstructions');
            const criteriaContainer = document.getElementById('criteriaContainer');
            
            if (instructionsDiv) {
                instructionsDiv.innerHTML = '<p class="text-danger">Viga andmete laadimisel</p>';
            }
            if (criteriaContainer) {
                criteriaContainer.innerHTML = '<p class="text-danger">Viga kriteeriumide laadimisel</p>';
            }
        });
}


/**
 * Update instructions display with preview/expand functionality
 */
function updateInstructionsDisplay() {
    const instructionsDiv = document.getElementById('assignmentInstructions');
    const previewDiv = document.getElementById('assignmentInstructionsPreview');
    const showMoreBtn = document.getElementById('showMoreInstructions');
    
    if (!instructionsDiv || !previewDiv || !showMoreBtn) {
        return;
    }
    
    // Check if content exceeds preview height
    const contentHeight = instructionsDiv.scrollHeight;
    const previewHeight = 60; // Max height in pixels
    
    if (contentHeight > previewHeight) {
        // Content is long, show "Show more" button
        showMoreBtn.style.display = 'block';
        previewDiv.style.maxHeight = previewHeight + 'px';
        previewDiv.style.overflow = 'hidden';
        
        // Add fade effect at bottom
        previewDiv.style.backgroundImage = 'linear-gradient(to bottom, transparent 70%, white 100%)';
        previewDiv.style.backgroundPosition = 'bottom';
        previewDiv.style.backgroundRepeat = 'no-repeat';
        
        showMoreBtn.onclick = function() {
            if (previewDiv.style.maxHeight === previewHeight + 'px') {
                // Expand
                previewDiv.style.maxHeight = contentHeight + 'px'; // Set to actual content height instead of 'none'
                previewDiv.style.overflow = 'hidden'; // Keep hidden to maintain container bounds
                previewDiv.style.backgroundImage = 'none';
                showMoreBtn.innerHTML = '<i class="fas fa-chevron-up me-1"></i>Näita vähem...';
                
                // Ensure button is visible and accessible
                showMoreBtn.style.position = 'relative';
                showMoreBtn.style.zIndex = '10';
                showMoreBtn.style.marginTop = '8px';
            } else {
                // Collapse
                previewDiv.style.maxHeight = previewHeight + 'px';
                previewDiv.style.overflow = 'hidden';
                previewDiv.style.backgroundImage = 'linear-gradient(to bottom, transparent 70%, white 100%)';
                showMoreBtn.innerHTML = '<i class="fas fa-chevron-down me-1"></i>Näita rohkem...';
                
                // Reset button positioning
                showMoreBtn.style.position = '';
                showMoreBtn.style.zIndex = '';
                showMoreBtn.style.marginTop = '';
            }
        };
    } else {
        // Content is short, hide "Show more" button
        showMoreBtn.style.display = 'none';
        previewDiv.style.maxHeight = 'none';
        previewDiv.style.overflow = 'visible';
        previewDiv.style.backgroundImage = 'none';
    }
}

/**
 * Load and display criteria
 */
function loadCriteria(criteriaData) {
    const criteriaContainer = document.getElementById('criteriaContainer');

    try {
        const criteria = JSON.parse(criteriaData || '[]');

        if (criteria.length === 0) {
            criteriaContainer.innerHTML = '<p class="text-muted">Kriteeriume pole määratud</p>';
            return;
        }

        let criteriaHtml = '';
        criteria.forEach(criterion => {
            const checked = criterion.isCompleted ? 'checked' : '';
            criteriaHtml += `
                <div class="form-check">
                    <input type="checkbox" class="form-check-input" id="criterion${criterion.criterionId}"
                           data-criterion-id="${criterion.criterionId}" ${checked}>
                    <label class="form-check-label" for="criterion${criterion.criterionId}">
                        ${criterion.criterionName}
                    </label>
                </div>
            `;
        });

        criteriaContainer.innerHTML = criteriaHtml;

        // Add event listeners to criteria checkboxes
        const criteriaCheckboxes = criteriaContainer.querySelectorAll('input[type="checkbox"]');
        criteriaCheckboxes.forEach(checkbox => {
            checkbox.addEventListener('change', function () {
                updateSaveButtonState();
            });
        });

    } catch (error) {
        console.error('Error parsing criteria data:', error);
        criteriaContainer.innerHTML = '<p class="text-danger">Viga kriteeriumide laadimisel</p>';
    }
}

/**
 * Update save button state based on selected grade
 */
function updateSaveButtonState() {
    const saveBtn = document.getElementById('saveBtn');
    const selectedGrade = document.querySelector('input[name="grade"]:checked');

    // Enable save button if grade is selected
    saveBtn.disabled = !selectedGrade;
}

/**
 * Save grade and comment
 */
function saveGradeAndComment() {
    const selectedGrade = document.querySelector('input[name="grade"]:checked')?.value;
    const comment = document.getElementById('newMessageContent').value.trim();
    const gradeError = document.getElementById('gradeError');
    const saveBtn = document.getElementById('saveBtn');
    const saveBtnText = document.getElementById('saveBtnText');
    const saveBtnSpinner = document.getElementById('saveBtnSpinner');

    // Clear previous errors
    gradeError.textContent = '';

    // Validate grade selection
    if (!selectedGrade) {
        gradeError.textContent = 'Palun valige hinne';
        gradeError.style.display = 'block';
        return;
    }

    // Collect criteria data
    const criteriaData = {};
    const criteriaCheckboxes = document.querySelectorAll('#criteriaContainer input[type="checkbox"]');
    criteriaCheckboxes.forEach(checkbox => {
        const criterionId = checkbox.dataset.criterionId;
        criteriaData[criterionId] = checkbox.checked ? 'true' : 'false';
    });

    // Show loading state
    saveBtn.disabled = true;
    saveBtnText.textContent = 'Salvestab...';
    saveBtnSpinner.classList.remove('d-none');

    // Prepare form data
    const formData = new URLSearchParams();
    formData.append('assignmentId', currentAssignmentId);
    formData.append('studentId', currentUserId);
    formData.append('grade', selectedGrade);
    formData.append('comment', comment);
    
    // Add image ID if present
    if (currentImageId) {
        formData.append('imageId', currentImageId);
    }

    // Add criteria data
    Object.keys(criteriaData).forEach(criterionId => {
        formData.append(`criteria[${criterionId}]`, criteriaData[criterionId]);
    });

    // Send grade and criteria
    fetch(`${window.BASE_URL || '/'}grading/saveGrade`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: formData.toString()
    })
        .then(response => response.json())
        .then(data => {
            if (data.status === 200) {
                // Clear comment form and reload messages
                document.getElementById('newMessageContent').value = '';
                
                // Clear image tracking
                currentImageId = null;
                
                loadMessages(currentAssignmentId, currentUserId);

                // Update the table row to reflect the new grade and timestamps
                updateTableRowGrade(currentAssignmentId, currentUserId, selectedGrade);

                // Update the "Hinnatud" and "Vahe" columns with the new data
                if (data.data.gradedAt) {
                    updateTableRowTimestamps(currentAssignmentId, currentUserId, data.data.gradedAt, data.data.daysDifference);
                }

                // Add grade badge to table row
                addGradeBadgeToTableRow(currentAssignmentId, currentUserId, selectedGrade);

                // Reset button state before closing modal to ensure clean state for next modal
                saveBtn.disabled = false;
                saveBtnText.textContent = 'Salvesta';
                saveBtnSpinner.classList.add('d-none');

                // Auto-close modal after successful save
                try {
                    if (gradingModalInstance) {
                        gradingModalInstance.hide();
                    } else {
                        console.error('Modal instance not found for auto-close');
                    }
                } catch (error) {
                    console.error('Error auto-closing modal:', error);
                }

            } else {
                gradeError.textContent = data.message || 'Viga andmete salvestamisel';
                gradeError.style.display = 'block';
                gradeError.classList.add('d-block');

                // Re-enable button immediately on error
                saveBtn.disabled = false;
                saveBtnText.textContent = 'Salvesta';
                saveBtnSpinner.classList.add('d-none');
            }
        })
        .catch(error => {
            console.error('Error saving data:', error);
            gradeError.textContent = 'Viga andmete salvestamisel';
            gradeError.style.display = 'block';
            gradeError.classList.add('d-block');

            // Re-enable button immediately on error
            saveBtn.disabled = false;
            saveBtnText.textContent = 'Salvesta';
            saveBtnSpinner.classList.add('d-none');
        });
}

/**
 * Load messages for assignment and student
 */
function loadMessages(assignmentId, studentId) {
    const messagesContainer = document.getElementById('messagesContainer');
    messagesContainer.innerHTML = `
        <div class="text-center">
            <span class="loading-spinner"></span>
            <span class="ms-2">Laen sõnumeid...</span>
        </div>
    `;

    // Make AJAX request to load messages
    fetch(`${window.BASE_URL || '/'}grading/getMessages`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `assignmentId=${assignmentId}&studentId=${studentId}`
    })
        .then(response => {
            return response.json();
        })
        .then(data => {
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

/**
 * Display messages in the container
 */
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
            let imageHtml = '';
            if (message.imageId) {
                imageHtml = `<div class="mt-2">${displayMessageImage(message.imageId)}</div>`;
            }
            messagesHtml += `
                <div class="message-item">
                    <div class="d-flex justify-content-between">
                        <span class="message-author">${message.userName || 'Tundmatu'}</span>
                        <span class="message-time">${messageDate}</span>
                    </div>
                    <div class="message-content">${parseMarkdown(message.content)}</div>
                    ${imageHtml}
                </div>
            `;
        }
    });

    messagesContainer.innerHTML = messagesHtml || '<p class="text-muted">Sõnumeid pole veel</p>';

    // Scroll to bottom
    messagesContainer.scrollTop = messagesContainer.scrollHeight;
}

/**
 * Update table row grade
 */
function updateTableRowGrade(assignmentId, userId, grade) {
    // Find the table row and update its data attribute
    const rows = document.querySelectorAll('.grading-row, tr[data-assignment-id]');
    rows.forEach(row => {
        if (row.dataset.assignmentId === assignmentId && row.dataset.userId === userId) {
            row.dataset.currentGrade = grade;
        }
    });
}

/**
 * Update table row timestamps
 */
function updateTableRowTimestamps(assignmentId, userId, gradedAt, daysDifference) {
    try {
        // Find the specific table row
        const targetRow = document.querySelector(`tr[data-assignment-id="${assignmentId}"][data-user-id="${userId}"], .grading-row[data-assignment-id="${assignmentId}"][data-user-id="${userId}"]`);

        if (!targetRow) {
            console.warn('Table row not found for updating timestamps:', {assignmentId, userId});
            return;
        }

        // Update "Hinnatud" column (6th column)
        const gradedCell = targetRow.querySelector('td:nth-child(6)');
        if (gradedCell && gradedAt) {
            const gradedDate = new Date(gradedAt);
            const formattedDate = gradedDate.toLocaleDateString('et-EE', {
                day: '2-digit',
                month: '2-digit',
                year: '2-digit'
            });
            const formattedTime = gradedDate.toLocaleTimeString('et-EE', {
                hour: '2-digit',
                minute: '2-digit'
            });

            gradedCell.innerHTML = `<span class="id-badge"><strong>${formattedDate}</strong> ${formattedTime}</span>`;

            // Update tooltip
            const fullDate = gradedDate.toLocaleDateString('et-EE', {
                day: '2-digit',
                month: '2-digit',
                year: 'numeric'
            }) + ' ' + formattedTime;
            gradedCell.setAttribute('title', `Hinnatud: ${fullDate}`);

            // Update data attribute for sorting
            targetRow.dataset.sortGraded = Math.floor(gradedDate.getTime() / 1000);
        }

        // Update "Vahe" column (7th column)
        const differenceCell = targetRow.querySelector('td:nth-child(7)');
        if (differenceCell) {
            differenceCell.textContent = daysDifference !== null ? daysDifference : '';

            // Update tooltip
            if (daysDifference) {
                differenceCell.setAttribute('title', `Hindamine võttis ${daysDifference} päeva`);
            } else {
                differenceCell.setAttribute('title', 'Sama päeva jooksul hinnatud');
            }

            // Update data attribute for sorting
            targetRow.dataset.sortDifference = daysDifference || 0;
        }

        // Reinitialize tooltips for the updated cells
        if (gradedCell) {
            const existingTooltip = bootstrap.Tooltip.getInstance(gradedCell);
            if (existingTooltip) {
                existingTooltip.dispose();
            }
            new bootstrap.Tooltip(gradedCell);
        }

        if (differenceCell) {
            const existingTooltip = bootstrap.Tooltip.getInstance(differenceCell);
            if (existingTooltip) {
                existingTooltip.dispose();
            }
            new bootstrap.Tooltip(differenceCell);
        }

    } catch (error) {
        console.error('Error updating table row timestamps:', error);
    }
}

/**
 * Add grade badge to table row
 */
function addGradeBadgeToTableRow(assignmentId, userId, grade) {
    try {
        // For subjects page: Find the specific grade cell using data attributes
        const gradeCell = document.querySelector(`td[data-assignment-id="${assignmentId}"][data-student-id="${userId}"]`);
        
        if (gradeCell) {
            // Subjects page structure - update the specific cell
            
            // Update the data attribute
            gradeCell.setAttribute('data-grade', grade);
            
            // Clear any existing content and add new grade
            gradeCell.textContent = grade;
            
            // Update CSS classes based on grade - preserve important classes
            // Remove only grade-specific classes, keep other classes
            gradeCell.classList.remove('red-cell', 'yellow-cell', 'green-cell', 'red-cell-intensity');
            if (!gradeCell.classList.contains('text-center')) {
                gradeCell.classList.add('text-center');
            }
            
            // Clear any inline styles that might override class styles (from setRedCellIntensity function)
            gradeCell.style.backgroundColor = '';
            gradeCell.style.color = '';
            
            // Apply grade-specific styling (same logic as PHP backend)
            const gradeNum = parseInt(grade);
            if (grade === 'MA' || gradeNum < 3) {
                // MA and failing grades (1-2) get yellow background - student needs to act
                gradeCell.classList.add('yellow-cell');
            } else if (gradeNum >= 3) {
                // Passing grades (3+) get green background - all good
                gradeCell.classList.add('green-cell');
            }
            
            // Update tooltip
            const existingTooltip = bootstrap.Tooltip.getInstance(gradeCell);
            if (existingTooltip) {
                existingTooltip.dispose();
            }
            gradeCell.setAttribute('title', `Hinne: ${grade}`);
            new bootstrap.Tooltip(gradeCell);
            
            return;
        }
        
        // Fallback for grading page structure
        const targetRow = document.querySelector(`tr[data-assignment-id="${assignmentId}"][data-user-id="${userId}"], .grading-row[data-assignment-id="${assignmentId}"][data-user-id="${userId}"]`);

        if (!targetRow) {
            console.warn('Table row not found for adding grade badge:', {assignmentId, userId});
            return;
        }

        // Find the grade cell - look for cell with data-grade or data-student-id attribute
        const fallbackGradeCell = targetRow.querySelector('td[data-student-id], td[data-grade], td.grade-cell, td:last-child');
        if (!fallbackGradeCell) {
            console.warn('Grade cell not found in table row');
            return;
        }

        // Clear any existing content in the grade cell
        fallbackGradeCell.innerHTML = '';

        // Create grade badge with appropriate styling based on grade value
        const gradeBadge = document.createElement('span');
        gradeBadge.className = 'grade-badge badge';
        gradeBadge.textContent = grade;

        // Apply grade-specific styling
        switch (grade) {
            case '2':
                gradeBadge.classList.add('bg-danger');
                break;
            case '3':
                gradeBadge.classList.add('bg-warning', 'text-dark');
                break;
            case '4':
            case '5':
                gradeBadge.classList.add('bg-success');
                break;
            case 'A':
                gradeBadge.classList.add('bg-primary');
                break;
            case 'MA':
                gradeBadge.classList.add('bg-secondary');
                break;
            default:
                gradeBadge.classList.add('bg-info');
        }

        // Add the badge to the grade cell
        fallbackGradeCell.appendChild(gradeBadge);

        // Add tooltip to the grade badge
        fallbackGradeCell.setAttribute('data-bs-toggle', 'tooltip');
        fallbackGradeCell.setAttribute('data-bs-placement', 'top');
        fallbackGradeCell.setAttribute('title', `Hinne: ${grade}`);

        // Initialize tooltip for the new grade cell
        new bootstrap.Tooltip(fallbackGradeCell);

        // Add visual indication that this row has been graded
        targetRow.classList.remove('ungraded');
        targetRow.classList.add('graded');

    } catch (error) {
        console.error('Error adding grade badge to table row:', error);
    }
}

/**
 * Copy solution URL to clipboard
 */
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

/**
 * Enhanced Markdown parser for comprehensive formatting
 */
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

    // Images - handle before links to avoid conflicts
    html = html.replace(/!\[([^\]]*)\]\(([^)]+)\)/g, function(match, alt, src) {
        return '<img src="' + src + '" alt="' + alt + '" class="message-image img-fluid rounded" style="max-height: 300px; cursor: pointer;" onclick="window.open(this.src, \'_blank\')">';
    });

    // Links
    html = html.replace(/\[([^\]]+)\]\(([^)]+)\)/g, '<a href="$2" target="_blank">$1</a>');

    // Unordered lists
    html = html.replace(/^\* (.+)$/gm, '<li>$1</li>');
    html = html.replace(/(<li>.*<\/li>)/s, '<ul>$1</ul>');

    // Ordered lists
    html = html.replace(/^\d+\. (.+)$/gm, '<li>$1</li>');
    html = html.replace(/(<li>.*<\/li>)/s, function (match) {
        if (match.includes('<ul>')) return match;
        return '<ol>' + match + '</ol>';
    });

    // Blockquotes
    html = html.replace(/^> (.+)$/gm, '<blockquote>$1</blockquote>');

    // Horizontal rules
    html = html.replace(/^---+$/gm, '<hr>');

    // Tables - process before line breaks to handle multi-line table blocks
    html = parseMarkdownTables(html);

    // Clean up excessive whitespace and line breaks before processing
    html = html.replace(/\n{3,}/g, '\n\n'); // Limit to maximum 2 consecutive newlines

    // Process paragraphs and line breaks with better control
    html = cleanupLineBreaksAndParagraphs(html);

    return html;
}

function cleanupLineBreaksAndParagraphs(text) {
    // Split into paragraphs based on double newlines
    const paragraphs = text.split(/\n\s*\n/);
    const processedParagraphs = [];

    paragraphs.forEach(paragraph => {
        paragraph = paragraph.trim();
        if (paragraph === '') return; // Skip empty paragraphs

        // Check if paragraph already contains HTML tags (like tables, lists, etc.)
        if (paragraph.includes('<table') || paragraph.includes('<ul>') ||
            paragraph.includes('<ol>') || paragraph.includes('<blockquote>') ||
            paragraph.includes('<h1>') || paragraph.includes('<h2>') ||
            paragraph.includes('<h3>') || paragraph.includes('<h4>') ||
            paragraph.includes('<pre>') || paragraph.includes('<hr>')) {
            // Already formatted content - add as-is
            processedParagraphs.push(paragraph);
        } else {
            // Regular text paragraph - convert single newlines to <br> and wrap in <p>
            let processedParagraph = paragraph.replace(/\n/g, '<br>');

            // Limit consecutive <br> tags to maximum of 2
            processedParagraph = processedParagraph.replace(/(<br>\s*){3,}/g, '<br><br>');

            // Wrap in paragraph tags if it doesn't start with a tag
            if (!processedParagraph.startsWith('<')) {
                processedParagraph = '<p>' + processedParagraph + '</p>';
            }

            processedParagraphs.push(processedParagraph);
        }
    });

    // Join paragraphs with single newlines (no extra spacing)
    let result = processedParagraphs.join('\n');

    // Final cleanup: remove any empty paragraphs that might have been created
    result = result.replace(/<p>\s*<\/p>/g, '');

    // Clean up any remaining excessive line breaks
    result = result.replace(/(<br>\s*){3,}/g, '<br><br>');

    // Remove <br> tags between list items (they create unwanted spacing)
    result = result.replace(/<\/li>\s*<br>\s*<li>/g, '</li><li>');
    result = result.replace(/<\/li>\s*<br>\s*<\/ul>/g, '</li></ul>');
    result = result.replace(/<\/li>\s*<br>\s*<\/ol>/g, '</li></ol>');
    result = result.replace(/<ul>\s*<br>\s*<li>/g, '<ul><li>');
    result = result.replace(/<ol>\s*<br>\s*<li>/g, '<ol><li>');

    // Remove <br> tags inside code blocks (they break ASCII art and directory structures)
    result = result.replace(/<pre><code>([\s\S]*?)<\/code><\/pre>/g, function (match, codeContent) {
        // Remove all <br> tags and any extra whitespace from code content
        let cleanedContent = codeContent.replace(/<br\s*\/?>/g, '\n');
        // Also remove any <p> tags that might have been added
        cleanedContent = cleanedContent.replace(/<\/?p>/g, '');
        // Clean up any double newlines
        cleanedContent = cleanedContent.replace(/\n\s*\n/g, '\n');
        return `<pre><code>${cleanedContent}</code></pre>`;
    });

    // Remove any trailing/leading whitespace around HTML tags
    result = result.replace(/>\s+</g, '><');

    return result;
}

function parseMarkdownTables(text) {
    // Split text into lines for processing
    const lines = text.split('\n');
    const result = [];
    let i = 0;

    while (i < lines.length) {
        const line = lines[i].trim();

        // Check if this line looks like a table header (contains |)
        if (line.includes('|') && line.startsWith('|') && line.endsWith('|')) {
            // Look ahead to see if next line is a separator
            if (i + 1 < lines.length) {
                const nextLine = lines[i + 1].trim();
                if (nextLine.includes('|') && nextLine.includes('-')) {
                    // This is a table - parse it
                    const tableResult = parseTable(lines, i);
                    result.push(tableResult.html);
                    i = tableResult.nextIndex;
                    continue;
                }
            }
        }

        // Not a table, add the line as-is
        result.push(lines[i]);
        i++;
    }

    return result.join('\n');
}

function parseTable(lines, startIndex) {
    let i = startIndex;
    const tableLines = [];

    // Collect all table lines
    while (i < lines.length) {
        const line = lines[i].trim();
        if (line.includes('|') && (line.startsWith('|') || line.endsWith('|'))) {
            tableLines.push(line);
            i++;
        } else {
            break;
        }
    }

    if (tableLines.length < 2) {
        // Not a valid table
        return {html: lines[startIndex], nextIndex: startIndex + 1};
    }

    // Parse header row
    const headerRow = tableLines[0];
    const separatorRow = tableLines[1];
    const dataRows = tableLines.slice(2);

    // Extract header cells
    const headerCells = headerRow.split('|')
        .map(cell => cell.trim())
        .filter(cell => cell !== '');

    // Build table HTML with Bootstrap classes
    let tableHtml = '<table class="table table-bordered table-sm mt-2 mb-2">\n';

    // Add header
    tableHtml += '  <thead class="table-light">\n';
    tableHtml += '    <tr>\n';
    headerCells.forEach(cell => {
        tableHtml += `      <th>${cell}</th>\n`;
    });
    tableHtml += '    </tr>\n';
    tableHtml += '  </thead>\n';

    // Add body
    if (dataRows.length > 0) {
        tableHtml += '  <tbody>\n';
        dataRows.forEach(row => {
            const cells = row.split('|')
                .map(cell => cell.trim())
                .filter(cell => cell !== '');

            if (cells.length > 0) {
                tableHtml += '    <tr>\n';
                cells.forEach((cell, index) => {
                    // Pad with empty cells if needed
                    if (index < headerCells.length) {
                        tableHtml += `      <td>${cell}</td>\n`;
                    }
                });
                // Add empty cells if row has fewer cells than headers
                for (let j = cells.length; j < headerCells.length; j++) {
                    tableHtml += '      <td></td>\n';
                }
                tableHtml += '    </tr>\n';
            }
        });
        tableHtml += '  </tbody>\n';
    }

    tableHtml += '</table>';

    return {html: tableHtml, nextIndex: i};
}

/**
 * Display message image
 */
function displayMessageImage(imageId) {
    if (!imageId) return '';
    return `<img src="images/${imageId}" class="message-image" alt="Attached image">`;
}

/**
 * Initialize grading modal functionality
 */
function initGradingModal() {
    // Add event listeners for grade buttons
    document.querySelectorAll('input[name="grade"]').forEach(button => {
        button.addEventListener('change', updateSaveButtonState);
    });

    // Add event listener for save button
    const saveBtn = document.getElementById('saveBtn');
    if (saveBtn) {
        saveBtn.addEventListener('click', saveGradeAndComment);
    }

    // Add event listener for copy URL button
    const copyBtn = document.getElementById('copySolutionUrl');
    if (copyBtn) {
        copyBtn.addEventListener('click', copySolutionUrl);
    }

    // Initialize image pasting functionality
    initializeImagePasting();

    // Note: Preview functionality is initialized when modal is shown
}

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', initGradingModal);

// Image pasting functionality
function initializeImagePasting() {
    const textarea = document.getElementById('newMessageContent');
    const uploadProgress = document.getElementById('imageUploadProgress');
    const uploadProgressBar = document.getElementById('uploadProgressBar');
    const uploadStatusText = document.getElementById('uploadStatusText');
    const uploadResults = document.getElementById('uploadResults');
    const cancelUploadBtn = document.getElementById('cancelUpload');
    const selectImagesBtn = document.getElementById('selectImagesBtn');
    const imageFileInput = document.getElementById('imageFileInput');
    
    // Check if required elements exist (they might not be in all modal configurations)
    if (!textarea) {
        return;
    }
    
    let currentUploads = [];
    let uploadCounter = 0;

    // Supported file types
    const supportedTypes = [
        'image/jpeg', 'image/jpg', 'image/png', 'image/gif', 
        'image/webp', 'image/avif', 'image/bmp', 'image/tiff'
    ];

    // File selection button
    if (selectImagesBtn && imageFileInput) {
        selectImagesBtn.addEventListener('click', () => {
            imageFileInput.click();
        });

        // File input change handler
        imageFileInput.addEventListener('change', (e) => {
            const files = Array.from(e.target.files);
            if (files.length > 0) {
                handleMultipleFiles(files);
            }
            e.target.value = ''; // Reset input
        });
    }

    // Handle paste events
    textarea.addEventListener('paste', function(e) {
        const items = (e.clipboardData || e.originalEvent.clipboardData).items;
        const imageFiles = [];
        
        for (let item of items) {
            if (item.type.indexOf('image') !== -1) {
                imageFiles.push(item.getAsFile());
            }
        }
        
        if (imageFiles.length > 0) {
            e.preventDefault();
            handleMultipleFiles(imageFiles);
        }
    });

    // Enhanced drag and drop
    textarea.addEventListener('dragover', function(e) {
        e.preventDefault();
        e.stopPropagation();
        textarea.classList.add('image-paste-active');
    });

    textarea.addEventListener('dragenter', function(e) {
        e.preventDefault();
        e.stopPropagation();
        textarea.classList.add('image-paste-active');
    });

    textarea.addEventListener('dragleave', function(e) {
        e.preventDefault();
        e.stopPropagation();
        // Only remove class if really leaving the textarea
        if (!textarea.contains(e.relatedTarget)) {
            textarea.classList.remove('image-paste-active');
        }
    });

    textarea.addEventListener('drop', function(e) {
        e.preventDefault();
        e.stopPropagation();
        textarea.classList.remove('image-paste-active');
        
        const files = Array.from(e.dataTransfer.files).filter(file => 
            file.type.indexOf('image') !== -1
        );
        
        if (files.length > 0) {
            handleMultipleFiles(files);
        }
    });

    // Cancel upload functionality
    if (cancelUploadBtn) {
        cancelUploadBtn.addEventListener('click', () => {
            cancelAllUploads();
        });
    }

    function validateFile(file) {
        const errors = [];
        
        // Check file type
        if (!supportedTypes.includes(file.type)) {
            errors.push(`Toetamata failitüüp: ${file.type}`);
        }
        
        // Check file size (10MB limit)
        if (file.size > 10 * 1024 * 1024) {
            const sizeMB = (file.size / (1024 * 1024)).toFixed(1);
            errors.push(`Fail on liiga suur: ${sizeMB}MB (max 10MB)`);
        }
        
        return errors;
    }

    function formatFileSize(bytes) {
        if (bytes === 0) return '0 B';
        const k = 1024;
        const sizes = ['B', 'KB', 'MB', 'GB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return parseFloat((bytes / Math.pow(k, i)).toFixed(1)) + ' ' + sizes[i];
    }

    function handleMultipleFiles(files) {
        if (files.length === 0) return;
        
        // Only proceed if upload elements exist
        if (!uploadProgress || !uploadResults || !uploadProgressBar || !uploadStatusText) {
            console.warn('Upload UI elements not found - cannot handle file uploads');
            return;
        }
                   
        // Show upload progress container
        uploadProgress.classList.remove('d-none');
        uploadResults.innerHTML = '';
        uploadProgressBar.style.width = '0%';
        uploadStatusText.textContent = `Kontrollin ${files.length} faili...`;
        
        // Validate all files first
        const validFiles = [];
        const invalidFiles = [];
        
        files.forEach(file => {
            const errors = validateFile(file);
            if (errors.length === 0) {
                validFiles.push(file);
            } else {
                invalidFiles.push({file, errors});
            }
        });
        
        // Show validation results
        if (invalidFiles.length > 0) {
            invalidFiles.forEach(({file, errors}) => {
                const errorDiv = document.createElement('div');
                errorDiv.className = 'upload-item error';
                errorDiv.innerHTML = `
                    <div class="d-flex justify-content-between">
                        <span><i class="fas fa-times"></i> ${file.name}</span>
                        <span class="file-info">${formatFileSize(file.size)}</span>
                    </div>
                    <div class="text-danger small mt-1">${errors.join(', ')}</div>
                `;
                uploadResults.appendChild(errorDiv);
            });
        }
        
        if (validFiles.length === 0) {
            uploadStatusText.textContent = 'Ühtegi kehtivat pilti ei leitud';
            setTimeout(() => {
                uploadProgress.classList.add('d-none');
            }, 3000);
            return;
        }
        
        // Upload valid files
        uploadStatusText.textContent = `Laen üles ${validFiles.length} pilti...`;
        uploadFilesSequentially(validFiles);
    }

    async function uploadFilesSequentially(files) {
        const totalFiles = files.length;
        let completedFiles = 0;
        
        for (let i = 0; i < files.length; i++) {
            const file = files[i];
            const uploadId = ++uploadCounter;
            
            // Create upload item in results
            const uploadItem = document.createElement('div');
            uploadItem.className = 'upload-item';
            uploadItem.id = `upload-${uploadId}`;
            uploadItem.innerHTML = `
                <div class="d-flex justify-content-between align-items-center">
                    <span><i class="fas fa-spinner fa-spin"></i> ${file.name}</span>
                    <span class="file-info">${formatFileSize(file.size)}</span>
                </div>
                <div class="progress mt-2" style="height: 4px;">
                    <div class="progress-bar" id="progress-${uploadId}" style="width: 0%"></div>
                </div>
            `;
            if (uploadResults) {
                uploadResults.appendChild(uploadItem);
            }
            
            try {
                const result = await uploadSingleFile(file, uploadId);
                
                // Update item to success state
                uploadItem.className = 'upload-item success';
                uploadItem.innerHTML = `
                    <div class="d-flex justify-content-between align-items-center">
                        <span><i class="fas fa-check"></i> ${file.name}</span>
                        <span class="file-info">${formatFileSize(result.processedSize || file.size)}</span>
                    </div>
                    ${result.compressionSavings ? `<div class="text-success small mt-1">
                        <i class="fas fa-compress-arrows-alt"></i> Kompressioon: ${result.compressionSavings}% väiksem
                    </div>` : ''}
                `;
                
                // Insert markdown into textarea
                insertImageMarkdown(result.imageId, file.name);
                
            } catch (error) {
                console.error('Upload failed:', error);
                
                // Update item to error state
                uploadItem.className = 'upload-item error';
                uploadItem.innerHTML = `
                    <div class="d-flex justify-content-between">
                        <span><i class="fas fa-times"></i> ${file.name}</span>
                        <span class="file-info">${formatFileSize(file.size)}</span>
                    </div>
                    <div class="text-danger small mt-1">${error.message}</div>
                `;
            }
            
            completedFiles++;
            const overallProgress = (completedFiles / totalFiles) * 100;
            if (uploadProgressBar) {
                uploadProgressBar.style.width = overallProgress + '%';
            }
            if (uploadStatusText) {
                uploadStatusText.textContent = `${completedFiles}/${totalFiles} pilti valmis`;
            }
        }
        
        // Hide progress after completion
        setTimeout(() => {
            if (uploadProgress) {
                uploadProgress.classList.add('d-none');
            }
        }, 3000);
    }

    function uploadSingleFile(file, uploadId) {
        return new Promise((resolve, reject) => {
            const formData = new FormData();
            formData.append('image', file);
            
            const xhr = new XMLHttpRequest();
            
            // Track this upload for cancellation
            currentUploads.push(xhr);
            
            // Progress tracking
            xhr.upload.addEventListener('progress', (e) => {
                if (e.lengthComputable) {
                    const progress = (e.loaded / e.total) * 100;
                    const progressBar = document.getElementById(`progress-${uploadId}`);
                    if (progressBar) {
                        progressBar.style.width = progress + '%';
                    }
                }
            });
            
            xhr.onload = function() {
                // Remove from tracking
                currentUploads = currentUploads.filter(u => u !== xhr);
                
                if (xhr.status === 200) {
                    try {
                        const response = JSON.parse(xhr.responseText);
                        if (response.status === 200) {
                            // Calculate compression savings if applicable
                            const originalSize = file.size;
                            const processedSize = response.data.processedSize;
                            const savings = originalSize > processedSize ? 
                                Math.round((1 - processedSize / originalSize) * 100) : 0;
                            
                            resolve({
                                imageId: response.data.imageId,
                                processedSize: processedSize,
                                compressionSavings: savings > 5 ? savings : null // Only show if significant
                            });
                        } else {
                            reject(new Error(response.message || 'Upload failed'));
                        }
                    } catch (e) {
                        reject(new Error('Invalid server response'));
                    }
                } else {
                    reject(new Error(`Server error: ${xhr.status}`));
                }
            };
            
            xhr.onerror = function() {
                currentUploads = currentUploads.filter(u => u !== xhr);
                reject(new Error('Network error'));
            };
            
            xhr.onabort = function() {
                currentUploads = currentUploads.filter(u => u !== xhr);
                reject(new Error('Upload cancelled'));
            };
            
            xhr.open('POST', (window.BASE_URL || '/') + 'images/upload');
            xhr.send(formData);
        });
    }

    function insertImageMarkdown(imageId, fileName) {
        const imageMarkdown = `![${fileName}](images/${imageId})`;
        
        // Get current cursor position and insert markdown
        const cursorPos = textarea.selectionStart;
        const textBefore = textarea.value.substring(0, cursorPos);
        const textAfter = textarea.value.substring(cursorPos);
        
        // Add newlines if needed for proper formatting
        const needsNewlineBefore = textBefore.length > 0 && !textBefore.endsWith('\n');
        const needsNewlineAfter = textAfter.length > 0 && !textAfter.startsWith('\n');
        
        const finalMarkdown = 
            (needsNewlineBefore ? '\n' : '') + 
            imageMarkdown + 
            (needsNewlineAfter ? '\n' : '');
        
        textarea.value = textBefore + finalMarkdown + textAfter;
        
        // Move cursor after the inserted text
        const newCursorPos = cursorPos + finalMarkdown.length;
        textarea.selectionStart = textarea.selectionEnd = newCursorPos;
        textarea.focus();
        
        // Trigger preview update
        textarea.dispatchEvent(new Event('input'));
    }

    function cancelAllUploads() {
        currentUploads.forEach(xhr => {
            try {
                xhr.abort();
            } catch (e) {
                console.error('Error aborting upload:', e);
            }
        });
        currentUploads = [];
        
        if (uploadStatusText) {
            uploadStatusText.textContent = 'Üleslaadimine tühistatud';
        }
        if (uploadProgressBar) {
            uploadProgressBar.style.width = '0%';
        }
        
        setTimeout(() => {
            if (uploadProgress) {
                uploadProgress.classList.add('d-none');
            }
        }, 2000);
    }
}

// Auto-resize textarea to fit content (infinite expansion)
function autoResizeTextarea(textarea) {
    // Reset height to auto to get the correct scrollHeight
    textarea.style.height = 'auto';
    
    // Set minimum height
    const minHeight = 200;
    
    // Calculate new height based on scroll height (no maximum limit)
    let newHeight = Math.max(textarea.scrollHeight, minHeight);
    
    // Always use hidden overflow since we expand to fit content
    textarea.style.overflowY = 'hidden';
    
    // Apply the new height
    textarea.style.height = newHeight + 'px';
    
    return newHeight;
}

// Global debounce mechanism for all resize operations
let globalResizeTimeout = null;
let isGloballyResizing = false;

// Debounced resize function that coordinates all resize calls
function debouncedResize(preview, source = 'unknown') {
    
    // If we're already in a resize operation, ignore this call
    if (isGloballyResizing) {
        return;
    }
    
    // Clear any pending resize operation
    if (globalResizeTimeout) {
        clearTimeout(globalResizeTimeout);
    }
    
    globalResizeTimeout = setTimeout(() => {
        isGloballyResizing = true;
        actualResizePreview(preview);
        
        // Reset flag after operation completes
        setTimeout(() => {
            isGloballyResizing = false;
        }, 200);
        
        globalResizeTimeout = null;
    }, 150); // Global debounce delay
}

// The actual resize implementation (renamed from autoResizePreview)
function actualResizePreview(preview) {
    
    // Set minimum height only - no maximum limit
    const minHeight = 200;
    
    // Temporarily remove height constraint to measure content
    const originalHeight = preview.style.height;
    const originalOverflow = preview.style.overflowY;
    
    preview.style.height = 'auto';
    preview.style.overflowY = 'hidden';
    
    // Get the actual content height including images
    let contentHeight = preview.scrollHeight;
    
    // Apply minimum height constraint only
    let newHeight = Math.max(contentHeight, minHeight);
    
    // Always allow infinite expansion - no scrolling needed
    preview.style.overflowY = 'hidden';
    
    // Apply the new height
    preview.style.height = newHeight + 'px';
    
    return newHeight;
}

// Keep the old function name for compatibility but route through debounced version
function autoResizePreview(preview) {
    debouncedResize(preview, 'autoResizePreview');
}

// Sync heights between textarea and preview
function syncElementHeights(textarea, preview) {
    // Use global debouncing instead of local debouncing
    debouncedResize(preview, 'syncElementHeights');
}

// The actual sync logic without debouncing
function performElementHeightSync(textarea, preview) {
    // Skip if globally resizing to prevent interference
    if (isGloballyResizing) {
        return;
    }
    
    // Get heights for both elements
    const textareaHeight = autoResizeTextarea(textarea);
    
    // Wait for images to load before calculating preview height
    const images = preview.querySelectorAll('img');
    if (images.length > 0) {
        let loadedImages = 0;
        const totalImages = images.length;
        
        const checkAllImagesLoaded = () => {
            if (loadedImages === totalImages) {
                // All images loaded, now resize preview
                debouncedResize(preview, 'performElementHeightSync-imageLoad');
            }
        };
        
        // Check each image and set up loading handlers
        images.forEach((img, index) => {
            if (img.complete && img.naturalWidth > 0) {
                // Image is already loaded
                loadedImages++;
            } else {
                // Image is still loading, set up handlers
                const handleImageLoad = () => {
                    loadedImages++;
                    checkAllImagesLoaded();
                    // Remove event listeners to prevent multiple calls
                    img.removeEventListener('load', handleImageLoad);
                    img.removeEventListener('error', handleImageLoad);
                };
                
                img.addEventListener('load', handleImageLoad);
                img.addEventListener('error', handleImageLoad);
            }
        });
        
        // If all images were already loaded, resize immediately
        if (loadedImages === totalImages) {
            debouncedResize(preview, 'performElementHeightSync-allLoaded');
        }
        
        // Removed fallback timeout that was causing infinite loops
        
    } else {
        // No images, just resize preview normally
        debouncedResize(preview, 'performElementHeightSync-noImages');
    }
}


// Real-time preview functionality
function initializePreview() {
    const textarea = document.getElementById('newMessageContent');
    const preview = document.getElementById('messagePreview');
    
    // Check if required elements exist
    if (!textarea || !preview) {
        return;
    }
    
    // Initialize mutation observer for dynamic content changes
    initializePreviewObserver(textarea, preview);
    
    // Update preview on input
    function updatePreview() {
        const content = textarea.value.trim();
        
        if (content === '') {
            preview.innerHTML = `
                <div class="text-muted text-center p-3">
                    <i class="fas fa-eye-slash"></i><br>
                    Eelvaade ilmub siia...
                </div>
            `;
            // Reset to minimum height when empty
            preview.style.height = '200px';
            preview.style.overflowY = 'hidden';
        } else {
            preview.innerHTML = parseMarkdown(content);
        }
        
        // Auto-resize both elements after content update
        // Use a small delay to ensure DOM is fully updated
        setTimeout(() => {
            syncElementHeights(textarea, preview);
        }, 50);
    }
    
    // Make updatePreview globally available for manual reset
    window.updateGradingPreview = function() {
        const currentTextarea = document.getElementById('newMessageContent');
        const currentPreview = document.getElementById('messagePreview');
        if (currentTextarea && currentPreview) {
            const content = currentTextarea.value.trim();
            if (content === '') {
                currentPreview.innerHTML = `
                    <div class="text-muted text-center p-3">
                        <i class="fas fa-eye-slash"></i><br>
                        Eelvaade ilmub siia...
                    </div>
                `;
                currentPreview.style.height = '200px';
                currentPreview.style.overflowY = 'hidden';
            } else {
                currentPreview.innerHTML = parseMarkdown(content);
            }
        }
    };
    
    // Update on every keystroke
    textarea.addEventListener('input', function() {
        updatePreview();
    });
    
    // Update on paste (with delays to handle image pasting)
    let pasteUpdateTimeout = null;
    textarea.addEventListener('paste', function() {
        // Clear any pending paste updates
        if (pasteUpdateTimeout) {
            clearTimeout(pasteUpdateTimeout);
        }
        
        // First update immediately for text content
        setTimeout(updatePreview, 10);
        
        // Single delayed update for images (reduced from 3 separate calls)
        pasteUpdateTimeout = setTimeout(() => {
            updatePreview();
            pasteUpdateTimeout = null;
        }, 800); // Single 800ms delay instead of multiple calls
    });
    
    // Handle manual resize of textarea
    textarea.addEventListener('mouseup', function() {
        syncElementHeights(textarea, preview);
    });
    
    // Initial update
    updatePreview();
}

// Monitor preview content changes for dynamic resizing
function initializePreviewObserver(textarea, preview) {
    let resizeTimeout = null;
    let isResizing = false;
    
    // Create a mutation observer to watch for content changes
    const observer = new MutationObserver((mutations) => {
        // Skip if we're currently in a resize operation
        if (isResizing) {
            return;
        }
        
        let shouldResize = false;
        let hasNewImages = false;
        
        mutations.forEach((mutation) => {
            // Check if nodes were added/removed
            if (mutation.type === 'childList') {
                // Check if any new images were added
                mutation.addedNodes.forEach((node) => {
                    if (node.nodeType === Node.ELEMENT_NODE) {
                        if (node.tagName === 'IMG' || node.querySelector('img')) {
                            hasNewImages = true;
                        }
                    }
                });
                shouldResize = true;
            } else if (mutation.type === 'attributes') {
                // Only trigger on src changes, not style changes to prevent loops
                if (mutation.target.tagName === 'IMG' && mutation.attributeName === 'src') {
                    hasNewImages = true;
                    shouldResize = true;
                }
                // Ignore style changes to prevent infinite loops
            }
        });
        
        if (shouldResize) {
            // Clear any pending resize
            if (resizeTimeout) {
                clearTimeout(resizeTimeout);
            }
            
            // If new images were added, wait a bit longer for them to start loading
            const delay = hasNewImages ? 250 : 100;
            
            resizeTimeout = setTimeout(() => {
                isResizing = true;
                debouncedResize(preview, 'mutationObserver');
                // Reset flag after a short delay
                setTimeout(() => {
                    isResizing = false;
                }, 200); // Increased to match global debouncing
                resizeTimeout = null;
            }, delay);
        }
    });
    
    // Start observing the preview div - removed style from attributeFilter
    observer.observe(preview, {
        childList: true,
        subtree: true,
        attributes: true,
        attributeFilter: ['src'] // Only monitor src changes, not style changes
    });
    
    return observer;
}

// Export functions for use in other scripts
window.openGradingModal = openGradingModal;