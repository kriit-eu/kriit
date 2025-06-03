# OpenAPI Button Module – Recent Changes & Usage

## Recent Changes

- **Modularization:**
  The OpenAPI button and modal functionality have been moved into a reusable module:  
  `views/modules/openapi_module.php`.

- **Easy Integration:**
  You can now add the OpenAPI button and modal to any page by including this module file.  
  The button will only be visible to non-student users.

- **Automatic Modal & Button:**
  The module provides both the OpenAPI button and the modal dialog for fetching and copying OpenAPI specs, including prompt management for admins.

- **Bootstrap & Icons:**
  The module relies on Bootstrap (for modal and tooltips) and Bootstrap Icons.  
  Ensure these are loaded on any page where you use the module.

## How to Use

1. **Set Required Variables:**  
   Before including the module, make sure the following variables are set in your view:
   - `$isStudent` (boolean): Should be `true` for students, `false` otherwise.
   - `$this->auth->userIsAdmin` (boolean): Used to determine admin privileges.

2. **Include the Module:**  
   Add this line where you want the OpenAPI button to appear:
   ```php
   <?php include __DIR__ . '/../modules/openapi_module.php'; ?>
   ```
   Adjust the path as needed based on your file’s location.

3. **Dependencies:**  
   Make sure your page includes Bootstrap CSS/JS and Bootstrap Icons.

4. **Result:**  
   The OpenAPI button will appear for non-student users. Clicking it opens a modal for fetching, viewing, and copying the OpenAPI specification and prompt.
