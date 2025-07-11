<?php

namespace App;

class FinalGradesController
{
    /**
     * Sync SISSEKANNE_O (outcome) entries into finalgrades_outcomes table
     * Endpoint: POST /api/finalgrades/sync
     * Payload: Array of subjects with assignments and results
     */
    public static function syncOutcomes($userId = null)
    {
        $payload = json_decode(file_get_contents('php://input'), true);
        if (!$payload || !is_array($payload)) {
            http_response_code(400);
            echo json_encode(['error' => 'Invalid payload']);
            return;
        }
        $inserted = \App\Sync::syncFinalGrades($payload, $userId);
        echo json_encode(['success' => true, 'inserted' => $inserted]);
    }
}
