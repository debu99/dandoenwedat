<?php

/**
 * SocialEngineSolutions
 *
 * @category   Application_Sesadvpmnt
 * @package    Sesadvpmnt
 * @copyright  Copyright 2019-2020 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: manifest.php  2019-04-25 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */
 return array (
  'package' =>
  array (
    'type' => 'module',
    'name' => 'sesadvpmnt',
    //'sku' => 'sesadvpmnt',
    'version' => '5.0.0',
	'dependencies' => array(
            array(
                'type' => 'module',
                'name' => 'core',
                'minVersion' => '5.0.0',
            ),
        ),
    'path' => 'application/modules/Sesadvpmnt',
    'title' => 'SES - Stripe Payment Gateway Plugin',
    'description' => 'Socialenginesolutions',
    'author' => '<a href="https://socialnetworking.solutions" style="text-decoration:underline;" target="_blank">SocialNetworking.Solutions</a>',
    'callback' => array(
      'path' => 'application/modules/Sesadvpmnt/settings/install.php',
      'class' => 'Sesadvpmnt_Installer',
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
      0 => 'application/modules/Sesadvpmnt',
    ),
    'files' =>
    array (
      0 => 'application/languages/en/sesadvpmnt.csv',
    ),
  ),
  'routes' => array(
      'sesadvpmnt_payment' => array(
        'route' => 'stripe/payment/:action/*',
        'defaults' => array(
            'module' => 'sesadvpmnt',
            'controller' => 'payment',
            'action' => 'index',
        ),
    ),
  ),
); ?>
