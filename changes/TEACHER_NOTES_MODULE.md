# Teacher Notes Module - Modularization Documentation

## Overview

The teacher notes feature has been refactored from a monolithic implementation into a reusable modular component. This change improves code organization, maintainability, and reusability across the application.

## Changes Made

### 1. Created Modular Component

**File Created:** `/views/modules/teacher_notes_module.php`

This self-contained module includes:
- **JavaScript Functions**: `loadTeacherNotes()`, `saveTeacherNotes()`, `initializeTeacherNotes()`
- **CSS Styling**: All teacher notes specific styles
- **HTML Structure**: Complete UI for teacher notes section
- **PHP Logic**: Conditional rendering based on user permissions

### 2. Updated Main Grading View

**File Modified:** `/views/grading/grading_index.php`

**Removed:**
- Teacher notes CSS styles (lines 268-284)
- Teacher notes HTML structure (lines 815-825)
- Teacher notes JavaScript functions (`loadTeacherNotes`, `saveTeacherNotes`)
- Event listener setup for save button

**Added:**
- Module include: `<?php include 'modules/teacher_notes_module.php'; ?>`

### 3. Created Dedicated API Controller

**File Created:** `/controllers/api/teachernotes.php`

This dedicated API controller includes:
- **Class Structure**: Extends `Controller` and follows API naming conventions
- **Method Separation**: `index()` for retrieving notes, `save()` for saving notes
- **Error Handling**: Comprehensive try-catch blocks with detailed error logging
- **Authentication**: Proper teacher/admin permission checks
- **Input Validation**: Required parameter validation for assignmentId and studentId

**File Modified:** `/controllers/grading.php`

**Removed:**
- `getTeacherNotes()` method (moved to API controller)
- `saveTeacherNotes()` method (moved to API controller)

**File Modified:** `/views/modules/teacher_notes_module.php`

**Updated:**
- API endpoint URLs changed from `grading/getTeacherNotes` to `api/teachernotes`
- API endpoint URLs changed from `grading/saveTeacherNotes` to `api/teachernotes/save`

### 4. Benefits of Modularization

- **Code Organization**: Related functionality grouped together
- **Reusability**: Module can be included in other views if needed
- **Maintainability**: Changes only need to be made in one place
- **Testing**: Easier to test individual components
- **Separation of Concerns**: Clear boundaries between features
- **API Architecture**: Dedicated API controller follows REST conventions
- **Error Handling**: Comprehensive error logging and user feedback

## Module Structure

### JavaScript Functions

```javascript
loadTeacherNotes(assignmentId, studentId)
```
- Loads existing teacher notes for a specific assignment/student combination
- Shows loading state and handles errors
- Updates UI with note content and last modified timestamp

```javascript
saveTeacherNotes()
```
- Saves current note content to the database
- Provides visual feedback during save operation
- Handles success/error states with appropriate messaging

```javascript
initializeTeacherNotes()
```
- Sets up event listeners for the save button
- Auto-initializes when DOM is loaded

### CSS Classes

- `#teacherNotesContent`: Main textarea styling with yellow background
- `#teacherNotesStatus`: Status message styling
- `#saveNotesBtn`: Save button styling

### HTML Structure

The module provides a complete form section with:
- Textarea for note content
- Status display for last updated information
- Save button with loading spinner
- Proper labeling and accessibility

## How to Use the Module

### 1. Including in Views

To add teacher notes functionality to any view:

```php
<?php include 'modules/teacher_notes_module.php'; ?>
```

### 2. Required Variables

The module expects these variables to be available:
- `$this->auth->userIsAdmin` - Check if user is admin
- `$this->auth->userIsTeacher` - Check if user is teacher

### 3. Required Global JavaScript Variables

The module JavaScript functions expect these variables in the global scope:
- `currentAssignmentId` - The current assignment ID
- `currentUserId` - The current student/user ID
- `BASE_URL` - Application base URL for AJAX requests

### 4. Backend Dependencies

The module requires these API endpoints:
- `api/teachernotes` - Retrieve existing notes (GET/POST)
- `api/teachernotes/save` - Save note changes (POST)

## Integration Example

```php
<!-- In your view file -->
<div class="modal-body">
    <!-- Other content -->
    
    <!-- Include teacher notes module -->
    <?php include 'modules/teacher_notes_module.php'; ?>
    
    <!-- More content -->
</div>

<script>
// Set required global variables
var currentAssignmentId = <?= $assignment['id'] ?>;
var currentUserId = <?= $student['id'] ?>;

// Call loadTeacherNotes when modal opens
function openModal(assignmentId, userId) {
    currentAssignmentId = assignmentId;
    currentUserId = userId;
    
    // Load teacher notes
    loadTeacherNotes(assignmentId, userId);
}
</script>
```

## File Structure

```
views/
├── modules/
│   ├── teacher_notes_module.php    # New modular component
│   └── openapi_module.php          # Existing module example
├── grading/
│   └── grading_index.php           # Updated to use module
└── ...

controllers/
├── grading.php                     # Teacher notes methods removed
├── api/
│   ├── teachernotes.php            # New dedicated API controller
│   ├── groups.php                  # Existing API controller
│   └── users.php                   # Existing API controller
└── ...
```

## Testing

### 1. Syntax Validation

```bash
# Check module syntax
docker exec kriit-app-1 php -l /var/www/html/views/modules/teacher_notes_module.php

# Check main grading file syntax
docker exec kriit-app-1 php -l /var/www/html/views/grading/grading_index.php
```

### 2. Functional Testing

1. **Load Notes**: Open grading modal and verify notes load correctly
2. **Save Notes**: Add/edit notes and verify they save successfully
3. **Status Updates**: Check that status messages display properly
4. **Permissions**: Verify only teachers/admins can see the notes section
5. **UI Feedback**: Confirm loading states and success/error messages work

### 3. Browser Console Testing

```javascript
// Test if functions are available globally
console.log(typeof loadTeacherNotes); // should return "function"
console.log(typeof saveTeacherNotes); // should return "function"

// Test function calls (with valid IDs)
loadTeacherNotes(1, 2);
```

## Migration Notes

### For Developers

- **No Database Changes**: The modularization doesn't affect the database schema
- **API Compatibility**: All existing API endpoints remain unchanged
- **Backward Compatibility**: Existing functionality is preserved
- **CSS Scope**: Module CSS is scoped to avoid conflicts

### Potential Issues

1. **Path Issues**: Ensure the module path `modules/teacher_notes_module.php` is correct relative to the including file
2. **Variable Scope**: Make sure required PHP variables are available in the including context
3. **JavaScript Dependencies**: Verify global JavaScript variables are set before calling module functions

## Future Enhancements

The modular structure enables easy future improvements:

1. **Rich Text Editor**: Could easily swap textarea for a WYSIWYG editor
2. **Auto-save**: Add automatic saving functionality
3. **Note Templates**: Add predefined note templates
4. **Note Categories**: Implement categorization of notes
5. **Note History**: Track changes over time
6. **Export/Import**: Add note export functionality

## Troubleshooting

### Common Issues

1. **Module Not Loading**
   - Check file path in include statement
   - Verify file permissions
   - Check PHP syntax errors

2. **JavaScript Errors**
   - Ensure global variables are defined
   - Check browser console for errors
   - Verify AJAX endpoints are accessible

3. **Styling Issues**
   - Check for CSS conflicts
   - Verify Bootstrap classes are available
   - Inspect element styles in browser

### Debug Commands

```bash
# Check if module file exists
ls -la /root/projektid/kriit/views/modules/teacher_notes_module.php

# Check PHP error logs
docker exec kriit-app-1 tail -f /var/log/apache2/error.log

# Test module in isolation
docker exec kriit-app-1 php -r "include '/var/www/html/views/modules/teacher_notes_module.php';"
```

## Conclusion

The teacher notes modularization successfully separates concerns, improves code organization, and maintains all existing functionality while making the codebase more maintainable and extensible. The module follows the same pattern as the existing OpenAPI module, providing consistency across the application architecture.
