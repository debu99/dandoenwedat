<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Album
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @author     Jung
 */
return array(
  // Package -------------------------------------------------------------------
  'package' => array(
    'type' => 'module',
    'name' => 'album',
    'version' => '5.0.1',
    'revision' => '$Revision: 10271 $',
    'path' => 'application/modules/Album',
    'repository' => 'socialengine.com',
    'title' => 'Albums',
    'description' => 'Albums',
    'author' => 'Webligo Developments',
    'dependencies' => array(
      array(
        'type' => 'module',
        'name' => 'core',
        'minVersion' => '5.0.0',
      ),
    ),
    'actions' => array(
       'install',
       'upgrade',
       'refresh',
       'enable',
       'disable',
     ),
    'callback' => array(
      'path' => 'application/modules/Album/settings/install.php',
      'class' => 'Album_Installer',
    ),
    'directories' => array(
      'application/modules/Album',
    ),
    'files' => array(
      'application/languages/en/album.csv',
    ),
  ),
  // Compose -------------------------------------------------------------------
  'composer' => array(
    'photo' => array(
      'script' => array('_composePhoto.tpl', 'album'),
      'plugin' => 'Album_Plugin_Composer',
      //'auth' => array('album', 'create'),
    ),
  ),
  // Items ---------------------------------------------------------------------
  'items' => array(
    'album',
    'album_category',
    'album_photo',
    'photo'
  ),
  // Hooks ---------------------------------------------------------------------
  'hooks' => array(
    array(
      'event' => 'onStatistics',
      'resource' => 'Album_Plugin_Core'
    ),
    array(
      'event' => 'onUserPhotoUpload',
      'resource' => 'Album_Plugin_Core'
    ),
    array(
      'event' => 'onUserDeleteAfter',
      'resource' => 'Album_Plugin_Core'
    ),
    array(
      'event' => 'onActivityActionUpdateAfter',
      'resource' => 'Album_Plugin_Composer'
    )
  ),
  // Routes --------------------------------------------------------------------
  'routes' => array(
     'album_extended' => array(
      'route' => 'albums/:controller/:action/*',
      'defaults' => array(
        'module' => 'album',
        'controller' => 'index',
        'action' => 'index'
      ),
    ),
    'album_specific' => array(
      'route' => 'albums/:action/:album_id/:slug/*',
      'defaults' => array(
        'module' => 'album',
        'controller' => 'album',
        'action' => 'view',
        'slug' => ''
      ),
      'reqs' => array(
        'action' => '(compose-upload|delete|edit|editphotos|upload|view|order)',
      ),
    ),
    'album_general' => array(
      'route' => 'albums/:action/*',
      'defaults' => array(
        'module' => 'album',
        'controller' => 'index',
        'action' => 'browse'
      ),
      'reqs' => array(
        'action' => '(browse|create|list|manage|upload|upload-photo|browse-photos)',
      ),
    ),

    'album_photo_specific' => array(
      'route' => 'albums/photos/:action/:album_id/:photo_id/:slug/*',
      'defaults' => array(
        'module' => 'album',
        'controller' => 'photo',
        'action' => 'view',
        'slug' => ''
      ),
      'reqs' => array(
        'action' => '(view|rotate|crop|flip|edit|delete)',
      ),
    ),
  ),
) ?>
