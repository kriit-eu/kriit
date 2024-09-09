<?php namespace App;

class assignments extends Controller
{
    public $template = 'master';

    function view()
    {
        $assignmentId = $this->getId();
        $this->assignment = Db::getFirst("SELECT * FROM assignments WHERE assignmentId = ?", [$assignmentId]);
        if (!$this->assignment) {
            error_out('Assignment not found');
        }
    }
}
