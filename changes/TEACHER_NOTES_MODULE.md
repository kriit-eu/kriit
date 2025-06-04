# Teacher Notes Module - Modularization Documentation

## Overview

The teacher notes feature has been refactored from a monolithic implementation into a reusable modular component with shared notes functionality. This change improves code organization, maintainability, and reusability across the application. **Important: Teacher notes are now shared among all teachers and admins**, replacing the previous private notes system.

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
- **Shared Notes Logic**: Modified to support shared notes visible to all teachers/admins
- **Last Updater Tracking**: Tracks which teacher last modified the notes

### 4. Updated API Logic for Shared Notes

**Key Changes Made:**
- **Removed Teacher-Specific Filtering**: Notes are no longer filtered by `teacherId` in retrieval
- **Single Note Per Assignment/Student**: Only one shared note exists per assignment-student combination
- **Last Updater Tracking**: The `teacherId` field now tracks who last updated the note
- **User Information Display**: Shows who last modified the notes and when
- **Database Query Updates**: Modified SQL queries to support shared note functionality

**File Modified:** `/controllers/grading.php`

**Removed:**
- `getTeacherNotes()` method (moved to API controller)
- `saveTeacherNotes()` method (moved to API controller)

**File Modified:** `/views/modules/teacher_notes_module.php`

**Updated:**
- API endpoint URLs changed from `grading/getTeacherNotes` to `api/teachernotes`
- API endpoint URLs changed from `grading/saveTeacherNotes` to `api/teachernotes/save`
- UI text changed from "Privaatsed märkmed (ainult õpetajale nähtav)" to "Õpetajate märkmed (kõigile õpetajatele nähtav)"
- Placeholder text updated to reflect shared nature of notes
- Status display enhanced to show who last updated the notes

### 5. Benefits of Modularization

- **Code Organization**: Related functionality grouped together
- **Reusability**: Module can be included in other views if needed
- **Maintainability**: Changes only need to be made in one place
- **Testing**: Easier to test individual components
- **Separation of Concerns**: Clear boundaries between features
- **API Architecture**: Dedicated API controller follows REST conventions
- **Error Handling**: Comprehensive error logging and user feedback
- **Collaborative Teaching**: Teachers can now collaborate on student notes
- **Transparency**: All teaching staff can see notes made by colleagues
- **Accountability**: Notes show who last made changes and when

## Module Structure

### JavaScript Functions

```javascript
loadTeacherNotes(assignmentId, studentId)
```
- Loads existing shared teacher notes for a specific assignment/student combination
- Shows loading state and handles errors
- Updates UI with note content, last modified timestamp, and updater information
- **Note**: Retrieves the most recent shared note visible to all teachers/admins

```javascript
saveTeacherNotes()
```
- Saves current note content to the shared database record
- Updates the `teacherId` field to track who last modified the note
- Provides visual feedback during save operation
- Handles success/error states with appropriate messaging
- **Note**: Updates the single shared note record for the assignment/student

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
- Textarea for shared note content
- Status display for last updated information and updater name
- Save button with loading spinner
- Proper labeling indicating collaborative nature ("Õpetajate märkmed")
- Clear indication that notes are visible to all teachers and admins

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
- `api/teachernotes` - Retrieve existing shared notes (GET/POST)
- `api/teachernotes/save` - Save note changes to shared record (POST)

**API Behavior:**
- **GET/POST `api/teachernotes`**: Returns the most recent note for assignment/student combination, including updater information
- **POST `api/teachernotes/save`**: Updates the shared note record and sets current user as last updater

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

## Shared Notes Architecture

### Database Design

The teacher notes system uses the existing `teacherNotes` table structure but with modified logic:

```sql
CREATE TABLE `teacherNotes` (
  `noteId` int unsigned NOT NULL AUTO_INCREMENT,
  `studentId` int unsigned NOT NULL COMMENT 'Student ID',
  `assignmentId` int unsigned NOT NULL COMMENT 'Assignment ID',
  `teacherId` int unsigned NOT NULL COMMENT 'Teacher who last updated the note',
  `noteContent` text NOT NULL COMMENT 'The shared note content',
  `createdAt` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updatedAt` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  -- indexes and constraints...
);
```

**Key Changes in Logic:**
- `teacherId` now represents "who last updated" instead of "note owner"
- Only one note record exists per `(assignmentId, studentId)` combination
- All teachers/admins can see and edit the same note

### API Query Changes

**Before (Private Notes):**
```sql
SELECT noteContent, createdAt, updatedAt 
FROM teacherNotes 
WHERE assignmentId = ? AND studentId = ? AND teacherId = ?
```

**After (Shared Notes):**
```sql
SELECT tn.noteContent, tn.createdAt, tn.updatedAt, u.userName as updatedBy
FROM teacherNotes tn
LEFT JOIN users u ON u.userId = tn.teacherId
WHERE tn.assignmentId = ? AND tn.studentId = ?
ORDER BY tn.updatedAt DESC
LIMIT 1
```

### Migration from Private to Shared Notes

**Important**: The system transition from private to shared notes means:
1. **Existing private notes**: Only the most recently updated note per assignment/student will be visible
2. **Potential data**: Some teacher-specific notes may become inaccessible
3. **Recommended**: Review existing notes before deployment to production

**Migration Strategy:**
```sql
-- Optional: Consolidate existing notes before switching to shared system
-- This query shows what notes exist per assignment/student
SELECT assignmentId, studentId, COUNT(*) as note_count, 
       GROUP_CONCAT(teacherId) as teachers
FROM teacherNotes 
GROUP BY assignmentId, studentId 
HAVING note_count > 1;
```

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

1. **Load Shared Notes**: Open grading modal and verify shared notes load correctly for all teachers
2. **Save Notes**: Add/edit notes and verify they save successfully and are visible to other teachers
3. **Status Updates**: Check that status messages display properly with updater information
4. **Permissions**: Verify only teachers/admins can see the notes section
5. **UI Feedback**: Confirm loading states and success/error messages work
6. **Cross-Teacher Visibility**: Test that notes saved by one teacher are visible to another teacher
7. **Last Updater Display**: Verify that the correct teacher name shows as the last updater

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

- **No Database Schema Changes**: The modularization uses existing table structure
- **Logic Changes**: API behavior changed from private to shared notes
- **Backward Compatibility**: UI functionality preserved, but note visibility changed
- **CSS Scope**: Module CSS is scoped to avoid conflicts
- **Data Implications**: Existing private notes become shared among teachers

### Potential Issues

1. **Path Issues**: Ensure the module path `modules/teacher_notes_module.php` is correct relative to the including file
2. **Variable Scope**: Make sure required PHP variables are available in the including context
3. **JavaScript Dependencies**: Verify global JavaScript variables are set before calling module functions
4. **Database Column Names**: Ensure `userName` field exists in users table (fixed in current implementation)
5. **Shared Notes Transition**: Teachers may see notes they didn't create from the previous private system

## Future Enhancements

The modular structure enables easy future improvements:

1. **Rich Text Editor**: Could easily swap textarea for a WYSIWYG editor
2. **Auto-save**: Add automatic saving functionality
3. **Note Templates**: Add predefined note templates
4. **Note Categories**: Implement categorization of notes
5. **Note History**: Track changes over time with full revision history
6. **Export/Import**: Add note export functionality
7. **Real-time Collaboration**: Add live editing indicators when multiple teachers are viewing
8. **Note Permissions**: Add granular permissions for different teacher roles
9. **Notification System**: Alert teachers when colleagues update notes

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

The teacher notes modularization successfully separates concerns, improves code organization, and **transforms the system from private teacher notes to collaborative shared notes visible to all teaching staff**. The module follows the same pattern as the existing OpenAPI module, providing consistency across the application architecture.

**Key Achievement**: The implementation now supports true collaborative teaching through shared notes while maintaining the modular architecture benefits. Teachers can work together more effectively by sharing observations and feedback about student assignments in a transparent, trackable manner.
