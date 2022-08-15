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
class Sesevent_Widget_EventTicketBuyerController extends Engine_Content_Widget_Abstract
{
  public function indexAction()
  {
	 if(Engine_Api::_()->core()->hasSubject('sesevent_event'))
	 	 $subject = Engine_Api::_()->core()->getSubject('sesevent_event');
		else
			 return $this->setNoRender();
    // Get paginator
  	$this->view->event_id = $param['id'] = $subject->getIdentity();
		$param['type'] = 'sesevent_event';
		$this->view->totalTicketSold  = $totalTicketSold = Engine_Api::_()->getDbtable('orders', 'sesevent')->getTotalTicketSoldCount(array('event_id'=>$subject->getIdentity(),'state'=>'complete'));
		//don't render widget if no ticket sold.
		
	 if(!Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled('seseventticket') || !Engine_Api::_()->getApi('settings', 'core')->getSetting('seseventticket.pluginactivated')){
			return $this->setNoRender();
	 }
		
		if($totalTicketSold <= 0)
			return $this->setNoRender();
    $this->view->paginator = $paginator =Engine_Api::_()->getDbtable('orders', 'sesevent')->getOrders(array('event_id'=>$subject->getIdentity(),'groupBy'=>'owner_id'));		
		$this->view->data_show = $limit_data = $this->_getParam('limitdata','10');
	  
    // Set item count per page and current page number
    $paginator->setItemCountPerPage($limit_data);
    $paginator->setCurrentPageNumber(1);
    // Do not render if nothing to show
    if( $paginator->getTotalItemCount() <= 0 ) {
      return $this->setNoRender();
    }
  }
}