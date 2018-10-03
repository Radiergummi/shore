<?php
/**
 * Created by PhpStorm.
 * User: Moritz
 * Date: 28.09.2018
 * Time: 15:03
 */

define('START', microtime(true));

// Define the directory root
define('ROOT', dirname(__DIR__) . DIRECTORY_SEPARATOR);

define('LIBRARY', ROOT . 'lib' . DIRECTORY_SEPARATOR);
define('APP', ROOT . 'app' . DIRECTORY_SEPARATOR);

// Set the debugging state
define('DEBUG', ! ! getenv('DEBUG') ?? false);

// Enable the composer autoloader
require_once ROOT . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';

// Bootstrap the application
$app = require_once APP . 'bootstrap.php';

// Execute the application
echo $app->run(
    $_SERVER,
    $_REQUEST,
    $_GET,
    $_POST,
    $_FILES,
    $_COOKIE,
    $_SESSION ?? []
);

echo microtime(true) - START;
