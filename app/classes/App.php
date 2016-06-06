<?php
namespace Plandown;

use Sledgehammer\Mvc\Component\Form;
use Sledgehammer\Mvc\Component\Input;
use Sledgehammer\Mvc\Component\Template;
use Sledgehammer\Mvc\Website;
use const Sledgehammer\PATH;
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

    /**
     * 
     * @param string $name
     * @return View
     */
    public function folder($name) {
        $folder = new ImportWizard($name);
        return $folder->generateContent();
    }

    protected function wrapContent($view) {
        $headers = array(
            'title' => 'Plandown',
            'css' => WEBROOT . 'mvc/css/bootstrap.css',
        );
        return new Template(PATH.'app/templates/layout.php', array('content' => $view), $headers);
    }

}
