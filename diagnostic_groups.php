<?php
/**
 * Diagnostic script to check student group assignments
 */

$host = 'db';  // Docker service name
$dbname = 'kriit';
$username = 'root';
$password = 'kriitkriit';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

function dbGetAll($query, $params = []) {
    global $pdo;
    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function dbGetFirst($query, $params = []) {
    global $pdo;
    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

echo "Diagnostic: Student Group Assignments\n";
echo "====================================\n\n";

// Check Enelin Männo specifically
$enelin = dbGetFirst("SELECT u.*, g.groupName FROM users u LEFT JOIN groups g ON u.groupId = g.groupId WHERE u.userPersonalCode = ?", ['49011132727']);
if ($enelin) {
    echo "Enelin Männo (49011132727)\n";
    echo "  - userId: {$enelin['userId']}\n";
    echo "  - userName: {$enelin['userName']}\n";
    echo "  - groupId: {$enelin['groupId']}\n";
    echo "  - groupName: {$enelin['groupName']}\n";
    echo "  - userDeleted: {$enelin['userDeleted']}\n";
    echo "  - userIsActive: {$enelin['userIsActive']}\n";
} else {
    echo "Enelin Männo (49011132727) not found in users table.\n";
}
