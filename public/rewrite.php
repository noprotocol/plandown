<?php
/**
 * rewrite.php
 */
define('Sledgehammer\STARTED', microtime(true));
include(dirname(__FILE__).'/../vendor/sledgehammer/core/render_public_folders.php');
require(dirname(__FILE__).'/../vendor/autoload.php');

$app = new App();
$app->handleRequest();
