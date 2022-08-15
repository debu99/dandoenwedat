<?php

/**
 * SocialEngineSolutions
 *
 * @category   Application_Sesadvancedactivity
 * @package    Sesadvancedactivity
 * @copyright  Copyright 2016-2017 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: manifest.php  2017-01-12 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */

return array (
  'package' =>
  array (
    'type' => 'module',
    'name' => 'sesadvancedactivity',
    //'sku' => 'sesadvancedactivity',
    'version' => '5.0.0',
    'dependencies' => array(
        array(
            'type' => 'module',
            'name' => 'core',
            'minVersion' => '5.0.0',
        ),
    ),
    'path' => 'application/modules/Sesadvancedactivity',
    'title' => 'SNS: Professional Activity & Nested Comments Plugin',
    'description' => 'SNS: Professional Activity & Nested Comments Plugin',
     'author' => '<a href="https://socialnetworking.solutions" style="text-decoration:underline;" target="_blank">SocialNetworking.Solutions</a>',
    'callback' => array(
			'path' => 'application/modules/Sesadvancedactivity/settings/install.php',
			'class' => 'Sesadvancedactivity_Installer',
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
      'application/modules/Sesadvancedactivity',
      'application/modules/Sesadvancedcomment',
      'application/modules/Sesfeedbg',
      'application/modules/Sesfeedgif',
      'application/modules/Sesfeelingactivity',
    ),
    'files' =>
    array (
      'public/admin/welcome-icon.png',
      'public/admin/feed.png',
      'public/admin/store-header-bg.png',
      'application/languages/en/sesadvancedactivity.csv',
      'application/languages/en/sesadvancedcomment.csv',
      'application/languages/en/sesfeedbg.csv',
      'application/languages/en/sesfeedgif.csv',
      'application/languages/en/sesfeelingactivity.csv',
    ),
  ),
  //Hooks
  'hooks' => array(
    array(
      'event' => 'getActivity',
      'resource' => 'Sesadvancedactivity_Plugin_Core',
    ),
    array(
      'event' => 'addActivity',
      'resource' => 'Sesadvancedactivity_Plugin_Core',
    ),
    array(
      'event' => 'onItemDeleteBefore',
      'resource' => 'Sesadvancedactivity_Plugin_Core',
    ),
    array(
            'event' => 'onRenderLayoutDefault',
        'resource' => 'Sesadvancedactivity_Plugin_Core'
    ),
    array(
        'event' => 'onRenderLayoutDefaultSimple',
        'resource' => 'Sesadvancedactivity_Plugin_Core'
    ),
    array(
        'event' => 'onRenderLayoutMobileDefault',
        'resource' => 'Sesadvancedactivity_Plugin_Core'
    ),
    array(
        'event' => 'onRenderLayoutMobileDefaultSimple',
        'resource' => 'Sesadvancedactivity_Plugin_Core'
    ),
    array(
        'event' => 'onUserLogoutAfter',
        'resource' => 'Sesadvancedactivity_Plugin_Core'
    ),
    array(
        'event' => 'onActivityActionCreateAfter',
        'resource' => 'Sesadvancedactivity_Plugin_Core'
    )
  ),
  // Compose -------------------------------------------------------------------
  'composer' => array(
    'sesadvancedactivityfacebook' => array(
      'script' => array('_composeFacebook.tpl', 'sesadvancedactivity'),
    ),
    'sesadvancedactivitytwitter' => array(
      'script' => array('_composeTwitter.tpl', 'sesadvancedactivity'),
    ),
    'sesadvancedactivitylinkedin' => array(
      'script' => array('_composeLinkedin.tpl', 'sesadvancedactivity'),
    ),
    'sesadvancedactivitylink' => array(
      'script' => array('_composeLink.tpl', 'sesadvancedactivity'),
      'plugin' => 'Sesadvancedactivity_Plugin_LinkComposer',
      'auth' => array('core_link', 'create'),
    ),
     'sesadvancedactivitytargetpost' => array(
      'script' => array('_composetargetpost.tpl', 'sesadvancedactivity'),
    ),
    'fileupload' => array(
      'script' => array('_composefileupload.tpl', 'sesadvancedactivity'),
      'plugin' => 'Sesadvancedactivity_Plugin_FileuploadComposer',
    ),
    'buysell' => array(
      'script' => array('_composebuysell.tpl', 'sesadvancedactivity'),
      'plugin' => 'Sesadvancedactivity_Plugin_BuysellComposer',
    ),
    'sesadvancedactivityfacebookpostembed' => array(
      'script' => array('_composefacebookpostembed.tpl', 'sesadvancedactivity'),
    ),
  ),
  // Items ---------------------------------------------------------------------
  'items' => array(
    'sesadvancedactivity_file',
    'sesadvancedactivity_buysell',
    'sesadvancedactivity_action',
    'sesadvancedactivity_filterlist',
    'sesadvancedactivity_event',
    'sesadvancedactivity_textcolor',
    'sesadvancedactivity_detail',
    'sesadvancedactivity_activitylike',
    'sesadvancedactivity_corelike',
    'sesadvancedactivity_link',
    'sesadvancedactivity_activitycomment',
    'sesadvancedactivity_corecomment',
  ),
  // Routes --------------------------------------------------------------------
  'routes' => array(
     'sesadvancedactivity_extended' => array(
          'route' => 'sesadvancedactivity/ajax/welcome/*',
          'defaults' => array(
              'module' => 'sesadvancedactivity',
              'controller' => 'index',
              'action' => 'welcome',
          ),
          'reqs' => array(
              'controller' => '\D+',
              'action' => '\D+',
          )
      ),
      'sesadvancedactivity_onthisday'=>array(
      'route' => 'onthisday',
      'defaults' => array(
        'module' => 'sesadvancedactivity',
        'controller' => 'index',
        'action' => 'onthisday'
      ),
    ),
     'sesadvancedactivity_hastag' => array(
      'route' => 'hashtag',
      'defaults' => array(
        'module' => 'sesadvancedactivity',
        'controller' => 'index',
        'action' => 'hashtag'
      ),
    ),
  ),
);
