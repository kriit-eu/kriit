<?php

namespace App;

class AssignmentLearningOutcomesController
{
    /**
     * Sync learning outcomes into learningOutcomes table
     * Endpoint: POST /outcomes/sync
     * Payload: Array of {subjectId, curriculumModuleOutcomes, outcomeName, learningOutcomeOrderNr}
     * DB columns: subjectId, curriculumModuleOutcomes, nameEt, learningOutcomeOrderNr
     */
    public static function syncOutcomes($userId = null)
    {
        $payload = json_decode(file_get_contents('php://input'), true);
        if (!$payload || !is_array($payload)) {
            http_response_code(400);
            echo json_encode(['error' => 'Invalid payload']);
            exit;
        }
        $inserted = \App\Sync::syncOutcomes($payload, $userId);
        echo json_encode(['success' => true, 'inserted' => $inserted]);
        exit;
    }
}
