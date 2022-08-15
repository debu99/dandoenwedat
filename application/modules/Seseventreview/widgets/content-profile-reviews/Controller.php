<?php
/**
 * SocialEngineSolutions
 *
 * @category   Application_Seseventreview
 * @package    Seseventreview
 * @copyright  Copyright 2015-2016 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: Controller.php 2016-07-26 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */
class Seseventreview_Widget_ContentProfileReviewsController extends Engine_Content_Widget_Abstract {
  protected $_childCount;
  public function indexAction() {
		
	$module_name = Zend_Controller_Front::getInstance()->getRequest()->getModuleName(); 
	$subject = Engine_Api::_()->core()->getSubject();
	$currentTime = time();
	//don't render widget if event ends
	if(strtotime($subject->starttime) > ($currentTime))
		return $this->setNoRender();
	$viewer = Engine_Api::_()->user()->getViewer();
	if(!Engine_Api::_()->getApi('settings', 'core')->getSetting('seseventreview.allow.review', 1))
		return $this->setNoRender();
	if(Engine_Api::_()->getApi('settings', 'core')->getSetting('seseventreview.allow.owner', 1)){
		$allowedCreate = true;	
	} else{
		if($subject->user_id == $viewer->getIdentity())	
			$allowedCreate = false;
		else
			$allowedCreate = true;
	}

    $this->view->allowedCreate = $allowedCreate;
    if (!Engine_Api::_()->core()->hasSubject('sesevent_event'))
      return $this->setNoRender();
		 $this->view->stats = isset($params['stats']) ? $params['stats'] : $this->_getParam('stats', array('featured', 'sponsored','likeCount', 'commentCount', 'viewCount', 'title', 'postedBy', 'pros', 'cons', 'description', 'creationDate', 'recommended','parameter','rating','customfields'));
    if (!Engine_Api::_()->authorization()->getPermission($viewer, 'eventreview', 'view'))
      return $this->setNoRender();
    $this->view->isReview = Engine_Api::_()->getDbtable('eventreviews', 'seseventreview')->isReview(array('content_id' => $subject->getIdentity(), 'content_type' =>$subject->getType(), 'module_name' => $module_name, 'column_name' => 'review_id'));
		$this->view->cancreate = Engine_Api::_()->authorization()->getPermission($viewer, 'eventreview', 'create');
    $table = Engine_Api::_()->getItemTable('eventreview');

    $select = $table->select()
            ->where('content_id = ?', $subject->getIdentity())
            ->where('module_name = ?', $module_name)
            ->where('content_type = ?', $subject->getType())
            ->order('creation_date DESC');

    $this->view->paginator = $paginator = Zend_Paginator::factory($select);
    //Set item count per page and current page number
    $paginator->setItemCountPerPage(10);
    $paginator->setCurrentPageNumber($this->_getParam('page', 1));

		if(!($this->view->allowedCreate && $this->view->cancreate && $viewer->getIdentity() && Engine_Api::_()->getApi('settings', 'core')->getSetting('seseventreview.allow.review', 1) ) &&  $paginator->getTotalItemCount() == 0 )
			return $this->setNoRender();
		
		//Add count to title if configured
    if ($paginator->getTotalItemCount() > 0)
      $this->_childCount = $paginator->getTotalItemCount();
			
  }
  public function getChildCount() {
    return $this->_childCount;
  }
}
