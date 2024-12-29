<?php namespace App;

class subjects extends Controller
{
    public $template = 'master';


    public function index()
    {
        $currentUserId = $this->auth->userId;
        $isAdmin = $this->auth->userIsAdmin;
        
        $this->groups = Group::getAll($currentUserId, $isAdmin);
    }
}
