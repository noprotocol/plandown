<?php

use Sledgehammer\Alert;
use Sledgehammer\Dump;
use Sledgehammer\Form;
use Sledgehammer\HttpAuthentication;
use Sledgehammer\HttpError;
use Sledgehammer\Input;
use Sledgehammer\Json;
use Sledgehammer\Nav;
use Sledgehammer\Template;
use Sledgehammer\View;
use Sledgehammer\VirtualFolder;
use const Sledgehammer\WEBROOT;
use function Sledgehammer\collection;



/**
 * Example App
 */
class DomainFolder extends VirtualFolder {

    const EPIC_LINK = 'customfield_10008';
    const EPIC_NAME = 'customfield_10009';

    protected $handle_filenames_without_extension = true;
    
    public $subdomain;
    /**
     *
     * @var Jira
     */
    public $jira;
        
    function __construct($subdomain) {
        $this->subdomain = $subdomain;
        parent::__construct();
    }

    public function generateContent() {
        $auth = new HttpAuthentication('https://'.$this->subdomain.'.atlassian.net/', function ($username, $password) {
            $this->jira = new Jira('https://'.$this->subdomain.'.atlassian.net', $username, $password);
            try {
                $this->jira->get('user', ['username' => $username]);
                return true;
            } catch (Exception $e) {
                return false;
            }
        });
        $credentials = $auth->authenticate();
        if (!$credentials) {
            return new HttpError(401);
        }
        return parent::generateContent();
    }


    /**
     * Public methods are accessable as file and must return a View object.
     * "/index.html"
     * @return View
     */
    function index() {
        $form = new Form([
            'method' => 'get',
            'fields' => [
                'Plandown' => new Input(['tag' => 'textarea', 'rows' => 20, 'name' => 'plandown', 'class' => 'form-control']),
                new Input(['type' => 'submit', 'class' => 'btn btn-primary', 'value' => 'continue']),
            ]
        ]);
        $values = $form->import($error);
        if ($values) {
            $stories = Plandown::parse($values['plandown']);
            return new Template('stories.php', ['stories' => $stories]);
        }
        return $form;
    }

    function create_epic() {
        $result = $this->jira->post('issue', [
            "fields" => [
                "project" => ['id' => 14600],
                "summary" => "TEST 124.",
                "description" => "Creating of an EPIC",
                self::EPIC_NAME => "Zoiets?",
                "issuetype" => ["name" => "Epic"],
            ]
        ]);
        return new Dump($result);
    }

    function create_story() {
        $result = $this->jira->post('issue', [
            "fields" => [
                "project" => ['id' => 14600],
                "summary" => "TEST in epic.",
                "description" => "Creating of an issue using project id and issue type names using the REST API",
                "issuetype" => ["name" => "Story"],
                self::EPIC_LINK => 'key:VAN-7'
            ],
        ]);
    }

    function projects() {
        return new Json(collection($this->jira->get('project'))->select('name', 'id')->toArray());
    }

    function epics() {
        return new Dump($this->jira->query('project=' . $_GET['project'] . ' AND issuetype="Epic"', 'summary'));
    }

}
