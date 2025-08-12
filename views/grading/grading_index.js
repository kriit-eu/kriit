/**
 * Grading page specific JavaScript
 * This file contains only code that is unique to the /grading page
 */

document.addEventListener('DOMContentLoaded', function () {
    // Initialize tooltips
    [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
        .map(el => new bootstrap.Tooltip(el));

    // Note: Row click handlers are handled by inline onclick in the HTML template

    // Initialize table sorting
    initializeTableSorting();

    // Initialize grading status styling for existing graded rows
    initializeGradingStatusStyling();

    // Handle the "Show graded" toggle
    const showGradedToggle = document.getElementById('showGradedToggle');
    if (showGradedToggle) {
        showGradedToggle.addEventListener('change', function () {
            const url = new URL(window.location.href);
            url.searchParams.set('showGraded', this.checked ? '1' : '0');
            window.location.href = url.toString();
        });
    }
});

// Note: Global variables are defined in grading_modal.js

// Note: Modal opening is handled by inline onclick in the HTML template

// Note: openGradingModal is defined globally in grading_modal.js

// Table sorting functionality - unique to grading page
let currentSort = {column: null, direction: 'asc'};

function initializeTableSorting() {
    const sortableHeaders = document.querySelectorAll('.sortable-header');

    sortableHeaders.forEach(header => {
        header.addEventListener('click', function () {
            const sortType = this.dataset.sort;
            handleSort(sortType, this);
        });
    });
}

function handleSort(sortType, headerElement) {
    const table = document.getElementById('grading-table');
    const tbody = table.querySelector('tbody');
    const rows = Array.from(tbody.querySelectorAll('tr'));

    // Determine sort direction
    let direction = 'asc';
    if (currentSort.column === sortType && currentSort.direction === 'asc') {
        direction = 'desc';
    }

    // Update current sort state
    currentSort = {column: sortType, direction: direction};

    // Clear all sort indicators
    document.querySelectorAll('.sort-indicator').forEach(indicator => {
        indicator.classList.remove('active');
        indicator.textContent = '⇅';
    });

    // Update active sort indicator to show current sort direction
    const indicator = headerElement.querySelector('.sort-indicator');
    indicator.classList.add('active');
    indicator.textContent = direction === 'asc' ? '↑' : '↓';

    // Sort the rows
    rows.sort((a, b) => compareRows(a, b, sortType, direction));

    // Remove all rows from tbody
    rows.forEach(row => row.remove());

    // Add sorted rows back
    rows.forEach(row => tbody.appendChild(row));

    // Update position numbers
    updatePositionNumbers(rows, direction);

    // Reinitialize tooltips for the sorted rows
    reinitializeTooltips();
}

function compareRows(rowA, rowB, sortType, direction) {
    let valueA, valueB;

    switch (sortType) {
        case 'student':
            valueA = rowA.dataset.studentName?.toLowerCase() || '';
            valueB = rowB.dataset.studentName?.toLowerCase() || '';
            break;
        case 'assignment':
            valueA = rowA.dataset.assignmentName?.toLowerCase() || '';
            valueB = rowB.dataset.assignmentName?.toLowerCase() || '';
            break;
        case 'submitted':
            valueA = parseInt(rowA.dataset.sortSubmitted || 0);
            valueB = parseInt(rowB.dataset.sortSubmitted || 0);
            break;
        case 'age':
            valueA = parseInt(rowA.dataset.sortAge || 0);
            valueB = parseInt(rowB.dataset.sortAge || 0);
            break;
        case 'graded':
            valueA = parseInt(rowA.dataset.sortGraded || 0);
            valueB = parseInt(rowB.dataset.sortGraded || 0);
            break;
        case 'difference':
            valueA = parseInt(rowA.dataset.sortDifference || 0);
            valueB = parseInt(rowB.dataset.sortDifference || 0);
            break;
        case 'grade':
            valueA = getGradeValue(rowA.dataset.currentGrade || '');
            valueB = getGradeValue(rowB.dataset.currentGrade || '');
            break;
        default:
            return 0;
    }

    let comparison = 0;
    if (typeof valueA === 'string') {
        comparison = valueA.localeCompare(valueB, 'et', { numeric: true, sensitivity: 'base' });
    } else {
        comparison = valueA - valueB;
    }

    return direction === 'desc' ? -comparison : comparison;
}

function getGradeValue(grade) {
    if (!grade || grade.trim() === '') return 0;
    
    const gradeStr = grade.toString().trim();
    switch (gradeStr) {
        case 'MA': return 1;
        case '2': return 2;
        case '3': return 3;
        case '4': return 4;
        case '5': return 5;
        case 'A': return 6;
        default: return 0;
    }
}

function updatePositionNumbers(rows, direction) {
    rows.forEach((row, index) => {
        const positionCell = row.querySelector('td:first-child');
        if (positionCell) {
            positionCell.textContent = (index + 1).toString();
        }
    });
}

function reinitializeTooltips() {
    // Dispose of existing tooltips
    document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(element => {
        const existingTooltip = bootstrap.Tooltip.getInstance(element);
        if (existingTooltip) {
            existingTooltip.dispose();
        }
    });

    // Initialize new tooltips
    [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
        .map(el => new bootstrap.Tooltip(el));
}

function initializeGradingStatusStyling() {
    const rows = document.querySelectorAll('.grading-row');
    rows.forEach(row => {
        const currentGrade = row.dataset.currentGrade;
        if (currentGrade && currentGrade.trim() !== '') {
            // Row is graded
            row.classList.remove('ungraded');
            row.classList.add('graded');
        } else {
            // Row is ungraded
            row.classList.remove('graded');
            row.classList.add('ungraded');
        }
    });
}