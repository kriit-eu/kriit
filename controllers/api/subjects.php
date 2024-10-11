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
}
