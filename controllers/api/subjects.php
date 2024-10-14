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
            ],'tahvelSubjectId = ?', [$tahvelSubjectId]);
            stop(200, 'Subject is synchronized');
        } else {
            stop(400, 'Subject not found');
        }
    }

    function subjectsSynchronizeData(): void
    {
        $tahvelSubjectsIds = $_POST['tahvelSubjectIds'];
        $data = [];

        $existingSubjects = Db::getAll("SELECT tahvelSubjectId, isSynchronized FROM subjects WHERE tahvelSubjectId IN (" . implode(',', array_map('intval', $tahvelSubjectsIds)) . ")");

        $existingSubjectsMap = [];
        foreach ($existingSubjects as $subject) {
            $existingSubjectsMap[$subject['tahvelSubjectId']] = $subject['isSynchronized'];
        }

        foreach ($tahvelSubjectsIds as $tahvelSubjectId) {
            $isSynchronized = $existingSubjectsMap[$tahvelSubjectId] ?? false;
            $data[] = [
                'tahvelSubjectId' => $tahvelSubjectId,
                'isSynchronized' => (bool)$isSynchronized
            ];
        }

        stop(200, $data);
    }
}
