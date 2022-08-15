<?php
/**
 * @package     Engine_Core
 * @version     $Id: index.php 9764 2012-08-17 00:04:31Z matthew $
 * @copyright   Copyright (c) 2008 Webligo Developments
 * @license     http://www.socialengine.com/license/
 */

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_WARNING);

error_reporting("E_ALL");
// Check version
if (version_compare(phpversion(), '7.2', '<')) {
    printf('PHP 7.2 is required, you have %s', phpversion());
    exit(1);
}

// Constants
define('_ENGINE_R_BASE', dirname($_SERVER['SCRIPT_NAME']));
define('_ENGINE_R_FILE', $_SERVER['SCRIPT_NAME']);

include dirname(__FILE__)
    . DIRECTORY_SEPARATOR.
    'boot.php';

$boot = new Engine_Boot(dirname(__FILE__));
$boot->boot();
