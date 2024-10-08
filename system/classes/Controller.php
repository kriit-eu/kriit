<?php namespace App;

/**
 * @property Auth auth
 * @property mixed params
 */
#[\AllowDynamicProperties]
class Controller
{
    public $template = 'master';
    public $requires_auth = true;
    public $requires_admin = false;

    function render($template)
    {
        global $supported_languages;

        // Make controller variables available to view
        extract(get_object_vars($this));

        // Load view
        require 'templates/' . $template . '_template.php';
    }

    function getId($index = 0)
    {

        // Verify the existence of the first parameter after the action name in the URL (the project_id)
        if (empty($this->params[$index])) {
            $position = date_format(date_create('Jan ' . ($index + 1)), 'jS');
            throw new \Exception("Required ID ($position parameter) missing from the URL");
        }


        $id = (int)$this->params[$index];


        // Check that project_id is an int greater than 0
        if (empty($id)) {
            throw new \Exception('Required ID parameter coerced to 0!');
        }

        // Made it here — all OK
        return $id;
    }

    protected function redirect($url)
    {
        $base_url = BASE_URL;
        header("Location: $base_url$url");
        exit();
    }

    function setActive($page)
    {
        list($controller, $action) = explode('/', $page);
        return $controller === $this->controller && $action === $this->action ? 'active' : '';
    }
}
