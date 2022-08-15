<?php
/**
 * SocialEngineSolutions
 *
 * @category   Application_Sesevent
 * @package    Sesevent
 * @copyright  Copyright 2015-2016 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: Controller.php 2016-07-26 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */
class Sesevent_Widget_ProfileNonsitehostController extends Engine_Content_Widget_Abstract {

  public function indexAction() {
  
    $this->view->viewer = $viewer = Engine_Api::_()->user()->getViewer();
    $this->view->viewer_id =  $viewer->getIdentity();
    
    $host_id = Zend_Controller_Front::getInstance()->getRequest()->getParam('host_id'); 
    if (empty($host_id))
      return $this->setNoRender();

    $this->view->host = $host = Engine_Api::_()->getItem('sesevent_host', $host_id);
    if (!$host)
      return $this->setNoRender();
      
    $this->view->type = 'sesevent_host';
    $this->view->id = $host->host_id;
    $this->view->isFollow = Engine_Api::_()->getDbTable('follows', 'sesevent')->isFollow(array('resource_type' => $this->view->type, 'resource_id' => $this->view->id));
    $this->view->allowFollow = Engine_Api::_()->getApi('settings', 'core')->getSetting('sesevent.followeventowner', 1);
    
    $this->view->socialshare_enable_plusicon = $this->_getParam('socialshare_enable_plusicon', 1);
    $this->view->socialshare_icon_limit = $this->_getParam('socialshare_icon_limit', 2);
    
    $select = Engine_Api::_()->getDbtable('follows', 'sesevent')->getFollowSelect($host);
    $results = $select->query()->fetchAll();
    $this->view->followCount = count($results);
    
    $this->view->hostEventCount = Engine_Api::_()->getDbtable('events', 'sesevent')->getHostEventCounts(array('host_id' => $host->host_id, 'type' => $host->type));


    $this->view->infoshow = $this->_getParam('infoshow', array('displayname', 'description', 'facebook', 'twitter', 'linkdin', 'googleplus', 'detaildescription'));

    $this->view->descriptionText = $this->_getParam('descriptionText', '');
  }

}
