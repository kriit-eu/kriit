<?php namespace App;

class subjects extends Controller
{
    public $template = 'master';

function index()
{
    $conditions = [
        "subjects.teacherId = {$this->auth->userId}",
        $this->auth->groupId ? "subjects.groupId = {$this->auth->groupId}" : null,
        $this->auth->userIsAdmin ? 'true' : null
    ];

    $whereClause = implode(' OR ', array_filter($conditions));

    $this->subjects = Db::getAll("
        SELECT subjectName, t.userName teacherName 
        FROM subjects 
        JOIN users t ON subjects.teacherId = t.userId 
        WHERE $whereClause
    ");
}}
