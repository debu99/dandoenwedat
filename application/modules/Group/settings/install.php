<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Group
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: install.php 9904 2013-02-14 02:39:21Z shaun $
 * @author     Steve
 */

/**
 * @category   Application_Extensions
 * @package    Group
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */
class Group_Installer extends Engine_Package_Installer_Module
{
  protected $_dropColumnsOnPreInstall = array(
    '4.9.0' => array(
      'engine4_group_groups' => array('like_count', 'comment_count'),
      'engine4_group_albums' => array('like_count'),
      'engine4_group_photos' => array('like_count')
    ),
    '4.9.3' => array(
      'engine4_group_groups' => array('view_privacy'),
    )
  );

  public function onInstall()
  {
    $this->_addUserProfileContent();
    $this->_addGroupProfilePage();
    $this->_addMobileGroupProfilePage();
    $this->_addGroupBrowsePage();

    $this->_addGroupCreatePage();
    $this->_addGroupManagePage();

    $this->_addPrivacyColumn();
    parent::onInstall();
  }

  protected function _addGroupManagePage()
  {
    $db = $this->getDb();

    // profile page
    $pageId = $db->select()
      ->from('engine4_core_pages', 'page_id')
      ->where('name = ?', 'group_index_manage')
      ->limit(1)
      ->query()
      ->fetchColumn();

    // insert if it doesn't exist yet
    if( !$pageId ) {
      // Insert page
      $db->insert('engine4_core_pages', array(
        'name' => 'group_index_manage',
        'displayname' => 'Group Manage Page',
        'title' => 'My Groups',
        'description' => 'This page lists a user\'s groups.',
        'custom' => 0,
      ));
      $pageId = $db->lastInsertId();

      // Insert top
      $db->insert('engine4_core_content', array(
        'type' => 'container',
        'name' => 'top',
        'page_id' => $pageId,
        'order' => 1,
      ));
      $topId = $db->lastInsertId();

      // Insert main
      $db->insert('engine4_core_content', array(
        'type' => 'container',
        'name' => 'main',
        'page_id' => $pageId,
        'order' => 2,
      ));
      $mainId = $db->lastInsertId();

      // Insert top-middle
      $db->insert('engine4_core_content', array(
        'type' => 'container',
        'name' => 'middle',
        'page_id' => $pageId,
        'parent_content_id' => $topId,
      ));
      $topMiddleId = $db->lastInsertId();

      // Insert main-middle
      $db->insert('engine4_core_content', array(
        'type' => 'container',
        'name' => 'middle',
        'page_id' => $pageId,
        'parent_content_id' => $mainId,
        'order' => 2,
      ));
      $mainMiddleId = $db->lastInsertId();

      // Insert main-right
      $db->insert('engine4_core_content', array(
        'type' => 'container',
        'name' => 'right',
        'page_id' => $pageId,
        'parent_content_id' => $mainId,
        'order' => 1,
      ));
      $mainRightId = $db->lastInsertId();


      // Insert menu
      $db->insert('engine4_core_content', array(
        'type' => 'widget',
        'name' => 'group.browse-menu',
        'page_id' => $pageId,
        'parent_content_id' => $topMiddleId,
        'order' => 1,
      ));

      // Insert content
      $db->insert('engine4_core_content', array(
        'type' => 'widget',
        'name' => 'core.content',
        'page_id' => $pageId,
        'parent_content_id' => $mainMiddleId,
        'order' => 1,
      ));

      // Insert search
      $db->insert('engine4_core_content', array(
        'type' => 'widget',
        'name' => 'group.browse-search',
        'page_id' => $pageId,
        'parent_content_id' => $mainRightId,
        'order' => 1,
      ));

      // Insert gutter menu
      $db->insert('engine4_core_content', array(
        'type' => 'widget',
        'name' => 'group.browse-menu-quick',
        'page_id' => $pageId,
        'parent_content_id' => $mainRightId,
        'order' => 2,
      ));
    }
  }

  protected function _addGroupCreatePage()
  {
    $db = $this->getDb();

    // profile page
    $pageId = $db->select()
      ->from('engine4_core_pages', 'page_id')
      ->where('name = ?', 'group_index_create')
      ->limit(1)
      ->query()
      ->fetchColumn();

    // insert if it doesn't exist yet
    if( !$pageId ) {
      // Insert page
      $db->insert('engine4_core_pages', array(
        'name' => 'group_index_create',
        'displayname' => 'Group Create Page',
        'title' => 'Group Create',
        'description' => 'This page allows users to create groups.',
        'custom' => 0,
      ));
      $pageId = $db->lastInsertId();

      // Insert top
      $db->insert('engine4_core_content', array(
        'type' => 'container',
        'name' => 'top',
        'page_id' => $pageId,
        'order' => 1,
      ));
      $topId = $db->lastInsertId();

      // Insert main
      $db->insert('engine4_core_content', array(
        'type' => 'container',
        'name' => 'main',
        'page_id' => $pageId,
        'order' => 2,
      ));
      $mainId = $db->lastInsertId();

      // Insert top-middle
      $db->insert('engine4_core_content', array(
        'type' => 'container',
        'name' => 'middle',
        'page_id' => $pageId,
        'parent_content_id' => $topId,
      ));
      $topMiddleId = $db->lastInsertId();

      // Insert main-middle
      $db->insert('engine4_core_content', array(
        'type' => 'container',
        'name' => 'middle',
        'page_id' => $pageId,
        'parent_content_id' => $mainId,
        'order' => 2,
      ));
      $mainMiddleId = $db->lastInsertId();

      // Insert menu
      $db->insert('engine4_core_content', array(
        'type' => 'widget',
        'name' => 'group.browse-menu',
        'page_id' => $pageId,
        'parent_content_id' => $topMiddleId,
        'order' => 1,
      ));

      // Insert content
      $db->insert('engine4_core_content', array(
        'type' => 'widget',
        'name' => 'core.content',
        'page_id' => $pageId,
        'parent_content_id' => $mainMiddleId,
        'order' => 1,
      ));

    }

  }


  protected function _addUserProfileContent()
  {
    //
    // install content areas
    //
    $db     = $this->getDb();
    $select = new Zend_Db_Select($db);

    //INSERT INTO `engine4_core_content` (`content_id`, `page_id`, `type`, `name`, `parent_content_id`, `order`, `params`) VALUES

    // profile page
    $select
      ->from('engine4_core_pages')
      ->where('name = ?', 'user_profile_index')
      ->limit(1);
    $pageId = $select->query()->fetchObject()->page_id;

    // group.profile-groups

    // Check if it's already been placed
    $select = new Zend_Db_Select($db);
    $select
      ->from('engine4_core_content')
      ->where('page_id = ?', $pageId)
      ->where('type = ?', 'widget')
      ->where('name = ?', 'group.profile-groups')
      ;
    $info = $select->query()->fetch();

    if( empty($info) ) {

      // container_id (will always be there)
      $select = new Zend_Db_Select($db);
      $select
        ->from('engine4_core_content')
        ->where('page_id = ?', $pageId)
        ->where('type = ?', 'container')
        ->limit(1);
      $containerId = $select->query()->fetchObject()->content_id;

      // middle_id (will always be there)
      $select = new Zend_Db_Select($db);
      $select
        ->from('engine4_core_content')
        ->where('parent_content_id = ?', $containerId)
        ->where('type = ?', 'container')
        ->where('name = ?', 'middle')
        ->limit(1);
      $middleId = $select->query()->fetchObject()->content_id;

      // tab_id (tab container) may not always be there
      $select
        ->reset('where')
        ->where('type = ?', 'widget')
        ->where('name = ?', 'core.container-tabs')
        ->where('page_id = ?', $pageId)
        ->limit(1);
      $tabId = $select->query()->fetchObject();
      if( $tabId && @$tabId->content_id ) {
          $tabId = $tabId->content_id;
      } else {
        $tabId = $middleId;
      }

      // tab on profile
      if( $tabId ) {
        $db->insert('engine4_core_content', array(
          'page_id' => $pageId,
          'type'    => 'widget',
          'name'    => 'group.profile-groups',
          'parent_content_id' => $tabId,
          'order'   => 9,
          'params'  => '{"title":"Groups","titleCount":true}',
        ));
      }
    }
  }

  protected function _addGroupProfilePage()
  {
    $db     = $this->getDb();
    $select = new Zend_Db_Select($db);

    //
    // Group main page
    //
    // page


    // Check if it's already been placed
    $select = new Zend_Db_Select($db);
    $pageId = $select
      ->from('engine4_core_pages', 'page_id')
      ->where('name = ?', 'group_profile_index')
      ->limit(1)
      ->query()
      ->fetchColumn()
      ;

    if( empty($pageId) ) {
      $db->insert('engine4_core_pages', array(
        'name' => 'group_profile_index',
        'displayname' => 'Group Profile',
        'title' => 'Group Profile',
        'description' => 'This is the profile for an group.',
        'custom' => 0,
        'provides' => 'subject=group',
      ));
      $pageId = $db->lastInsertId('engine4_core_pages');

      // containers
      $db->insert('engine4_core_content', array(
        'page_id' => $pageId,
        'type' => 'container',
        'name' => 'main',
        'parent_content_id' => null,
        'order' => 1,
        'params' => '',
      ));
      $containerId = $db->lastInsertId('engine4_core_content');

      $db->insert('engine4_core_content', array(
        'page_id' => $pageId,
        'type' => 'container',
        'name' => 'middle',
        'parent_content_id' => $containerId,
        'order' => 3,
        'params' => '',
      ));
      $middleId = $db->lastInsertId('engine4_core_content');

      $db->insert('engine4_core_content', array(
        'page_id' => $pageId,
        'type' => 'container',
        'name' => 'left',
        'parent_content_id' => $containerId,
        'order' => 1,
        'params' => '',
      ));
      $leftId = $db->lastInsertId('engine4_core_content');

      // middle column
      $db->insert('engine4_core_content', array(
        'page_id' => $pageId,
        'type' => 'widget',
        'name' => 'core.container-tabs',
        'parent_content_id' => $middleId,
        'order' => 2,
        'params' => '{"max":"6"}',
      ));
      $tabId = $db->lastInsertId('engine4_core_content');

      // left column
      $db->insert('engine4_core_content', array(
        'page_id' => $pageId,
        'type' => 'widget',
        'name' => 'group.profile-info',
        'parent_content_id' => $leftId,
        'order' => 3,
        'params' => '',
      ));

      // tabs
      $db->insert('engine4_core_content', array(
        'page_id' => $pageId,
        'type' => 'widget',
        'name' => 'activity.feed',
        'parent_content_id' => $tabId,
        'order' => 1,
        'params' => '{"title":"Updates"}',
      ));
      $db->insert('engine4_core_content', array(
        'page_id' => $pageId,
        'type' => 'widget',
        'name' => 'group.profile-members',
        'parent_content_id' => $tabId,
        'order' => 2,
        'params' => '{"title":"Members","titleCount":true}',
      ));
      $db->insert('engine4_core_content', array(
        'page_id' => $pageId,
        'type' => 'widget',
        'name' => 'group.profile-photos',
        'parent_content_id' => $tabId,
        'order' => 3,
        'params' => '{"title":"Photos","titleCount":true}',
      ));
      $db->insert('engine4_core_content', array(
        'page_id' => $pageId,
        'type' => 'widget',
        'name' => 'group.profile-discussions',
        'parent_content_id' => $tabId,
        'order' => 4,
        'params' => '{"title":"Discussions","titleCount":true}',
      ));
      $db->insert('engine4_core_content', array(
        'page_id' => $pageId,
        'type' => 'widget',
        'name' => 'core.profile-links',
        'parent_content_id' => $tabId,
        'order' => 5,
        'params' => '{"title":"Links","titleCount":true}',
      ));
      $db->insert('engine4_core_content', array(
        'page_id' => $pageId,
        'type' => 'widget',
        'name' => 'group.profile-events',
        'parent_content_id' => $tabId,
        'order' => 6,
        'params' => '{"title":"Events","titleCount":true}',
      ));
      $db->insert('engine4_core_content', array(
        'page_id' => $pageId,
        'type' => 'widget',
        'name' => 'group.profile-blogs',
        'parent_content_id' => $tabId,
        'order' => 7,
        'params' => '{"title":"Blogs","titleCount":true}',
      ));
      $db->insert('engine4_core_content', array(
        'page_id' => $pageId,
        'type' => 'widget',
        'name' => 'group.profile-polls',
        'parent_content_id' => $tabId,
        'order' => 7,
        'params' => '{"title":"Polls","titleCount":true}',
      ));
      $db->insert('engine4_core_content', array(
        'page_id' => $pageId,
        'type' => 'widget',
        'name' => 'group.profile-videos',
        'parent_content_id' => $tabId,
        'order' => 7,
        'params' => '{"title":"Videos","titleCount":true}',
      ));
    } else {
      //Poll, Blogs and Video
      
      $select = new Zend_Db_Select($db);
      $select->from('engine4_core_content')
            ->where('type = ?', 'widget')
            ->where('name = ?', 'core.container-tabs')
            ->where('page_id = ?', $pageId)
            ->limit(1);
      $tabId = $select->query()->fetchObject();
      if( $tabId && @$tabId->content_id ) {
        $tabId = $tabId->content_id;

        $hasBlog = $db->select()
          ->from('engine4_core_content', 'content_id')
          ->where('page_id = ?', $pageId)
          ->where('name = ?', 'group.profile-blogs')
          ->limit(1)
          ->query()
          ->fetchColumn();
        if (empty($hasBlog)) {
          $db->insert('engine4_core_content', array(
            'page_id' => $pageId,
            'type' => 'widget',
            'name' => 'group.profile-blogs',
            'parent_content_id' => $tabId,
            'order' => 80,
            'params' => '{"title":"Blogs","titleCount":true}',
          ));
        }
        
        $hasPoll = $db->select()
          ->from('engine4_core_content', 'content_id')
          ->where('page_id = ?', $pageId)
          ->where('name = ?', 'group.profile-polls')
          ->limit(1)
          ->query()
          ->fetchColumn();
        if (empty($hasPoll)) {
          $db->insert('engine4_core_content', array(
            'page_id' => $pageId,
            'type' => 'widget',
            'name' => 'group.profile-polls',
            'parent_content_id' => $tabId,
            'order' => 81,
            'params' => '{"title":"Polls","titleCount":true}',
          ));
        }
        
        $hasVideo = $db->select()
          ->from('engine4_core_content', 'content_id')
          ->where('page_id = ?', $pageId)
          ->where('name = ?', 'group.profile-videos')
          ->limit(1)
          ->query()
          ->fetchColumn();
        if (empty($hasVideo)) {
          $db->insert('engine4_core_content', array(
            'page_id' => $pageId,
            'type' => 'widget',
            'name' => 'group.profile-videos',
            'parent_content_id' => $tabId,
            'order' => 82,
            'params' => '{"title":"Videos","titleCount":true}',
          ));
        }
      }
    }
    
    $this->_addContentCoverPhoto($pageId);
  }

  protected function _addMobileGroupProfilePage()
  {
    $db     = $this->getDb();
    $select = new Zend_Db_Select($db);

    //
    // Mobile Group Profile
    //
    // page


    // Check if it's already been placed
    $select = new Zend_Db_Select($db);
    $select
      ->from('engine4_core_pages')
      ->where('name = ?', 'mobi_group_profile')
      ->limit(1);
      ;
    $info = $select->query()->fetch();

    if( empty($info) ) {
      $db->insert('engine4_core_pages', array(
        'name' => 'mobi_group_profile',
        'displayname' => 'Mobile Group Profile',
        'title' => 'Mobile Group Profile',
        'description' => 'This is the mobile verison of a group profile.',
        'custom' => 0
      ));
      $pageId = $db->lastInsertId('engine4_core_pages');

      // containers
      $db->insert('engine4_core_content', array(
        'page_id' => $pageId,
        'type' => 'container',
        'name' => 'main',
        'parent_content_id' => null,
        'order' => 1,
        'params' => '',
      ));
      $containerId = $db->lastInsertId('engine4_core_content');

      $db->insert('engine4_core_content', array(
        'page_id' => $pageId,
        'type' => 'container',
        'name' => 'middle',
        'parent_content_id' => $containerId,
        'order' => 2,
        'params' => '',
      ));
      $middleId = $db->lastInsertId('engine4_core_content');

      $db->insert('engine4_core_content', array(
        'page_id' => $pageId,
        'type' => 'widget',
        'name' => 'group.profile-status',
        'parent_content_id' => $middleId,
        'order' => 3,
        'params' => '',
      ));
      $db->insert('engine4_core_content', array(
        'page_id' => $pageId,
        'type' => 'widget',
        'name' => 'group.profile-photo',
        'parent_content_id' => $middleId,
        'order' => 4,
        'params' => '',
      ));
      $db->insert('engine4_core_content', array(
        'page_id' => $pageId,
        'type' => 'widget',
        'name' => 'group.profile-info',
        'parent_content_id' => $middleId,
        'order' => 5,
        'params' => '',
      ));
      $db->insert('engine4_core_content', array(
        'page_id' => $pageId,
        'type' => 'widget',
        'name' => 'core.container-tabs',
        'parent_content_id' => $middleId,
        'order' => 6,
        'params' => '{"max":6}',
      ));
      $tabId = $db->lastInsertId('engine4_core_content');

      $db->insert('engine4_core_content', array(
        'page_id' => $pageId,
        'type' => 'widget',
        'name' => 'activity.feed',
        'parent_content_id' => $tabId,
        'order' => 7,
        'params' => '{"title":"What\'s New"}',
      ));
      $db->insert('engine4_core_content', array(
        'page_id' => $pageId,
        'type' => 'widget',
        'name' => 'group.profile-members',
        'parent_content_id' => $tabId,
        'order' => 8,
        'params' => '{"title":"Members","titleCount":true}',
      ));
    }
  }

  protected function _addGroupBrowsePage()
  {
    $db = $this->getDb();

    // profile page
    $pageId = $db->select()
      ->from('engine4_core_pages', 'page_id')
      ->where('name = ?', 'group_index_browse')
      ->limit(1)
      ->query()
      ->fetchColumn();

    // insert if it doesn't exist yet
    if( !$pageId ) {
      // Insert page
      $db->insert('engine4_core_pages', array(
        'name' => 'group_index_browse',
        'displayname' => 'Group Browse Page',
        'title' => 'Group Browse',
        'description' => 'This page lists groups.',
        'custom' => 0,
      ));
      $pageId = $db->lastInsertId();

      // Insert top
      $db->insert('engine4_core_content', array(
        'type' => 'container',
        'name' => 'top',
        'page_id' => $pageId,
        'order' => 1,
      ));
      $topId = $db->lastInsertId();

      // Insert main
      $db->insert('engine4_core_content', array(
        'type' => 'container',
        'name' => 'main',
        'page_id' => $pageId,
        'order' => 2,
      ));
      $mainId = $db->lastInsertId();

      // Insert top-middle
      $db->insert('engine4_core_content', array(
        'type' => 'container',
        'name' => 'middle',
        'page_id' => $pageId,
        'parent_content_id' => $topId,
      ));
      $topMiddleId = $db->lastInsertId();

      // Insert main-middle
      $db->insert('engine4_core_content', array(
        'type' => 'container',
        'name' => 'middle',
        'page_id' => $pageId,
        'parent_content_id' => $mainId,
        'order' => 2,
      ));
      $mainMiddleId = $db->lastInsertId();

      // Insert main-right
      $db->insert('engine4_core_content', array(
        'type' => 'container',
        'name' => 'right',
        'page_id' => $pageId,
        'parent_content_id' => $mainId,
        'order' => 1,
      ));
      $mainRightId = $db->lastInsertId();

      // Insert banner
     $db->insert('engine4_core_banners', array(
        'name' => 'group',
        'module' => 'group',
        'title' => 'Get Together in Groups',
        'body' => 'Create and join interest based Groups. Bring people together.',
        'photo_id' => 0,
        'params' => '{"label":"Create New Group","route":"group_general","routeParams":{"action":"create"}}',
        'custom' => 0
      ));
      $bannerId = $db->lastInsertId();

      if( $bannerId ) {
        $db->insert('engine4_core_content', array(
          'type' => 'widget',
          'name' => 'core.banner',
          'page_id' => $pageId,
          'parent_content_id' => $topMiddleId,
          'params' => '{"title":"","name":"core.banner","banner_id":"'. $bannerId .'","nomobile":"0"}',
          'order' => 1,
        ));
      }

      // Insert menu
      $db->insert('engine4_core_content', array(
        'type' => 'widget',
        'name' => 'group.browse-menu',
        'page_id' => $pageId,
        'parent_content_id' => $topMiddleId,
        'order' => 2,
      ));

      // Insert content
      $db->insert('engine4_core_content', array(
        'type' => 'widget',
        'name' => 'core.content',
        'page_id' => $pageId,
        'parent_content_id' => $mainMiddleId,
        'order' => 1,
      ));

      // Insert search
      $db->insert('engine4_core_content', array(
        'type' => 'widget',
        'name' => 'group.browse-search',
        'page_id' => $pageId,
        'parent_content_id' => $mainRightId,
        'order' => 1,
      ));

      // Insert gutter menu
      $db->insert('engine4_core_content', array(
        'type' => 'widget',
        'name' => 'group.browse-menu-quick',
        'page_id' => $pageId,
        'parent_content_id' => $mainRightId,
        'order' => 2,
      ));

      // Insert list categories
      $db->insert('engine4_core_content', array(
        'type' => 'widget',
        'name' => 'group.list-categories',
        'page_id' => $pageId,
        'parent_content_id' => $mainRightId,
        'order' => 3,
      ));
    }
  }

  // Create and populate `view_privacy` column
  protected function _addPrivacyColumn()
  {
    if( $this->_databaseOperationType != 'upgrade' || version_compare('4.9.3', $this->_currentVersion, '<=') ) {
      return $this;
    }

    $db = $this->getDb();
    $sql = "ALTER TABLE `engine4_group_groups` ADD `view_privacy` VARCHAR(24) NOT NULL DEFAULT 'everyone' AFTER `view_count`";
    try {
     $db->query($sql);
    } catch( Exception $e ) {
      return $this->_error('Query failed with error: ' . $e->getMessage());
    }

    // populate `view_privacy` column
    $select = new Zend_Db_Select($db);

    try {
    $select
      ->from('engine4_authorization_allow', array('resource_id' => 'resource_id', 'privacy_values' => new Zend_Db_Expr('GROUP_CONCAT(DISTINCT role)')))
      ->where('resource_type = ?', 'group')
      ->where('action = ?', 'view')
      ->group('resource_id');

      $privacyList = $select->query()->fetchAll();
    } catch( Exception $e ) {
      return $this->_error('Query failed with error: ' . $e->getMessage());
    }

    foreach( $privacyList as $privacy ) {
      $viewPrivacy = 'member';
      $privacyVal = explode(",", $privacy['privacy_values']);
      if( in_array('everyone', $privacyVal) ) {
        $viewPrivacy = 'everyone';
      } elseif( in_array('registered', $privacyVal) ) {
        $viewPrivacy = 'registered';
      } elseif( in_array('member', $privacyVal) ) {
        $viewPrivacy = 'member';
      }

      $db->update('engine4_group_groups', array(
            'view_privacy' => $viewPrivacy,
            ), array(
            'group_id = ?' => $privacy['resource_id'],
          ));
    }

    return $this;
  }

  private function _addContentCoverPhoto($pageId)
  {
    if (empty($pageId)) {
      return;
    }

    $db = $this->getDb();
    $hasCover = $db->select()
      ->from('engine4_core_content', 'content_id')
      ->where('page_id = ?', $pageId)
      ->where('name = ?', 'group.cover-photo')
      ->limit(1)
      ->query()
      ->fetchColumn();
    if (!empty($hasCover)) {
      return;
    }

    $hasTop = $db->select()
      ->from('engine4_core_content', 'content_id')
      ->where('name = ?', 'top')
      ->where('page_id = ?', $pageId)
      ->where('type = ?', 'container')
      ->limit(1)
      ->query()
      ->fetchColumn();
    if (empty($hasTop)) {
      // top-containers
      $db->insert('engine4_core_content', array(
        'type' => 'container',
        'name' => 'top',
        'page_id' => $pageId,
        'order' => 0
      ));
      $topId = $db->lastInsertId();

      // Insert top-middle
      $db->insert('engine4_core_content', array(
        'type' => 'container',
        'name' => 'middle',
        'page_id' => $pageId,
        'parent_content_id' => $topId,
      ));
      $topMiddleId = $db->lastInsertId();
    } else {
      $topMiddleId = $db->select()
        ->from('engine4_core_content', 'content_id')
        ->where('name = ?', 'middle')
        ->where('page_id = ?', $pageId)
        ->where('type = ?', 'container')
        ->where('parent_content_id = ?', $hasTop)
        ->limit(1)
        ->query()
        ->fetchColumn();
    }

    if (!empty($topMiddleId)) {
      $db->insert('engine4_core_content', array(
        'type' => 'widget',
        'name' => 'group.cover-photo',
        'page_id' => $pageId,
        'parent_content_id' => $topMiddleId,
      ));
    }
  }
}
