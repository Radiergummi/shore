<?php
/**
 * Created by PhpStorm.
 * User: Moritz
 * Date: 01.10.2018
 * Time: 16:12
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
