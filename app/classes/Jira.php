<?php

use Sledgehammer\Collection;
use Sledgehammer\Curl;
use Sledgehammer\Html;
use Sledgehammer\Json;
use Sledgehammer\Logger;
use Sledgehammer\Object;
use Sledgehammer\Url;
use function Sledgehammer\format_parsetime;

/**
 * REST API Client for the Jira bugtracker
 * https://developer.atlassian.com/static/rest/jira/6.1.html
 */
class Jira extends Object {

    public $logger;
    public $baseUrl;
    public $username;
    public $password;

    function __construct($domain, $username, $password) {
        $this->baseUrl = $domain . '/rest/api/2/';
        $this->username = $username;
        $this->password = $password;
        $this->logger = new Logger([
            'identifier' => 'Jira',
//            'singular' => 'request',
//            'plural' => 'requests',
            'renderer' => self::class . '::renderLog',
            'columns' => array('Request', 'Duration'),
        ]);
    }

    function get($path, $parameters = []) {
        $url = new Url($this->baseUrl . $path);
        $url->query = $parameters;
        return $this->api([
                    CURLOPT_URL => (string) $url
        ]);
    }

    function post($path, $data) {
        $url = new Url($this->baseUrl . $path);
        return $this->api([
                    CURLOPT_POST => true,
                    CURLOPT_URL => (string) $url,
                    CURLOPT_POSTFIELDS => Json::encode($data),
                    CURLOPT_HTTPHEADER => [
                        'Content-Type: application/json'
                    ]
        ]);
    }

    /**
     * Search for issues using JQL.
     * 
     * @param string $jql  a JQL query string
     * @param array|string $fields  The list of fields to return for each issue. By default, all navigable fields are returned.
     * @param array|string $expand  A comma-separated list of the parameters to expand.
     * @return Collection
     */
    function query($jql, $fields = null, $expand = null) {
        $parameters = ['jql' => $jql];
        if ($fields !== null) {
            if (is_array($fields)) {
                $fields = implode(',', $fields);
            }
            $parameters['fields'] = $fields;
        }
        if ($expand !== null) {
            if (is_array($expand)) {
                $expand = implode(',', $expand);
            }
            $parameters['expand'] = $expand;
        }
        $url = new Url($this->baseUrl . 'search');
        $url->query = $parameters;
        return new Collection(new PagedResult($this, $url));
    }

    /**
     * Perform the Api call
     *
     * @param type $options
     * @return type
     * @throws Exception
     */
    function api($options = []) {
        $start = microtime(true);
        $options[CURLOPT_USERNAME] = $this->username;
        $options[CURLOPT_PASSWORD] = $this->password;
        $options[CURLOPT_FAILONERROR] = false;
        $options[CURLOPT_RETURNTRANSFER] = true;

        $options += Curl::$defaults;
        $response = new Curl($options);
        $body = $response->getBody();
        $this->logger->append($options[CURLOPT_URL], ['relativeUrl' => substr($options[CURLOPT_URL], strlen($this->baseUrl)), 'duration' => microtime(true) - $start]);
        if (in_array($response->http_code, [200, 201])) {
            return Json::decode($body);
        }
        if (substr($body, 0, 1) === '{') {// looks like json?
            $json = json_decode($body);
            if (is_object($json) && isset($json->errorMessages) && count($json->errorMessages) > 0) {
                throw new Exception('[Jira] ' . $json->errorMessages[0]);
            }
            if (is_object($json) && isset($json->errors) && is_object($json->errors)) {
                throw new Exception('[Jira] ' . current(get_object_vars($json->errors)));
            }
        }
        throw new Exception('[Jira] ' . $response->http_code . ' ' . $options[CURLOPT_URL]);
    }

    static function renderLog($entry, $meta) {
        $duration = $meta['duration'];
        if ($duration > 3) {
            $color = 'logentry-alert';
        } elseif ($duration > 1.5) {
            $color = 'logentry-warning';
        } else {
            $color = 'logentry-debug';
        }
        echo '<td title="', Html::escape($entry), '">', Html::escape($meta['relativeUrl']), '</td>';
        echo '<td class="logentry-number ', $color, '"><b>', format_parsetime($duration), '</b>&nbsp;sec</td>';
    }

}
