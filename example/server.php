<?php declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2020-03-05 16:51:41 +0800
 */

defined('BASE_PATH') || define('BASE_PATH', __DIR__ . '/');
defined('RESOURCES_PATH') || define('RESOURCES_PATH', BASE_PATH . 'resources/');


$composer = require BASE_PATH . '../vendor/autoload.php';
$composer->setPsr4('App\\', BASE_PATH . 'app');

// Teddy\Guzzle\DefaultHandler::set('pool');
$app = require BASE_PATH . 'bootstrap/app.php';
$app->runConsole();
