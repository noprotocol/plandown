<?php

use Sledgehammer\Curl;
use Sledgehammer\Json;
use Sledgehammer\Object;

class Jira extends Object {

    public $baseUrl;
    public $username;
    public $password;

    function __construct($domain, $username, $password) {
        $this->baseUrl = $domain . '/rest/api/2/';
        $this->username = $username;
        $this->password = $password;
    }

    function get($path, $parameters = []) {
        $url = new \Sledgehammer\Url($this->baseUrl . $path);
        $url->query = $parameters;
        return $this->api([
                CURLOPT_URL => (string) $url
        ]);
    }

    function post($path, $data) {
        $url = new \Sledgehammer\Url($this->baseUrl . $path);
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
     * 
     * @param string $jql
     * @param array|string $fields
     * @return object
     */
    function query($jql, $fields = null) {
        $parameters = ['jql' => $jql];
        if ($fields !== null) {
            if (is_array($fields)) {
                $fields = implode(',', $fields);
            }
            $parameters['fields'] = $fields;
        }
        return $this->get('search', $parameters);
    }

    function api($options = []) {
        $options[CURLOPT_USERNAME] = $this->username;
        $options[CURLOPT_PASSWORD] = $this->password;
        $options[CURLOPT_FAILONERROR] = false;
        $options[CURLOPT_RETURNTRANSFER] = true;

        $options += Curl::$defaults;
        $response = new Curl($options);
        $body = $response->getBody();
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

}
