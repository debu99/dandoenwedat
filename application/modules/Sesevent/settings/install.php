<?php
/**
 * SocialEngineSolutions
 *
 * @category   Application_Sesevent
 * @package    Sesevent
 * @copyright  Copyright 2015-2016 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: install.php 2016-07-26 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */
class Sesevent_Installer extends Engine_Package_Installer_Module {

  public function onEnable() {
    parent::onEnable();
  }

  public function onInstall() {

    $db = $this->getDb();
    //$this->_addContentMemberHome();
    $this->_addContentMemberProfile();
    $this->_addHashtagSearchContent();
    parent::onInstall();
  }

  protected function _addHashtagSearchContent() {

    $db = $this->getDb();
    $select = new Zend_Db_Select($db);

    // hashtag search page
    $select
        ->from('engine4_core_pages')
        ->where('name = ?', 'core_hashtag_index')
        ->limit(1);
    $pageId = $select->query()->fetchObject()->page_id;

    // Check if it's already been placed
    $select = new Zend_Db_Select($db);
    $select
        ->from('engine4_core_content')
        ->where('page_id = ?', $pageId)
        ->where('type = ?', 'widget')
        ->where('name = ?', 'sesevent.browse-events');
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
            $tabId = null;
        }
        
        $db->insert('engine4_core_content', array(
          'type' => 'widget',
          'name' => 'sesevent.browse-events',
          'page_id' => $pageId,
          'parent_content_id' => ($tabId ? $tabId : $middleId),
          'order' => 100,
          'params' => '{"enableTabs":["list","grid","advgrid","pinboard","masonry","map"],"openViewType":"advgrid","show_criteria":["verifiedLabel","listButton","favouriteButton","likeButton","socialSharing","joinedcount","location","buy","title","startenddate","category","host","listdescription","pinboarddescription","commentpinboard"],"limit_data":"12","pagging":"button","order":"mostSPliked","show_item_count":"1","list_title_truncation":"60","grid_title_truncation":"45","advgrid_title_truncation":"45","pinboard_title_truncation":"45","masonry_title_truncation":"45","list_description_truncation":"170","grid_description_truncation":"45","pinboard_description_truncation":"45","masonry_description_truncation":"45","height":"215","width":"300","photo_height":"290","photo_width":"296","info_height":"160","advgrid_height":"370","advgrid_width":"297","pinboard_width":"250","masonry_height":"350","title":"Events","nomobile":"0","name":"sesevent.browse-events"}',
        ));
    }
  }
  
//   protected function _addContentMemberHome() {
// 
//     $db = $this->getDb();
// 
//     $select = new Zend_Db_Select($db);
//     // Get page id
//     $page_id = $select
//             ->from('engine4_core_pages', 'page_id')
//             ->where('name = ?', 'user_index_home')
//             ->limit(1)
//             ->query()
//             ->fetchColumn(0);
//     // Check if it's already been placed
//     $select = new Zend_Db_Select($db);
//     $hasWidget = $select
//             ->from('engine4_core_content', new Zend_Db_Expr('TRUE'))
//             ->where('page_id = ?', $page_id)
//             ->where('type = ?', 'widget')
//             ->where('name = ?', 'sesevent.home-upcoming')
//             ->query()
//             ->fetchColumn();
//     if (!$hasWidget) {
//       $select = new Zend_Db_Select($db);
//       $container_id = $select
//               ->from('engine4_core_content', 'content_id')
//               ->where('page_id = ?', $page_id)
//               ->where('type = ?', 'container')
//               ->limit(1)
//               ->query()
//               ->fetchColumn();
// 
//       // middle_id (will always be there)
//       $select = new Zend_Db_Select($db);
//       $right_id = $select
//               ->from('engine4_core_content', 'content_id')
//               ->where('parent_content_id = ?', $container_id)
//               ->where('type = ?', 'container')
//               ->where('name = ?', 'right')
//               ->limit(1)
//               ->query()
//               ->fetchColumn();
// 
//       // insert
//       if ($right_id) {
//         $db->insert('engine4_core_content', array(
//             'page_id' => $page_id,
//             'type' => 'widget',
//             'name' => 'sesevent.home-upcoming',
//             'parent_content_id' => $right_id,
//             'order' => 1,
//             'params' => '{"title":"Upcoming Events","titleCount":true}',
//         ));
//       }
//     }
//   }

  protected function _addContentMemberProfile() {

    $db = $this->getDb();
    $select = new Zend_Db_Select($db);

    // Get page id
    $page_id = $select
            ->from('engine4_core_pages', 'page_id')
            ->where('name = ?', 'user_profile_index')
            ->limit(1)
            ->query()
            ->fetchColumn(0);

    // sesevent.profile-events
    // Check if it's already been placed
    $select = new Zend_Db_Select($db);
    $hasProfileEvents = $select
            ->from('engine4_core_content', new Zend_Db_Expr('TRUE'))
            ->where('page_id = ?', $page_id)
            ->where('type = ?', 'widget')
            ->where('name = ?', 'sesevent.profile-events')
            ->query()
            ->fetchColumn();

    // Add it
    if (!$hasProfileEvents) {

      // container_id (will always be there)
      $select = new Zend_Db_Select($db);
      $container_id = $select
              ->from('engine4_core_content', 'content_id')
              ->where('page_id = ?', $page_id)
              ->where('type = ?', 'container')
              ->limit(1)
              ->query()
              ->fetchColumn();

      // middle_id (will always be there)
      $select = new Zend_Db_Select($db);
      $middle_id = $select
              ->from('engine4_core_content', 'content_id')
              ->where('parent_content_id = ?', $container_id)
              ->where('type = ?', 'container')
              ->where('name = ?', 'middle')
              ->limit(1)
              ->query()
              ->fetchColumn();

      // tab_id (tab container) may not always be there
      $select = new Zend_Db_Select($db);
      $select
              ->from('engine4_core_content', 'content_id')
              ->where('type = ?', 'widget')
              ->where('name = ?', 'core.container-tabs')
              ->where('page_id = ?', $page_id)
              ->limit(1);
      $tab_id = $select->query()->fetchObject();
      if ($tab_id && @$tab_id->content_id) {
        $tab_id = $tab_id->content_id;
      } else {
        $tab_id = $middle_id;
      }

      // insert
      if ($tab_id) {
        $db->insert('engine4_core_content', array(
            'page_id' => $page_id,
            'type' => 'widget',
            'name' => 'sesevent.profile-events',
            'parent_content_id' => $tab_id,
            'order' => 8,
            'params' => '{"title":"Events","titleCount":true}',
        ));
      }
    }
  }
}
