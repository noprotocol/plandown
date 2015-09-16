<?php
/**
 * rewrite.php
 */
define('Sledgehammer\STARTED', microtime(true));
if (isset($_SERVER['HEROKU_ADMIN'])) {
	$_SERVER['SERVER_ADMIN'] = $_SERVER['HEROKU_ADMIN'];
}
include(dirname(__FILE__).'/../vendor/sledgehammer/core/render_public_folders.php');
require(dirname(__FILE__).'/../vendor/autoload.php');
$app = new App();
$app->handleRequest();
