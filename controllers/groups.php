<?php namespace App;

class groups extends Controller
{
    public $template = 'master';

    function index()
    {
        // Get all groups and count of subjects in each group order by group name
        $this->groups = Db::getAll("
            SELECT
                groups.groupId,
                groups.groupName,
                COUNT(subjects.subjectId) subjectCount
            FROM groups
            LEFT JOIN subjects
                ON groups.groupId = subjects.groupId
            GROUP BY groups.groupId, groups.groupName
            ORDER BY groups.groupName");
    }
}
