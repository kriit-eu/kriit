<?php
// Usage: php scripts/user_import/idcodes.txt

require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../classes/App/Db.php';
require_once __DIR__ . '/../../classes/App/User.php';

use App\User;

function generateEmail($name) {
    // Convert to lowercase
    $email = strtolower($name);
    
    // Replace Estonian characters
    $estonian_chars = ['õ', 'ä', 'ö', 'ü', 'š', 'ž', 'Õ', 'Ä', 'Ö', 'Ü', 'Š', 'Ž'];
    $replacements = ['o', 'a', 'o', 'y', 's', 'z', 'o', 'a', 'o', 'y', 's', 'z'];
    $email = str_replace($estonian_chars, $replacements, $email);
    
    // Replace spaces with dots (keep hyphens)
    $email = preg_replace('/\s+/', '.', $email);
    
    // Remove any non-alphanumeric characters except dots and hyphens
    $email = preg_replace('/[^a-z0-9.\-]/', '', $email);
    
    // Remove multiple consecutive dots
    $email = preg_replace('/\.+/', '.', $email);
    
    // Remove leading/trailing dots
    $email = trim($email, '.');
    
    return $email . '@vikk.ee';
}

function get_users($argv) {
    if (count($argv) < 2) {
        fwrite(STDERR, "Usage: php add_users_by_idcode.php scripts/user_import/idcodes.txt\n");
        exit(1);
    }
    $arg = $argv[1];
    $users = [];
    if (is_file($arg)) {
        foreach (file($arg) as $line) {
            $line = trim($line);
            if ($line === '' || strpos($line, '#') === 0) continue;
            $parts = array_map('trim', explode(',', $line));
            if (count($parts) == 2) {
                $users[] = ['name' => $parts[0], 'idcode' => $parts[1]];
            } else {
                fwrite(STDERR, "Skipping invalid line: $line\n");
            }
        }
    } else {
        fwrite(STDERR, "Only file input is supported for name+idcode mode.\n");
        exit(1);
    }
    return $users;
}

$users = get_users($argv);

foreach ($users as $user) {
    $name = $user['name'];
    $idcode = $user['idcode'];
    if (!preg_match('/^\d{11}$/', $idcode)) {
        echo "Skipping invalid idcode: $idcode\n";
        continue;
    }
    $existing = User::findByPersonalCode($idcode);
    if ($existing) {
        echo "User already exists: $idcode\n";
        continue;
    }
    $email = generateEmail($name);
    $data = [
        'userName' => $name,
        'userPersonalCode' => $idcode,
        'userEmail' => $email
    ];
    $userId = App\Db::insert('users', $data);
    echo "Created user: $name ($idcode) (ID: $userId) Email: $email\n";
}

echo "Done.\n";
