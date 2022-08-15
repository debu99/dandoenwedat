<?php

/**
 * SocialEngineSolutions
 *
 * @category   Application_Sesadvancedcomment
 * @package    Sesadvancedcomment
 * @copyright  Copyright 2016-2017 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: manifest.php 2017-01-19 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */
return array (
  'package' =>
  array (
    'type' => 'module',
    'name' => 'sesadvancedcomment',
    'version' => '4.10.3p6',
    'dependencies' => array(
        array(
            'type' => 'module',
            'name' => 'core',
            'minVersion' => '4.9.4p3',
        ),
    ),
    'path' => 'application/modules/Sesadvancedcomment',
    'title' => '<span style="color:#DDDDDD">SNS - Advanced Nested Comments with Attachments Plugin</span>',
    'description' => '<span style="color:#DDDDDD">SNS - Advanced Nested Comments with Attachments Plugin</span>',
     'author' => '<a href="http://www.socialenginesolutions.com" style="text-decoration:underline;" target="_blank">SocialEngineSolutions</a>',
    'callback' => array(
			'path' => 'application/modules/Sesadvancedcomment/settings/install.php',
			'class' => 'Sesadvancedcomment_Installer',
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
      0 => 'application/modules/Sesadvancedcomment',
    ),
    'files' =>
    array (
      0 => 'application/languages/en/sesadvancedcomment.csv',
      1 => 'public/admin/store-header-bg.png',
    ),
  ),
  // Items ---------------------------------------------------------------------
  'items' => array(
  'sesadvancedcomment_comment',
  'sesadvancedcomment_emotioncategory',
  'sesadvancedcomment_emotiongallery',
  'sesadvancedcomment_emotionfile', 'sesadvancedcomment_reaction',
  ),
  'hooks' => array(
       array(
      'event' => 'onRenderLayoutDefault',
      'resource' => 'Sesadvancedcomment_Plugin_Core',
    ),
    array(
      'event' => 'onRenderLayoutDefaultSimple',
      'resource' => 'Sesadvancedcomment_Plugin_Core',
    ),
    array(
      'event' => 'onRenderLayoutMobileDefault',
      'resource' => 'Sesadvancedcomment_Plugin_Core',
    ),
    array(
      'event' => 'onRenderLayoutMobileDefaultSimple',
      'resource' => 'Sesadvancedcomment_Plugin_Core',
    ),
    ),
  // Routes --------------------------------------------------------------------
  'routes' => array(
  ),
);
