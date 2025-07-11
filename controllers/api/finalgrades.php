<?php

namespace App\api;

use App\Controller;
use App\FinalGradesController;

class finalgrades extends Controller
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
        FinalGradesController::syncOutcomes($userId);
    }
}
