<?php
/**
 * Shore entry point
 * =================
 *
 * This file starts your application. It defines a few constants to tell the app where the code is to be found,
 * bootstraps the app and echo's the response. As a side note: This is the only framework file using the path constants.
 * You are free to just use static paths here.
 *
 * To dig deeper, you might want to look at the bootstrap.php file.
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
echo $app->run();

echo microtime(true) - START;
