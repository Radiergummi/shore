<?php

/**
 * Shore bootstrap script
 * ======================
 *
 * This is where the magic happens. A new application instance is booted, the facades are initialized and the routes are
 * loaded into the router. The next step will be executing your middleware, then routing the request.
 *
 * To dig deeper, you could take a peek into the Application class at lib/Application.php.
 */

use Shore\Framework\Application;
use Shore\Framework\Facade;

// Create an application instance
$app = new Application([]);

// Enable facades
Facade::setApplication($app);

// Load all routes from the main routes file.
require_once __DIR__ . DIRECTORY_SEPARATOR . 'routes' . DIRECTORY_SEPARATOR . 'main.php';

return $app;
