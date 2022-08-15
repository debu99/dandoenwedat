<?php

/**
 * SocialEngineSolutions
 *
 * @category   Application_Sesmembershipswitch
 * @package    Sesmembershipswitch
 * @copyright  Copyright 2018-2019 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: manifest.php  2018-10-16 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */
return array (
  'package' =>
  array(
    'type' => 'module',
    'name' => 'sesmembershipswitch',
    //'sku' => 'sesmembershipswitch',
    'version' => '5.0.0',
    'dependencies' => array(
        array(
            'type' => 'module',
            'name' => 'core',
            'minVersion' => '5.0.0',
        ),
    ),
    'path' => 'application/modules/Sesmembershipswitch',
    'title' => 'SES - Auto Switching / Notification of Subscription Plans',
    'description' => 'SES - Auto Switching / Notification of Subscription Plans',
    'author' => '<a href="https://socialnetworking.solutions" style="text-decoration:underline;" target="_blank">SocialNetworking.Solutions</a>',
    'callback' => array(
        'path' => 'application/modules/Sesmembershipswitch/settings/install.php',
        'class' => 'Sesmembershipswitch_Installer',
    ),
    'actions' =>
    array(
        0 => 'install',
        1 => 'upgrade',
        2 => 'refresh',
        3 => 'enable',
        4 => 'disable',
    ),
    'directories' =>
    array(
        0 => 'application/modules/Sesmembershipswitch',
    ),
    'files' =>
    array(
        0 => 'application/languages/en/sesmembershipswitch.csv',
    ),
  ),
  // Hooks ---------------------------------------------------------------------
  'hooks' => array(
    array(
        'event' => 'onPaymentSubscriptionUpdateAfter',
        'resource' => 'Sesmembershipswitch_Plugin_Core'
    ),
    array(
        'event' => 'onUserLoginAfter',
        'resource' => 'Sesmembershipswitch_Plugin_Core'
    ),
    array(
        'event' => 'onPaymentSubscriptionUpdateBefore',
        'resource' => 'Sesmembershipswitch_Plugin_Core'
    ),
  ),
  'items'=>array(
    'sesmembershipswitch_plan'
  )
);
