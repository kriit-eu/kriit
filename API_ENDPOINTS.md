# Kriit API Endpoints Documentation

This document provides a comprehensive overview of all API endpoints available in the Kriit system.

## API Base URL Structure

- **Base Pattern**: `/api/{controller}/{action}`
- **Authentication**: Required for all endpoints (teachers/admins for most)
- **Method**: Primarily POST requests for data submission
- **Response Format**: JSON with standardized structure via `stop()` function

---

## Chrome Extension Compatible APIs

These endpoints are particularly suitable for Chrome extension integration due to their read-only nature, lightweight responses, and minimal side effects:

### **Read-Only Data Retrieval**

#### `GET /api/groups/index`
**Purpose**: Retrieve all groups (suitable for dropdown population)
- **CORS**: Supported
- **Use Case**: Group selection in extension UI
- **Response Size**: Minimal
- **Authentication**: Required

#### `GET /api/groups/view/{id}`
**Purpose**: Get specific group details
- **CORS**: Supported  
- **Use Case**: Display group information
- **Response Size**: Small
- **Authentication**: Required

#### `POST /api/teachernotes/index`
**Purpose**: Get shared teacher notes (read-only access)
- **CORS**: Supported
- **Use Case**: Display existing notes in extension
- **Response Size**: Variable (text content)
- **Authentication**: Teacher/Admin required

### **Lightweight Write Operations**

#### `POST /api/teachernotes/save`
**Purpose**: Save teacher notes (minimal database impact)
- **CORS**: Supported
- **Use Case**: Quick note-taking from extension
- **Side Effects**: Minimal (single table update)
- **Authentication**: Teacher/Admin required

#### `POST /assignments/ajax_saveMessage`
**Purpose**: Add comments/messages
- **CORS**: Supported
- **Use Case**: Quick commenting from extension
- **Side Effects**: Minimal (message logging)
- **Authentication**: Required

### **Validation & Utility**

#### `POST /assignments/ajax_validateAndCheckLinkAccessibility`
**Purpose**: Validate URLs without saving
- **CORS**: Supported
- **Use Case**: Real-time URL validation in extension
- **Side Effects**: None (validation only)
- **Authentication**: Required

#### `POST /assignments/ajax_getOpenApiPrompt`
**Purpose**: Retrieve OpenAPI prompt text
- **CORS**: Supported
- **Use Case**: Display prompts in extension
- **Side Effects**: None (read-only)
- **Authentication**: Required

### **Not Recommended for Chrome Extensions**

The following endpoints are **NOT suitable** for Chrome extension use due to security, performance, or architectural concerns:

#### **Heavy Data Operations**
- `POST /api/subjects/getDifferences` - Large payload processing
- `POST /api/users/addGroupStudents` - Bulk user operations
- `POST /api/assignments/addOrUpdate` - Complex assignment creation

#### **Administrative Operations**
- `POST /admin/AJAX_deleteAssignment` - Destructive operations
- `POST /admin/AJAX_deleteStudent` - User management
- `POST /admin/AJAX_addUser` - Account creation

#### **System Configuration**
- `POST /assignments/ajax_saveOpenApiPrompt` - System settings
- Most admin AJAX endpoints - Configuration changes

---

## Chrome Extension Implementation Guidelines

### **Authentication Considerations**

```javascript
// Example: Using session-based auth in extension
const apiCall = async (endpoint, data) => {
  const response = await fetch(`${BASE_URL}/api/${endpoint}`, {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
    },
    credentials: 'include', // Include session cookies
    body: JSON.stringify(data)
  });
  
  if (response.status === 403) {
    // Handle authentication failure
    redirectToLogin();
  }
  
  return response.json();
};
```

### **CORS Configuration**

Ensure your Kriit server has proper CORS headers configured:

```php
// In index.php - already implemented
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
```

### **Recommended Extension Features**

1. **Quick Note Taking**
   - Use `POST /api/teachernotes/save`
   - Lightweight overlay on web pages
   - Auto-save functionality

2. **Assignment Overview**
   - Use `GET /api/groups/index` for group selection
   - Display assignment summaries
   - Read-only dashboard

3. **URL Validation**
   - Use `POST /assignments/ajax_validateAndCheckLinkAccessibility`
   - Real-time feedback on student submissions
   - Link checking for teachers

4. **Message/Comment System**
   - Use `POST /assignments/ajax_saveMessage`
   - Quick feedback on assignments
   - Contextual commenting

### **Security Best Practices**

1. **Minimal Permissions**: Request only necessary host permissions
2. **Data Validation**: Validate all data before sending to API
3. **Error Handling**: Graceful handling of authentication failures
4. **Rate Limiting**: Implement client-side rate limiting
5. **Secure Storage**: Use Chrome's storage API for sensitive data

### **Example Extension Manifest**

```json
{
  "manifest_version": 3,
  "name": "Kriit Assistant",
  "permissions": [
    "activeTab",
    "storage"
  ],
  "host_permissions": [
    "https://your-kriit-domain.com/*"
  ],
  "content_scripts": [{
    "matches": ["https://your-kriit-domain.com/*"],
    "js": ["content.js"]
  }]
}
```

---

## 1. Assignments API (`/api/assignments`)

### `POST /api/assignments/addOrUpdate`

**Purpose**: Create or update assignments from external systems

**Parameters**:
- `assignmentExternalId` (required) - Unique identifier from external system
- `subjectId` OR `subjectName` (required, but not both) - Subject identification
- `groupId` OR `groupName` (required, but not both) - Group identification
- `subjectExternalId` (required) - External subject identifier
- `assignmentDueAt` (optional) - Due date in YYYY-MM-DD format
- `assignmentInstructions` (required) - Assignment instructions/description
- `teachersData` (optional) - Array of teacher objects with:
  - `teacherName`
  - `teacherPersonalCode`
  - `teacherEmail`

**Returns**: 
- Success (200): Assignment ID
- Error (400): Validation error message
- Error (403): Permission denied

**Example Request**:
```json
{
  "assignmentExternalId": "EXT123",
  "subjectName": "Programming Fundamentals",
  "groupName": "CS101",
  "subjectExternalId": "SUBJ456",
  "assignmentDueAt": "2025-12-31",
  "assignmentInstructions": "Complete the coding exercise",
  "teachersData": [
    {
      "teacherName": "John Doe",
      "teacherPersonalCode": "38001010001",
      "teacherEmail": "john.doe@school.edu"
    }
  ]
}
```

### `POST /api/assignments/deleteAssignment`

**Purpose**: Delete assignment by external ID

**Parameters**:
- `assignmentExternalId` (required) - External assignment identifier

**Returns**:
- Success (200): "Assignment deleted"
- Error (404): "Assignment not found"
- Error (500): "Failed to delete assignment"

---

## 2. Groups API (`/api/groups`)

### `GET /api/groups/index`

**Purpose**: Retrieve all groups

**Parameters**: None

**Returns**: Array of all groups with properties:
- `groupId`
- `groupName`

### `GET /api/groups/view/{id}`

**Purpose**: Get specific group details

**Parameters**: 
- `{id}` - Group ID in URL path

**Returns**: Group object with detailed information

### `POST /api/groups/add`

**Purpose**: Create new group

**Parameters**:
- `groupName` (required) - Must be unique

**Returns**:
- Success (200): New group ID
- Error (400): "Invalid groupName"
- Error (409): "Group already exists"

**Example Request**:
```json
{
  "groupName": "Computer Science 2025"
}
```

---

## 3. Subjects API (`/api/subjects`)

### `POST /api/subjects/getDifferences`

**Purpose**: Synchronize subjects and assignments between Kriit and external systems

**Parameters**:
- JSON body with subjects array
- `systemId` (optional, defaults to 1) - External system identifier

**Request Body Structure**:
```json
[
  {
    "subjectId": "SUBJ123",
    "subjectName": "Programming",
    "groupName": "CS101",
    "assignments": [
      {
        "assignmentId": "ASSIGN456",
        "assignmentName": "Hello World",
        "dueDate": "2025-12-31"
      }
    ]
  }
]
```

**Returns**: Array of differences between systems

**Features**:
- Adds missing entities from external system
- Calculates differences between systems
- Logs sync activities for auditing

---

## 4. Teacher Notes API (`/api/teachernotes`)

### `POST /api/teachernotes/index`

**Purpose**: Get shared teacher notes for specific assignment and student

**Access**: Teachers and admins only

**Parameters**:
- `assignmentId` (required) - Assignment identifier
- `studentId` (required) - Student identifier

**Returns**: Notes object containing:
- `notes` - Note content
- `createdAt` - Creation timestamp
- `updatedAt` - Last update timestamp
- `updatedBy` - Username of last updater

**Example Request**:
```json
{
  "assignmentId": "123",
  "studentId": "456"
}
```

### `POST /api/teachernotes/save`

**Purpose**: Save shared teacher notes

**Access**: Teachers and admins only

**Parameters**:
- `assignmentId` (required) - Assignment identifier
- `studentId` (required) - Student identifier
- `noteContent` (required) - Note text content

**Returns**:
- Success (200): `{"success": true}`
- Error (400): Parameter validation errors
- Error (403): "Access denied"

**Behavior**:
- Updates existing note if one exists
- Creates new note if none exists
- Deletes note if content is empty
- Tracks current user as last updater

---

## 5. Users API (`/api/users`)

### `POST /api/users/addGroupStudents`

**Purpose**: Add or update students in a group

**Parameters**:
- `groupName` (required) - Target group name
- `students` (required) - Array of student objects containing:
  - `id` - Student external ID
  - `fullname` - Student full name
  - `idcode` - Personal identification code

**Returns**: Array of personal codes for users without email addresses

**Example Request**:
```json
{
  "groupName": "CS101",
  "students": [
    {
      "id": "STU123",
      "fullname": "Jane Smith",
      "idcode": "50001010001"
    }
  ]
}
```

**Behavior**:
- Updates existing users or creates new ones
- Assigns students to specified group
- Logs user creation/update activities

### `POST /api/users/addStudentsEmails`

**Purpose**: Update student email addresses

**Parameters**:
- `usersEmailsData` (required) - Array of objects containing:
  - `userPersonalCode` - Student personal code
  - `userEmail` - Email address to assign

**Returns**: "Emails added" on success

**Example Request**:
```json
{
  "usersEmailsData": [
    {
      "userPersonalCode": "50001010001",
      "userEmail": "jane.smith@student.edu"
    }
  ]
}
```

---

## AJAX Endpoints (Non-API)

These endpoints are accessible through the main application controllers:

### Assignments Controller AJAX

- `POST /assignments/ajax_saveAssignmentGrade` - Save student grade and criteria
- `POST /assignments/ajax_saveStudentSolutionUrl` - Save solution URL for student
- `POST /assignments/ajax_saveMessage` - Save messages/comments
- `POST /assignments/ajax_editAssignment` - Edit assignment details
- `POST /assignments/ajax_validateAndCheckLinkAccessibility` - Validate solution URLs
- `POST /assignments/ajax_getOpenApiPrompt` - Get OpenAPI prompt text
- `POST /assignments/ajax_saveOpenApiPrompt` - Save OpenAPI prompt (admin only)
- `POST /assignments/ajax_fetchSwaggerDoc` - Fetch OpenAPI specification from URL

### Admin Controller AJAX

- `POST /admin/AJAX_deleteAssignment` - Delete assignment
- `POST /admin/AJAX_deleteStudent` - Delete student
- `POST /admin/AJAX_addAssignment` - Add new assignment
- `POST /admin/AJAX_addSubject` - Add new subject
- `POST /admin/AJAX_addUser` - Add new user
- `POST /admin/AJAX_addStudent` - Add new student
- `POST /admin/AJAX_editUser` - Edit user details

---

## Authentication & Authorization

### Access Levels

- **Public**: No endpoints are publicly accessible
- **Authenticated**: All endpoints require valid session
- **Teacher/Admin**: Most endpoints require teacher or admin privileges
- **Admin Only**: Some configuration endpoints (e.g., OpenAPI prompt saving)

### Authentication Method

- Session-based authentication
- User roles checked via `$this->auth->userIsTeacher` and `$this->auth->userIsAdmin`

---

## Response Format

All endpoints use standardized responses via the `stop()` function:

### Success Response
```json
{
  "status": 200,
  "data": "response_data_here"
}
```

### Error Response
```json
{
  "status": 400,
  "error": "Error message description"
}
```

### HTTP Status Codes Used

- **200**: Success
- **400**: Bad Request (validation errors)
- **403**: Forbidden (access denied)
- **404**: Not Found
- **409**: Conflict (duplicate resources)
- **500**: Internal Server Error

---

## External System Integration

The API is designed to integrate with external educational systems (primarily Tahvel):

### Supported Systems

- **System ID 1**: Tahvel (default)
- Additional systems can be configured via `systemId` parameter

### Integration Features

- **Bidirectional Sync**: Compare and sync data between systems
- **Conflict Resolution**: Handles updates and conflicts
- **Activity Logging**: Comprehensive audit trail
- **Data Validation**: Ensures data integrity across systems

### Sync Process

1. External system sends data via `/api/subjects/getDifferences`
2. Kriit adds missing entities (groups, subjects, assignments, users)
3. System calculates differences between local and external data
4. Returns differences for external system to process
5. Activities are logged for audit purposes

---

## Error Handling

### Common Error Scenarios

1. **Invalid Authentication**: 403 Forbidden
2. **Missing Parameters**: 400 Bad Request
3. **Duplicate Resources**: 409 Conflict
4. **External System Errors**: 500 Internal Server Error
5. **Data Validation Failures**: 400 Bad Request with specific message

### Best Practices

- Always check HTTP status codes
- Handle authentication errors gracefully
- Validate parameters before making requests
- Implement retry logic for temporary failures
- Log errors for debugging purposes

---

## Rate Limiting

Currently, no explicit rate limiting is implemented. Consider implementing rate limiting for production deployments to prevent abuse.

---

## API Versioning

The current API does not implement versioning. Future versions should consider:

- URL-based versioning (e.g., `/api/v1/assignments`)
- Header-based versioning
- Backward compatibility strategies

---

## Development Notes

### Adding New Endpoints

1. Create controller in `controllers/api/` directory
2. Extend `Controller` class
3. Implement authentication checks
4. Use `stop()` function for responses
5. Follow existing naming conventions

### Testing

- Test with valid authentication tokens
- Verify parameter validation
- Test error scenarios
- Check response formats
- Validate database changes

### Security Considerations

- All endpoints require authentication
- Input validation is implemented
- SQL injection protection via parameterized queries
- XSS protection via output encoding
- Access control based on user roles
