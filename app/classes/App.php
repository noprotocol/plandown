<?php

use Sledgehammer\Form;
use Sledgehammer\Input;
use Sledgehammer\Template;
use Sledgehammer\View;
use Sledgehammer\Website;
use const Sledgehammer\WEBROOT;
use function Sledgehammer\redirect;

/**
 * 
 */
class App extends Website {

    /**
     * Public methods are accessable as file and must return a View object.
     * "/index.html"
     * @return View
     */
    function index() {
        $form = new Form([
            'method' => 'post',
            'fields' => [
                'JIRA Subdomain' => new Input(['name' => 'subdomain', 'class' => 'form-control']),
                new Input(['type' => 'submit', 'class' => 'btn btn-primary', 'value' => 'Continue']),
            ]
        ]);
        $values = $form->import($error);
        if ($values) {
            redirect($values['subdomain'] . '/');
        }
        return $form;
    }

    public function dynamicFoldername($name) {
        $folder = new DomainFolder($name);
        return $folder->generateContent();
    }

    protected function wrapContent($view) {
        $headers = array(
            'title' => 'Sledgehammer App',
            'css' => WEBROOT . 'mvc/css/bootstrap.css',
        );
        return new Template('layout.php', array('content' => $view), $headers);
    }

}
