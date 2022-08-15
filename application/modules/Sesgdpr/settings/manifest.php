<?php

/**
 * SocialEngineSolutions
 *
 * @category   Application_Sesgdpr
 * @package    Sesgdpr
 * @copyright  Copyright 2018-2019 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: manifest.php 2018-05-19 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */
  return array (
  'package' =>
  array(
      'type' => 'module',
      'name' => 'sesgdpr',
      //'sku' => 'sesgdpr',
      'version' => '5.0.0',
        'dependencies' => array(
            array(
                'type' => 'module',
                'name' => 'core',
                'minVersion' => '5.0.0',
            ),
        ),
      'path' => 'application/modules/Sesgdpr',
      'title' => 'SES - Professional GDPR Plugin',
      'description' => 'SES - Professional GDPR Plugin',
      'author' => '<a href="https://socialnetworking.solutions" style="text-decoration:underline;" target="_blank">SocialNetworking.Solutions</a>',
      'callback' => array(
          'path' => 'application/modules/Sesgdpr/settings/install.php',
          'class' => 'Sesgdpr_Installer',
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
          0 => 'application/modules/Sesgdpr',
      ),
      'files' =>
      array(
          0 => 'application/languages/en/sesgdpr.csv',
      ),
  ),
  'hooks' => array(

      array(
          'event' => 'onUserLoginAfter',
          'resource' => 'Sesgdpr_Plugin_Core',
      ),
      array(
          'event' => 'onUserSignupAfter',
          'resource' => 'Sesgdpr_Plugin_Core',
      ),
      array(
            'event' => 'onRenderLayoutDefault',
          'resource' => 'Sesgdpr_Plugin_Core'
      ),
      array(
        'event' => 'onUserLogoutBefore',
        'resource' => 'Sesgdpr_Plugin_Core'
      ),
      array(
          'event' => 'onRenderLayoutDefaultSimple',
          'resource' => 'Sesgdpr_Plugin_Core'
      ),
      array(
          'event' => 'onRenderLayoutMobileDefault',
          'resource' => 'Sesgdpr_Plugin_Core'
      ),
      array(
          'event' => 'onRenderLayoutMobileDefaultSimple',
          'resource' => 'Sesgdpr_Plugin_Core'
      )
  ),
  'items' => array('sesgdpr_content','sesgdpr_service','sesgdpr_audit'),
  'routes' => array(
    'sesgdpr_view' => array(
      'route' => 'privacy-center/*',
      'defaults' => array(
        'module' => 'sesgdpr',
        'controller' => 'index',
        'action' => 'index',
      ),
     ),
  ),
);
