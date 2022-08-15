<?php

/**
 * SocialEngineSolutions
 *
 * @category   Application_Sesadvancedactivity
 * @package    Sesadvancedactivity
 * @copyright  Copyright 2016-2017 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: Controller.php  2017-01-12 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */

class Sesadvancedactivity_Widget_WelcometabSectionsController extends Engine_Content_Widget_Abstract {

  public function indexAction() {
    
    $this->view->displaysections = $displaysections = $this->_getParam('displaysections', 1);
    $settings = Engine_Api::_()->getApi('settings', 'core');
    $this->view->viewer = $viewer = Engine_Api::_()->user()->getViewer();
    $this->view->findfriends = $settings->getSetting('sesadvancedactivity.findfriends', 1);
    if($this->view->findfriends) {
      $this->view->friendsCount = $viewer->membership()->getMemberCount($viewer);
      $this->view->searchnumfriend = $settings->getSetting('sesadvancedactivity.searchnumfriend', 3);
    }
    $this->view->profilephotoupload = $settings->getSetting('sesadvancedactivity.profilephotoupload', 1);
    $this->view->canphotoshow = $settings->getSetting('sesadvancedactivity.canphotoshow', 1);
    $this->view->friendrequest = $settings->getSetting('sesadvancedactivity.friendrequest', 1);
    $this->view->tabsettings = $settings->getSetting('sesadvancedactivity.tabsettings', "Welcome to [site_title], [user_name]");
    $this->view->sitetitle = $settings->getSetting('core.general.site.title', "My Community");
    if($this->view->friendrequest ) {
      $countfriends = $settings->getSetting('sesadvancedactivity.countfriends', 3);
      $enable_type = array();
      foreach (Engine_Api::_()->getDbtable('NotificationTypes', 'activity')->getNotificationTypes() as $type) {
        $enable_type[] = $type->type;
      }
        $page = $this->_getParam('page', 1);
      $select = Engine_Api::_()->getDbtable('notifications', 'activity')->select()
                              ->where('user_id = ?', $viewer->getIdentity())
                              ->where('type IN(?)', $enable_type)
                              ->where('type = ?', 'friend_request')
                              ->where('mitigated = ?', 0)
                            //  ->limit($countfriends)
                              ->order('date DESC');
      $this->view->friendRequests = $friendRequests = Zend_Paginator::factory($select);
      $friendRequests->setItemCountPerPage($countfriends);
      $friendRequests->setCurrentPageNumber( $page );

    }
    if($this->view->profilephotoupload && $displaysections == 1 && Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled('sesalbum')) {
      // Don't render this if not authorized
      $user_id = $viewer->getIdentity();
      $this->view->widgetPlaced = 'home';
      $this->view->canEdit = true;
      $this->view->user_id = $user_id;
    }
  }
}