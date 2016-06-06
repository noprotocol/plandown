<?php
namespace Plandown;

use Iterator;
use Plandown\Jira;
use Sledgehammer\Core\Object;
use Sledgehammer\Core\Url;

/**
 * Lazyload all pages in the jira resultsset.
 */
class PagedResult extends Object implements Iterator {

    const PER_PAGE = 50;

    public $jira;
    public $url;
    public $pages = [];
    public $index = 0;

    function __construct(Jira $jira, Url $url) {
        $this->jira = $jira;
        $this->url = new Url($url);
    }

    public function current() {
        $pageIndex = floor($this->index / static::PER_PAGE);
        $offset = $this->index - ($pageIndex * static::PER_PAGE);
        $page = $this->getPage($pageIndex);
        $issue = $page->issues[$offset];
        return $issue;
    }

    public function key() {
        return $this->index;
    }

    public function next() {
        $this->index++;
    }

    public function rewind() {
        $this->index = 0;
    }

    public function valid() {
        return $this->index < $this->getPage(0)->total;
    }

    private function getPage($index) {
        if (isset($this->pages[$index])) {
            return $this->pages[$index];
        }
        $this->url->query['startAt'] = $index * static::PER_PAGE;
        $this->url->query['maxResults'] = static::PER_PAGE;
        $result = $this->jira->api([CURLOPT_URL => (string) $this->url]);
        $this->pages[$index] = $result;
        return $result;
    }

}
