<?php

use Sledgehammer\DescriptionList;
use Sledgehammer\Dump;
use Sledgehammer\Form;
use Sledgehammer\HttpAuthentication;
use Sledgehammer\HttpError;
use Sledgehammer\InfoException;
use Sledgehammer\Input;
use Sledgehammer\Json;
use Sledgehammer\Template;
use Sledgehammer\View;
use Sledgehammer\VirtualFolder;
use function Sledgehammer\collection;

/**
 * 
 */
class ImportWizard extends VirtualFolder {

    const EPIC_LINK = 'customfield_10008';
    const EPIC_NAME = 'customfield_10009';
    const POINTS = 'customfield_10004';

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
        $auth = new HttpAuthentication('https://' . $this->subdomain . '.atlassian.net/', function ($username, $password) {
            $this->jira = new Jira('https://' . $this->subdomain . '.atlassian.net', $username, $password);
            try {
                $this->jira->get('myself');
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
            'action' => 'parse',
            'fields' => [
                'Plandown' => new Input(['tag' => 'textarea', 'rows' => 20, 'name' => 'plandown', 'class' => 'form-control']),
                new Input(['type' => 'submit', 'class' => 'btn btn-primary', 'value' => 'continue']),
            ]
        ]);
        return $form;
    }

    function parse() {
        try {
            $stories = Plandown::parse($_REQUEST['plandown']);
        } catch (InfoException $e) {
            return new Template('parse-error.php', [
                'error' => $e->getMessage(),
                'info' => new DescriptionList(['items' => $e->getInformation()])
            ]);
        }
        $projects = collection($this->jira->get('project'))->orderByDescending('id')->select('name', 'id');
        $form = new Form([
            'action' => 'import',
            'fields' => [
                new Input(['name' => 'stories', 'type' => 'hidden', 'value' => Json::encode($stories)]),
                'Into project' => new Input(['type' => 'select', 'name' => 'project', 'attributes' => ['options' => $projects]]),
                new Input(['type' => 'submit', 'class' => 'btn btn-primary', 'value' => 'import']),
            ]
        ]);
        return new Template('stories.php', [
            'stories' => $stories,
            'form' => $form
        ]);
    }

    function import() {
        $projectId = $_REQUEST['project'];
        $stories = collection(Json::decode($_REQUEST['stories']));
        // Synchronize epics
        $epics = array_unique($stories->select('epic')->toArray());
        $existingEpics = $this->jira->query('project=' . $projectId . ' AND issuetype="Epic"', self::EPIC_NAME);
        $epicKeys = [];
        foreach ($existingEpics as $issue) {
            $epicKeys[$issue->fields->{self::EPIC_NAME}] = $issue->key;
            $foundIndex = array_search($issue->fields->{self::EPIC_NAME}, $epics);
            if ($foundIndex !== false) {
                unset($epics[$foundIndex]);
            }
        }
        foreach ($epics as $epic) {
            $result = $this->jira->post('issue', [
                "fields" => [
                    "project" => ['id' => $projectId],
                    "issuetype" => ["name" => "Epic"],
                    "summary" => $epic,
                    self::EPIC_NAME => $epic,
                ]
            ]);
            $epicKeys[$epic] = $result->key;
        }
        // Create stories
        foreach ($stories as $story) {
            $result = $this->jira->post('issue', [
                "fields" => [
                    "project" => ['id' => $projectId],
                    "summary" => $story->summary,
                    "issuetype" => ["name" => "Story"],
                    self::EPIC_LINK => 'key:' . $epicKeys[$story->epic],
                    self::POINTS => $story->points,
                ],
            ]);
        }
        return new Dump($stories->count() . ' stories created');
    }

    function projects() {
        return new Json(collection($this->jira->get('project'))->select('name', 'id')->toArray());
    }

    function epics() {
        return new Dump($this->jira->query('project=' . $_GET['project'] . ' AND issuetype="Epic"', 'summary,' . self::EPIC_NAME)->toArray());
    }

    function issues() {
        return new Dump($this->jira->query('project=' . $_GET['project'])->toArray());
    }

}
