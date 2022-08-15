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
class Sesevent_Widget_SponsorshipViewPageController extends Engine_Content_Widget_Abstract {
  public function indexAction() {
    // Get subject and check auth
		if (isset($_POST['params']))
    	$params = json_decode($_POST['params'], true);
		$sponsorship_id = Zend_Controller_Front::getInstance()->getRequest()->getParam('id',0);
		$sponsorship_id = $sponsorship_id ? $sponsorship_id : ($params['subject_id'] ? $params['subject_id'] : 0);
		if(!$sponsorship_id)
			 return $this->setNoRender();
		$this->view->subject = $subject =  Engine_Api::_()->getItem('sesevent_sponsorship',$sponsorship_id);
    if (!$subject) {
      return $this->setNoRender();
    }
    
    if(!Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled('seseventsponsorship') || !Engine_Api::_()->getApi('settings', 'core')->getSetting('seseventsponsorship.pluginactivated')){
			return $this->setNoRender();
		}
		
		$this->view->can_buy = false;
		if(!$is_ajax){
			 $this->view->event = Engine_Api::_()->core()->getSubject();
			 $viewer_id =  Engine_Api::_()->user()->getViewer()->getIdentity();
			 if($viewer_id != 0){
			 $can_buy = Engine_Api::_()->getDbTable('sponsorships', 'sesevent')->getSponsorship(array('user_id'=>$viewer_id,'event_id'=>$subject->event_id,'sponsorship_id'=>$subject->getIdentity(),'sponsorship'=>true));	
			 $this->view->can_buy = count($can_buy);
			 }else
			 	$this->view->can_buy = false;
		}
    $this->view->subject = $subject;
		$this->view->identityForWidget = isset($_POST['identity']) ? $_POST['identity'] : '';
    $this->view->loadOptionData = $loadOptionData = isset($params['pagging']) ? $params['pagging'] : $this->_getParam('pagging', 'auto_load');
		$this->view->is_ajax = $is_ajax = isset($_POST['is_ajax']) ? true : false;
    $page = isset($_POST['page']) ? $_POST['page'] : 1;
		$limit_data = isset($params['limit_data']) ? $params['limit_data'] : $this->_getParam('limit_data', '10');
		$this->view->details = $details = isset($params['details']) ? $params['details'] : $this->_getParam('details', array('title','description','logo'));
 	  $this->view->params = array('pagging' => $loadOptionData, 'limit_data' => $limit_data,  'details' => $details,'subject_id'=>$subject->getIdentity());
    $this->view->page = $page;
    
    if ($is_ajax)
      $this->getElement()->removeDecorator('Container');
    // Get paginator
    $this->view->paginator = $paginator = Engine_Api::_()->getDbTable('sponsorshipdetails', 'sesevent')->getSponsorDetails(array('sponsorship_id'=>$subject->getIdentity(),'event_id'=>$subject->event_id));
    $paginator->setItemCountPerPage($limit_data);
    $paginator->setCurrentPageNumber($page);
  }
}
