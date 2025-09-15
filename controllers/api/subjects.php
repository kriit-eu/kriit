<?php namespace App\api;

use App\Controller;
use App\Activity;
use App\Sync;

class subjects extends Controller
{

    /**
     * Compares the grades in Kriit with the grades in External System, inserts missing subjects, groups, teachers, assignments
     * and students, and returns the differences between the two, using the same format as the input.
     * @return void
     */
    function getDifferences(): void
    {
        $rawData = file_get_contents('php://input');
        $requestInfo = [
            'timestamp' => date('Y-m-d H:i:s'),
            'systemId' => $_REQUEST['systemId'] ?? 1,
            'dataSize' => strlen($rawData),
            'clientIp' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            'userAgent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown'
        ];
        
        // Log the sync start event
        Activity::create(ACTIVITY_SYNC_START, null, null, [
            'requestInfo' => $requestInfo,
            'summary' => "External system sync started. Data size: {$requestInfo['dataSize']} bytes"
        ]);
        
        $remoteSubjects = json_decode($rawData, true);
        
        // Validate the input data structure
        if (json_last_error() !== JSON_ERROR_NONE) {
            $errorMsg = "JSON decode error: " . json_last_error_msg();
            Activity::create(ACTIVITY_SYNC_START, null, null, [
                'error' => $errorMsg,
                'status' => 'failed'
            ]);
            stop(400, ["error" => "Invalid JSON data received"]);
        }
        
        if (empty($remoteSubjects) || !is_array($remoteSubjects)) {
            $errorMsg = "Empty or invalid remoteSubjects data";
            Activity::create(ACTIVITY_SYNC_START, null, null, [
                'error' => $errorMsg,
                'status' => 'failed'
            ]);
            stop(400, ["error" => "Invalid data format - expected array of subjects"]);
        }
        
        // Get systemId from request, default to 1 (Tahvel)
        $systemId = intval($_REQUEST['systemId'] ?? 1);
        
        // Log summary of data received
        $subjectCount = count($remoteSubjects);
        $totalAssignments = 0;
        
        foreach ($remoteSubjects as $subject) {
            if (isset($subject['assignments']) && is_array($subject['assignments'])) {
                $totalAssignments += count($subject['assignments']);
            }
        }
        
        Activity::create(ACTIVITY_SYNC_START, null, null, [
            'systemId' => $systemId,
            'subjectCount' => $subjectCount,
            'assignmentCount' => $totalAssignments,
            'summary' => "Processing {$subjectCount} subjects with {$totalAssignments} assignments from system ID {$systemId}"
        ]);
        
        // First, add any missing entities from the external system
        Sync::addMissingEntities($remoteSubjects, $systemId);
        
        // Then, calculate and return the differences between systems
        $differences = Sync::getSystemDifferences($remoteSubjects, $systemId);
        
        // Log the completion and differences
        Activity::create(ACTIVITY_SYNC_START, null, null, [
            'systemId' => $systemId,
            'status' => 'completed',
            'differences' => count($differences),
            'summary' => "Sync completed with " . count($differences) . " subjects having differences"
        ]);
        
        // Also include assignments that were newly created during addMissingEntities
        $newAssignments = Sync::getCreatedAssignmentsBySubject();

        // Additionally, include any existing local assignments that are missing an external ID
        // so the frontend knows these need to be written to the external system as well.
        $localMissing = [];
        $rows = \App\Db::getAll(
            "SELECT a.assignmentId, a.subjectId, s.subjectExternalId, a.assignmentName, a.assignmentEntryDate, a.assignmentDueAt, a.assignmentHours
             FROM assignments a
             JOIN subjects s ON a.subjectId = s.subjectId
             WHERE (a.assignmentExternalId IS NULL OR a.assignmentExternalId = '')"
        );
        foreach ($rows as $r) {
            $sx = $r['subjectExternalId'] ?? null;
            if ($sx === null) continue; // skip assignments for subjects without external id
            if (!isset($localMissing[$sx])) $localMissing[$sx] = [];
            $localMissing[$sx][] = [
                'assignmentExternalId' => null,
                'assignmentName' => $r['assignmentName'] ?? null,
                'assignmentEntryDate' => $r['assignmentEntryDate'] ?? null,
                'assignmentDueAt' => $r['assignmentDueAt'] ?? null,
                'assignmentHours' => isset($r['assignmentHours']) ? $r['assignmentHours'] : null,
                'createdAssignmentId' => $r['assignmentId']
            ];
        }

        // Merge created-by-sync and preexisting local-missing lists (localMissing should not overwrite existing keys)
        foreach ($localMissing as $sx => $list) {
            if (!isset($newAssignments[$sx])) $newAssignments[$sx] = [];
            // Append local missing assignments after any sync-created ones
            $newAssignments[$sx] = array_merge($newAssignments[$sx], $list);
        }

        stop(200, ['differences' => $differences, 'newAssignments' => $newAssignments]);
    }

}
