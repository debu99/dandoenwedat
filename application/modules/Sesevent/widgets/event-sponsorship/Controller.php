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
class Sesevent_Widget_EventSponsorshipController extends Engine_Content_Widget_Abstract {
  public function indexAction() {
    // Get subject and check auth
		if (isset($_POST['params']))
    	$params = json_decode($_POST['params'], true);
		if(isset($params['subject_id']))
			$subject =  Engine_Api::_()->getItem('sesevent_event',$params['subject_id']);
		else
    	$subject = Engine_Api::_()->core()->getSubject('sesevent_event');
    if (!$subject) {
      return $this->setNoRender();
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
    
    if(!Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled('seseventsponsorship') || !Engine_Api::_()->getApi('settings', 'core')->getSetting('seseventsponsorship.pluginactivated')) {
			return $this->setNoRender();
		}
		
    if ($is_ajax)
      $this->getElement()->removeDecorator('Container');
    // Get paginator
    $this->view->paginator = $paginator = Engine_Api::_()->getDbTable('sponsorshipdetails', 'sesevent')->getSponsorDetails(array('event_id'=>$subject->getIdentity()));
    $paginator->setItemCountPerPage($limit_data);
    $paginator->setCurrentPageNumber($page);
    
		if(!$is_ajax && $paginator->getTotalItemCount() == 0){
			return $this->setNoRender();
		}
  }
}
