<?php namespace App\api;

use App\Activity;
use App\Controller;
use App\Db;

// add /api/groups to the URL

class groups extends Controller
{

    function index()
    {
        $this->groups = Db::getAll("SELECT * FROM groups");
    }

    function view()
    {
        $groupId = $this->getId();
        $this->group = Db::getFirst("SELECT * FROM groups WHERE groupId = '{$groupId}'");
    }

    function add()
    {
        //Check if group name is provided
        if (empty($_POST['groupName'])) {
            stop(400, 'Invalid groupName');
        }

        //Check if group name is unique
        if (Db::getFirst("SELECT * FROM groups WHERE groupName = ?", [$_POST['groupName']])) {
            stop(409, 'Group already exists');
        }

        //Save group
        $data = [
            'groupName' => $_POST['groupName'],
        ];

        $groupId = Db::insert('groups', $data);
        Activity::create(ACTIVITY_CREATE_GROUP, $this->auth->userId, $groupId);

        stop(200, $groupId);
    }

}
