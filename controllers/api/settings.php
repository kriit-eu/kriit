<?php namespace App;

require_once __DIR__ . '/../../system/functions.php';
require_once __DIR__ . '/../../constants.php';

// Init composer auto-loading
if (!@include_once(__DIR__ . "/../../vendor/autoload.php")) {
    exit('Run composer install');
}

header('Content-Type: application/json');

$auth = new Auth();
$activity = new Activity();

if (!$auth->userId) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    
    if ($path === '/api/settings/email') {
        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';

        if (empty($email) || empty($password)) {
            echo json_encode(['success' => false, 'message' => 'Puuduvad andmed']);
            exit;
        }

        // Verify current password
        if (!password_verify($password, $auth->userPassword)) {
            echo json_encode('vale parool');
            exit;
        }

        // Update email
        if ($auth->updateEmail($auth->userId, $email)) {
            // Log the activity
            $activity->log($auth->userId, 'update_email');
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => 'E-posti muutmine ebaõnnestus']);
        }
    } else if ($path === '/api/settings/password') {
        $currentPassword = $_POST['current_password'] ?? '';
        $newPassword = $_POST['new_password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';

        if (empty($currentPassword) || empty($newPassword) || empty($confirmPassword)) {
            echo json_encode(['success' => false, 'message' => 'Puuduvad andmed']);
            exit;
        }

        if ($newPassword !== $confirmPassword) {
            echo json_encode(['success' => false, 'message' => 'Paroolid ei kattu']);
            exit;
        }

        if (strlen($newPassword) < 8) {
            echo json_encode(['success' => false, 'message' => 'Parool peab olema vähemalt 8 tähemärki pikk']);
            exit;
        }

        // Verify current password
        if (!password_verify($currentPassword, $auth->userPassword)) {
            echo json_encode('vale parool');
            exit;
        }

        // Update password
        if ($auth->updatePassword($auth->userId, $newPassword)) {
            // Log the activity
            $activity->log($auth->userId, 'update_password');
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Parooli muutmine ebaõnnestus']);
        }
    } else {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Endpoint not found']);
    }
} else {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
} 