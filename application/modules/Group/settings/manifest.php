<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Group
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @author     John
 */
return array(
  // Package -------------------------------------------------------------------
  'package' => array(
    'type' => 'module',
    'name' => 'group',
    'version' => '5.0.1',
    'revision' => '$Revision: 10271 $',
    'path' => 'application/modules/Group',
    'repository' => 'socialengine.com',
    'title' => 'Groups',
    'description' => 'Groups',
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
      'path' => 'application/modules/Group/settings/install.php',
      'class' => 'Group_Installer',
    ),
    'directories' => array(
      'application/modules/Group',
    ),
    'files' => array(
      'application/languages/en/group.csv',
    ),
  ),
  // Hooks ---------------------------------------------------------------------
  'hooks' => array(
    array(
      'event' => 'onStatistics',
      'resource' => 'Group_Plugin_Core'
    ),
    array(
      'event' => 'onUserDeleteBefore',
      'resource' => 'Group_Plugin_Core',
    ),
    array(
      'event' => 'getActivity',
      'resource' => 'Group_Plugin_Core',
    ),
    array(
      'event' => 'addActivity',
      'resource' => 'Group_Plugin_Core',
    ),
  ),
  // Items ---------------------------------------------------------------------
  'items' => array(
    'group',
    'group_album',
    'group_category',
    'group_list',
    'group_list_item',
    'group_photo',
    'group_post',
    'group_topic',
  ),
  // Routes --------------------------------------------------------------------
  'routes' => array(
    'group_extended' => array(
      'route' => 'groups/:controller/:action/*',
      'defaults' => array(
        'module' => 'group',
        'controller' => 'index',
        'action' => 'index',
      ),
      'reqs' => array(
        'controller' => '\D+',
        'action' => '\D+',
      )
    ),
    'group_general' => array(
      'route' => 'groups/:action/*',
      'defaults' => array(
        'module' => 'group',
        'controller' => 'index',
        'action' => 'browse',
      ),
      'reqs' => array(
        'action' => '(browse|create|list|manage)',
      )
    ),
    'group_specific' => array(
      'route' => 'groups/:action/:group_id/*',
      'defaults' => array(
        'module' => 'group',
        'controller' => 'group',
        'action' => 'index',
      ),
      'reqs' => array(
        'action' => '(edit|delete|join|leave|cancel|accept|invite|style)',
        'group_id' => '\d+',
      )
    ),
    'group_profile' => array(
      'route' => 'group/:id/:slug/*',
      'defaults' => array(
        'module' => 'group',
        'controller' => 'profile',
        'action' => 'index',
        'slug' => '',
      ),
      'reqs' => array(
        'id' => '\d+',
      )
    ),
    'group_topic' => array(
      'route' => 'group/:controller/:action/:group_id/:topic_id/:slug/*',
      'defaults' => array(
        'module' => 'group',
        'controller' => 'index',
        'action' => 'index',
        'slug' => ''
      ),
      'reqs' => array(
        'controller' => '\D+',
        'action' => '\D+',
      )
    ),
    'group_coverphoto' => array(
      'route' => 'group/coverphoto/:action/*',
      'defaults' => array(
        'module' => 'group',
        'controller' => 'coverphoto',
      ),
      'reqs' => array(
          'action' => '(get-cover-photo|get-main-photo|reset-cover-photo-position|upload-cover-photo|choose-from-albums|remove-cover-photo)'
      ),
    ),
  )
) ?>
