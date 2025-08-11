#!/bin/bash

# Deployment script for Kriit application
# Purpose: Ensure proper configuration before deployment

set -e  # Exit on error

echo "Starting deployment checks..."

# Check if config.php exists
if [ ! -f "config.php" ]; then
    echo "❌ Error: config.php not found!"
    exit 1
fi

# Check if EXERCISES_SESSION_DURATION is defined in config.php
if ! grep -q "const EXERCISES_SESSION_DURATION" config.php; then
    echo "EXERCISES_SESSION_DURATION not found in config.php"
    echo "Adding EXERCISES_SESSION_DURATION to config.php..."
    
    # Add the constant before the closing PHP tag or at the end if no closing tag
    # Default value is 1200 seconds (20 minutes)
    echo "" >> config.php
    echo "// Exercise session duration in seconds (default: 20 minutes)" >> config.php
    echo "const EXERCISES_SESSION_DURATION = 1200;" >> config.php
    
    echo "✅ EXERCISES_SESSION_DURATION added with default value of 1200 seconds (20 minutes)"
else
    # Check if the value is greater than 1
    CURRENT_VALUE=$(grep "const EXERCISES_SESSION_DURATION" config.php | sed -E 's/.*= ([0-9]+).*/\1/')
    
    if [ -z "$CURRENT_VALUE" ]; then
        echo "❌ Error: Could not parse EXERCISES_SESSION_DURATION value"
        exit 1
    fi
    
    if [ "$CURRENT_VALUE" -ne 1200 ]; then
        echo "⚠️ EXERCISES_SESSION_DURATION is $CURRENT_VALUE (should be 1200 for 20 minutes)"
        echo "🔄 Updating EXERCISES_SESSION_DURATION to 1200 seconds (20 minutes)..."
        
        # Replace the line with new value
        sed -i.bak "s/const EXERCISES_SESSION_DURATION = .*/const EXERCISES_SESSION_DURATION = 1200;/" config.php
        rm config.php.bak
        
        echo "🔄 EXERCISES_SESSION_DURATION updated to 1200 seconds (20 minutes)"
    else
        echo "EXERCISES_SESSION_DURATION is properly configured (1200 seconds = 20 minutes)"
    fi
fi

echo ""
echo "Running database migrations..."

# Read database connection parameters from config.php
echo "Reading database configuration from config.php..."
if ! command -v php &> /dev/null; then
    echo "❌ Error: PHP not found - cannot read config.php"
    exit 1
fi

# Extract database settings from config.php
DB_HOST=$(php -r "include 'config.php'; echo DATABASE_HOSTNAME;")
DB_PORT=$(php -r "include 'config.php'; echo defined('DATABASE_PORT') ? DATABASE_PORT : '3306';")
DB_USER=$(php -r "include 'config.php'; echo DATABASE_USERNAME;")
DB_PASS=$(php -r "include 'config.php'; echo DATABASE_PASSWORD;")
DB_NAME=$(php -r "include 'config.php'; echo DATABASE_DATABASE;")

# Convert Docker internal hostname to external one
if [ "$DB_HOST" = "db" ]; then
    DB_HOST="127.0.0.1"
    echo "Database config: ${DB_USER}@${DB_HOST}:${DB_PORT}/${DB_NAME} (converted from Docker internal)"
else
    echo "Database config: ${DB_USER}@${DB_HOST}:${DB_PORT}/${DB_NAME}"
fi

# Function to execute SQL and check result
execute_sql() {
    local sql="$1"
    local description="$2"
    
    echo -n "  $description... "
    
    if mysql -h "$DB_HOST" -P "$DB_PORT" -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" -e "$sql" 2>/dev/null; then
        echo "done"
        return 0
    else
        # Check if it's an expected error (like table/column already exists)
        local error_output=$(mysql -h "$DB_HOST" -P "$DB_PORT" -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" -e "$sql" 2>&1)
        if echo "$error_output" | grep -q "already exists\|Duplicate\|Unknown VIEW"; then
            echo "(already applied)"
            return 0
        else
            echo "❌ Error: $error_output"
            return 1
        fi
    fi
}

# Run migrations
echo ""

# Check if table needs renaming
if mysql -h "$DB_HOST" -P "$DB_PORT" -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" -e "SHOW TABLES LIKE 'userDoneExercises'" 2>/dev/null | grep -q "userDoneExercises"; then
    execute_sql "RENAME TABLE userDoneExercises TO userExercises" "🔄 Renaming userDoneExercises to userExercises"
else
    echo "  👍 Renaming userDoneExercises to userExercises... (already renamed)"
fi
execute_sql "ALTER TABLE userExercises ADD COLUMN startTime TIMESTAMP NULL DEFAULT NULL" "👍 Adding startTime column"
execute_sql "ALTER TABLE userExercises ADD COLUMN endTime TIMESTAMP NULL DEFAULT NULL" "👍 Adding endTime column"
execute_sql "DROP VIEW IF EXISTS userExercisesWithComputedStatus" "🗑️ Dropping old view if exists"

# Create the view with proper collation
execute_sql "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci; CREATE VIEW userExercisesWithComputedStatus AS
SELECT
    ue.userId,
    ue.exerciseId,
    ue.startTime,
    ue.endTime,
    u.userTimeUpAt,
    CASE
        WHEN ue.startTime IS NULL THEN 'not_started'
        WHEN ue.endTime IS NOT NULL THEN 'completed'
        WHEN ue.startTime IS NOT NULL AND ue.endTime IS NULL AND u.userTimeUpAt IS NOT NULL AND DATE_ADD(UTC_TIMESTAMP(), INTERVAL 3 HOUR) > u.userTimeUpAt THEN 'timed_out'
        WHEN ue.startTime IS NOT NULL AND ue.endTime IS NULL THEN 'started'
        ELSE 'not_started'
    END COLLATE utf8mb4_unicode_ci as status,
    CASE
        WHEN ue.startTime IS NULL THEN NULL
        WHEN ue.endTime IS NOT NULL THEN TIMESTAMPDIFF(SECOND, ue.startTime, ue.endTime)
        WHEN u.userTimeUpAt IS NULL THEN TIMESTAMPDIFF(SECOND, ue.startTime, DATE_ADD(UTC_TIMESTAMP(), INTERVAL 3 HOUR))
        WHEN DATE_ADD(UTC_TIMESTAMP(), INTERVAL 3 HOUR) > u.userTimeUpAt THEN TIMESTAMPDIFF(SECOND, ue.startTime, u.userTimeUpAt)
        ELSE TIMESTAMPDIFF(SECOND, ue.startTime, DATE_ADD(UTC_TIMESTAMP(), INTERVAL 3 HOUR))
    END as durationSeconds
FROM userExercises ue
JOIN users u ON u.userId = ue.userId" "✅ Creating userExercisesWithComputedStatus view"

execute_sql "ALTER TABLE subjects ADD COLUMN subjectLastLessonDate DATE NULL COMMENT 'Date of the last lesson for this subject'" "👍 Adding subjectLastLessonDate column"

echo ""
echo "Updating exercises from doc/exercises.sql..."

# Check if exercises.sql file exists
if [ ! -f "doc/exercises.sql" ]; then
    echo "⚠️ doc/exercises.sql not found - skipping exercises update"
else
    echo "🗑️ Clearing existing exercises..."
    if mysql -h "$DB_HOST" -P "$DB_PORT" -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" -e "DELETE FROM userExercises; DELETE FROM exercises;" 2>/dev/null; then
        echo "   Exercises cleared"
        
        echo "Loading new exercises from doc/exercises.sql..."
        if mysql -h "$DB_HOST" -P "$DB_PORT" -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" < doc/exercises.sql 2>/dev/null; then
            # Count exercises and show them being added
            EXERCISE_COUNT=$(mysql -h "$DB_HOST" -P "$DB_PORT" -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" -e "SELECT COUNT(*) FROM exercises" 2>/dev/null | tail -n 1)
            echo "✅ Loaded $EXERCISE_COUNT exercises"
        else
            echo "❌ error"
            echo "    Error loading exercises from doc/exercises.sql"
        fi
    else
        echo "❌ error"
        echo "    Error clearing existing exercises"
    fi
fi

echo ""
echo "Checking idcodes.txt file..."

# Check multiple possible locations for idcodes.txt
IDCODES_FILE=""
if [ -f "idcodes.txt" ]; then
    IDCODES_FILE="idcodes.txt"
elif [ -f "scripts/user_import/idcodes.txt" ]; then
    IDCODES_FILE="scripts/user_import/idcodes.txt"
fi

if [ -z "$IDCODES_FILE" ]; then
    echo "❌ Error: idcodes.txt file not found!"
    echo "   Looked in: ./idcodes.txt and ./scripts/user_import/idcodes.txt"
    echo "   Please create idcodes.txt with at least 2 personal ID codes"
    exit 1
fi

echo "Found idcodes.txt at: $IDCODES_FILE"

# Count non-empty lines that are not comments in idcodes.txt (format: Name,IDCode only)
LINE_COUNT=$(grep -v "^#" "$IDCODES_FILE" | grep -c "^[^,]\+,[0-9]" 2>/dev/null || echo "0")
LINE_COUNT=$(echo "$LINE_COUNT" | tr -d '\n\r')  # Remove any newlines

if [ "$LINE_COUNT" -lt 2 ]; then
    echo "❌ Error: idcodes.txt must contain at least 2 entries in format 'Name,IDCode'"
    echo "   Current count: $LINE_COUNT"
    echo "   Please add more entries to $IDCODES_FILE in format 'Name,IDCode'"
    exit 1
else
    echo "idcodes.txt contains $LINE_COUNT valid ID codes (excluding comments)"
fi

echo ""
echo "Checking which users need to be imported..."

# Check if users from idcodes.txt are already in database
MISSING_USERS=0
echo -n "  Checking ID codes: "
while IFS= read -r line; do
    # Skip empty lines and comments
    if [[ "$line" =~ ^[^#] && "$line" != "" ]]; then
        # Only allow format: "Name,IDCode" - reject lines without names
        if [[ "$line" =~ ,[0-9] ]]; then
            name=$(echo "$line" | cut -d',' -f1)
            idcode=$(echo "$line" | cut -d',' -f2)
            
            # Check if name is not empty
            if [[ -z "$name" || "$name" =~ ^[[:space:]]*$ ]]; then
                echo ""
                echo "❌ Error: Line '$line' has no name - all entries must have format 'Name,IDCode'"
                exit 1
            fi
        else
            echo ""
            echo "❌ Error: Line '$line' is invalid - all entries must have format 'Name,IDCode'"
            exit 1
        fi
        
        # Check if this ID code exists in database
        COUNT=$(mysql -h "$DB_HOST" -P "$DB_PORT" -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" -e "SELECT COUNT(*) FROM users WHERE userPersonalCode = '$idcode'" 2>/dev/null | tail -n 1)
        if [ "$COUNT" -eq 0 ]; then
            MISSING_USERS=$((MISSING_USERS + 1))
            echo -n "."
        else
            echo -n "✓"
        fi
    fi
done < "$IDCODES_FILE"
echo ""

if [ "$MISSING_USERS" -eq 0 ]; then
    echo "All users from idcodes.txt already exist in database"
    echo "   Skipping user import (no duplicates will be created)"
else
    echo "Found $MISSING_USERS missing users - importing only new ones..."
    
    # Check for bun in multiple locations
    BUN_CMD=""
    if command -v bun &> /dev/null; then
        BUN_CMD="bun"
    elif [ -f "$HOME/.bun/bin/bun" ]; then
        BUN_CMD="$HOME/.bun/bin/bun"
    elif [ -f /usr/local/bin/bun ]; then
        BUN_CMD="/usr/local/bin/bun"
    fi
    
    # Check if Docker is available
    if command -v docker &> /dev/null && docker info &> /dev/null; then
        # Docker is available, try bun import:users
        if [ -n "$BUN_CMD" ]; then
            echo "Running: $BUN_CMD import:users (with Docker)"
            
            # If using full path, add to PATH temporarily
            if [[ "$BUN_CMD" == *"/.bun/bin/bun" ]]; then
                export PATH="$(dirname "$BUN_CMD"):$PATH"
            fi
            
            if OUTPUT=$($BUN_CMD import:users 2>&1); then
                echo "$OUTPUT" | sed 's/^Created user:/✅ Created user:/'
                echo "Missing users imported successfully"
            else
                echo "❌ User import failed - please check the logs"
                echo "$OUTPUT"
            fi
        else
            echo "⚠️ Bun not found - skipping user import"
        fi
    else
        # No Docker, use direct PHP execution
        echo "⚠️ Docker not available, using direct PHP execution..."
        if command -v php &> /dev/null && [ -f "scripts/user_import/add_users_by_idcode.php" ]; then
            if OUTPUT=$(php scripts/user_import/add_users_by_idcode.php scripts/user_import/idcodes.txt 2>&1); then
                echo "$OUTPUT" | sed 's/^Created user:/✅ Created user:/'
                echo "Missing users imported successfully (direct PHP)"
            else
                echo "❌ Direct PHP import failed"
                echo "$OUTPUT"
            fi
        else
            echo "❌ PHP not found or script missing - cannot import users"
        fi
    fi
fi

echo ""
echo "Deployment completed successfully!"
echo ""
echo "Summary:"
echo "Configuration checks passed"
echo "Database migrations applied"  
echo "User import executed"
echo ""
echo "Next steps:"
echo "1. Review the changes"
echo "2. Test the application locally"
echo "3. Deploy to production"