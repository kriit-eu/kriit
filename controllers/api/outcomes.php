<?php

namespace App\api;

use App\Controller;
use App\Outcome;

/**
 * @noinspection PhpUnused
 * Auto-loaded by MVC framework for /api/outcomes/* routes
 */
class outcomes extends Controller
{
    public $requires_auth = true;

    public function sync()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['error' => 'Method Not Allowed. Use POST.']);
            return;
        }
        $userId = isset($this->auth) ? $this->auth->userId : null;
        Outcome::sync($userId);
    }
}