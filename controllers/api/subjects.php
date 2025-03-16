<?php namespace App\api;

use App\Controller;
use App\Db;
use App\Activity;
use App\Sync;

class subjects extends Controller
{
    function subjectIsSynchronized(): void
    {
        $subjectExternalId = $_POST['subjectExternalId'];

        $existingSubject = Db::getFirst("SELECT * FROM subjects WHERE subjectExternalId = ?", [$subjectExternalId]);

        if ($existingSubject) {
            Db::update('subjects', [
                'isSynchronized' => 1
            ], 'subjectExternalId = ?', [$subjectExternalId]);
            stop(200, 'Subject is synchronized');
        } else {
            stop(400, 'Subject not found');
        }
    }

    function subjectsSynchronizeData(): void
    {
        // Get IDs from POST data and convert to integers
        $subjectIds = array_map('intval', $_POST['subjectExternalIds'] ?? []);

        // If no IDs provided, return empty array
        if (!$subjectIds) {
            stop(200, []);
        }

        // Create placeholders for parameterized query
        $placeholders = implode(',', array_fill(0, count($subjectIds), '?'));

        // SQL query to fetch subjects
        // Execute query with IDs as parameters
        $subjects = Db::getAll("
            SELECT subjectExternalId, isSynchronized, subjectName, groupName
            FROM subjects
            JOIN `groups` USING (groupId)
            WHERE subjectExternalId IN ($placeholders)
        ", $subjectIds);

        // Map subjects by their IDs for easy access
        $subjectsById = array_column($subjects, null, 'subjectExternalId');

        // Prepare the data to return
        $data = [];
        foreach ($subjectIds as $id) {
            $subject = $subjectsById[$id] ?? [];
            $data[] = [
                'subjectExternalId' => $id,
                'subjectName' => $subject['subjectName'] ?? '',
                'groupName' => $subject['groupName'] ?? '',
                'isSynchronized' => !empty($subject['isSynchronized']),
            ];
        }

        // Return the data with HTTP status 200
        stop(200, $data);
    }

    /**
     * Compares the grades in Kriit with the grades in External System, inserts missing subjects, groups, teachers, assignments
     * and students, and returns the differences between the two, using the same format as the input.
     * @return void
     */
    function getDifferences(): void
    {
        $remoteSubjects = json_decode(file_get_contents('php://input'), true);
        
        // Get systemId from request, default to 1 (Tahvel)
        $systemId = intval($_REQUEST['systemId'] ?? 1);
        
        // First, add any missing entities from the external system
        Sync::addMissingEntities($remoteSubjects, $systemId);
        
        // Then, calculate and return the differences between systems
        stop(200, Sync::getSystemDifferences($remoteSubjects, $systemId));
    }

}
