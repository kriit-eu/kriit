# Guide: Importing Users with add_users_by_idcode.php

## 1. Prepare the Input File

- Edit or create `scripts/user_import/idcodes.txt`.
- Each line: `Name,PersonalIDCode`
  - Example:
    ```
    John Doe,39001010001
    Jane Smith,49002020002
    ```
- Lines starting with `#` are comments and ignored.

## 2. Run the Script

- Open a terminal in the project root.
- Run with PHP:
  ```fish
  php scripts/user_import/add_users_by_idcode.php scripts/user_import/idcodes.txt
  ```
- Or use the bun shortcut (if configured):
  ```fish
  bun import:users
  ```

## 3. What Happens

- Reads each valid line from the file.
- For each user:
  - Checks if the personal ID code is valid (11 digits).
  - Skips users already in the database.
  - Adds new users to the `users` table.
  - Prints status messages for each user processed.

## 4. Troubleshooting

- "Skipping invalid line": Check your file formatting.
- "User already exists": That ID code is already in the database.
- "Skipping invalid idcode": The ID code is not 11 digits.

---

**Summary:**
Edit `idcodes.txt` with user data, then run the script as shown above. The script will add new users and report any issues directly in the terminal.
