<?php

 /**
 * socialnetworking.solutions
 *
 * @category   Application_Modules
 * @package    Ememsub
 * @copyright  Copyright 2014-2020 Ahead WebSoft Technologies Pvt. Ltd.
 * @license    https://socialnetworking.solutions/license/
 * @version    $Id: manifest.php 2020-01-17 00:00:00 socialnetworking.solutions $
 * @author     socialnetworking.solutions
 */

 return array (
  'package' => 
  array (
    'type' => 'module',
    'name' => 'ememsub',
    'version' => '5.0.0',
	'dependencies' => array(
            array(
                'type' => 'module',
                'name' => 'core',
                'minVersion' => '5.0.0',
            ),
        ),
    //'sku' => 'ememsub',
    'path' => 'application/modules/Ememsub',
    'title' => 'SNS - Membership Subscription Pricing Table & Plan Layout Plugin',
    'description' => 'SNS - Membership Subscription Pricing Table & Plan Layout Plugin',
    'author' => '<a href="https://socialnetworking.solutions" style="text-decoration:underline;" target="_blank">SocialNetworking.Solutions</a>',
    'callback' => 
    array (
      'path' => 'application/modules/Ememsub/settings/install.php',
      'class' => 'Ememsub_Installer',
    ),
    'actions' => 
    array (
      0 => 'install',
      1 => 'upgrade',
      2 => 'refresh',
      3 => 'enable',
      4 => 'disable',
    ),
    'directories' => 
    array (
      0 => 'application/modules/Ememsub',
    ),
    'files' => 
    array (
      0 => 'application/languages/en/ememsub.csv',
    ),
  ),
   // Hooks ---------------------------------------------------------------------
  'hooks' => array(
    array(
      'event' => 'onUserCreateBefore',
      'resource' => 'Ememsub_Plugin_Core',
    ),
    array(
      'event' => 'onUserUpdateBefore',
      'resource' => 'Ememsub_Plugin_Core',
    ),
    array(
      'event' => 'onAuthorizationLevelDeleteBefore',
      'resource' => 'Ememsub_Plugin_Core',
    ),
    array(
      'event' => 'onRenderLayoutDefault',
      'resource' => 'Ememsub_Plugin_Core',
    ),
  ),
  // Items ---------------------------------------------------------------------
  'items' => array(
      'ememsub_feature',
      'ememsub_template',
      'ememsub_style'
  ),
); ?>
