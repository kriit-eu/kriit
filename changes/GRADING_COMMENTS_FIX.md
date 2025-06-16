# Fix: Teacher messages visible to all students

## Problem

When teachers write messages in the grading interface for a specific student's assignment, all students can see these messages instead of just the intended recipient.

## Root Cause

The grading page was using the `messages` table which only has:

- `userId` (who wrote the message)
- `assignmentId` (which assignment)

But no field to specify **which student** a teacher's message is intended for.

## Solution ✅ IMPLEMENTED

Instead of adding a `targetStudentId` field to the messages table, I used the existing comment system that already works correctly in the subjects page.

### Changes Made

1. **Modified `saveOrUpdateUserAssignment()` method** in `controllers/grading.php`:

   - Replaced `saveMessageInternal()` with `addAssignmentCommentForStudent()`
   - Comments are now stored in `userAssignments.comments` field as JSON
   - Each comment includes `name`, `comment`, and `createdAt`

2. **Added `addAssignmentCommentForStudent()` method** to grading controller:

   - Duplicated the working method from assignments controller
   - Properly targets comments to specific students via the userAssignments table

3. **Updated `getMessages()` method** to fetch from the correct source:

   - Now reads from `userAssignments.comments` for student-specific comments
   - Still includes system notifications from `messages` table (for grade changes)
   - Combines and sorts both sources by creation time

4. **Deprecated the old `saveMessage()` method**:
   - Marked as deprecated since comments now flow through `saveGrade()`

### Additional Fix for Events Display

Added dual-saving approach:

- Comments saved to `userAssignments.comments` for student-specific display
- Notification messages saved to `messages` table for "Sündmused" (events) section

### Result

- ✅ Teacher comments are now targeted to specific students
- ✅ Only the intended student can see teacher comments in "Kommentaarid"
- ✅ All students can see teacher comment notifications in "Sündmused"
- ✅ System notifications (grade changes) still work
- ✅ Consistent with how subjects page handles comments
- ✅ No database schema changes required
