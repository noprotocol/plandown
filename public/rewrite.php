<?php

use Plandown\App;
use Sledgehammer\Core\Debug\ErrorHandler;
/**
 * rewrite.php
 */
define('Sledgehammer\STARTED', microtime(true));
if (isset($_SERVER['HEROKU_ADMIN'])) {
	$_SERVER['SERVER_ADMIN'] = $_SERVER['HEROKU_ADMIN'];
}
include(dirname(__FILE__).'/../vendor/sledgehammer/core/src/render_public_folders.php');
require(dirname(__FILE__).'/../vendor/autoload.php');
ErrorHandler::enable();

$app = new App();
$app->handleRequest();
