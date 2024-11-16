<?php namespace App\api;

use App\Controller;
use App\Db;

class subjects extends Controller
{
    function subjectIsSynchronized(): void
    {
        $tahvelSubjectId = $_POST['tahvelSubjectId'];

        $existingSubject = Db::getFirst("SELECT * FROM subjects WHERE tahvelSubjectId = ?", [$tahvelSubjectId]);

        if ($existingSubject) {
            Db::update('subjects', [
                'isSynchronized' => 1
            ], 'tahvelSubjectId = ?', [$tahvelSubjectId]);
            stop(200, 'Subject is synchronized');
        } else {
            stop(400, 'Subject not found');
        }
    }

    function subjectsSynchronizeData(): void
    {
        // Get IDs from POST data and convert to integers
        $subjectIds = array_map('intval', $_POST['tahvelSubjectIds'] ?? []);

        // If no IDs provided, return empty array
        if (!$subjectIds) {
            stop(200, []);
        }

        // Create placeholders for parameterized query
        $placeholders = implode(',', array_fill(0, count($subjectIds), '?'));

        // SQL query to fetch subjects
        // Execute query with IDs as parameters
        $subjects = Db::getAll("
            SELECT tahvelSubjectId, isSynchronized, subjectName, groupName
            FROM subjects
            JOIN `groups` USING (groupId)
            WHERE tahvelSubjectId IN ($placeholders)
        ", $subjectIds);

        // Map subjects by their IDs for easy access
        $subjectsById = array_column($subjects, null, 'tahvelSubjectId');

        // Prepare the data to return
        $data = [];
        foreach ($subjectIds as $id) {
            $subject = $subjectsById[$id] ?? [];
            $data[] = [
                'tahvelSubjectId' => $id,
                'subjectName'     => $subject['subjectName'] ?? '',
                'groupName'       => $subject['groupName'] ?? '',
                'isSynchronized'  => !empty($subject['isSynchronized']),
            ];
        }

        // Return the data with HTTP status 200
        stop(200, $data);
    }
}
