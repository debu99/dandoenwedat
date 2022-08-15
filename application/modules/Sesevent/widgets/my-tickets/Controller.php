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
class Sesevent_Widget_MyTicketsController extends Engine_Content_Widget_Abstract {
  public function indexAction() {
		$view_type = $this->_getParam('view_type','current');
		$this->view->is_ajax = $is_ajax = $this->_getParam('is_ajax',false);
		 $viewer = Engine_Api::_()->user()->getViewer();
		if(!$viewer)
			return $this->setNoRender();
		if($view_type == 'current'){
   		$this->view->paginator = $paginator =  Engine_Api::_()->getDbTable('orders', 'sesevent')->getOrders(array('view_type' => $view_type,'viewer_id'=>$viewer->getIdentity(),'limit'=>10000,'page'=>1));
			//get current order count 
			$this->view->currentOrderCount = $paginator->getTotalItemCount();
			//get past order count 
			if(!$is_ajax){
				$this->view->pastOrderCount = Engine_Api::_()->getDbTable('orders', 'sesevent')->getOrders(array('view_type' => 'past','viewer_id'=>$viewer->getIdentity()))->getTotalItemCount();
			}
		}else if($view_type == 'past'){
			$this->view->paginator = $paginator = Engine_Api::_()->getDbTable('orders', 'sesevent')->getOrders(array('view_type' => $view_type,'viewer_id'=>$viewer->getIdentity(),'limit'=>10000,'page'=>1));	
			//get past order count 
			$this->view->pastOrderCount = $paginator->getTotalItemCount();
			//get current order count 
			if(!$is_ajax){
				$this->view->currentOrderCount = Engine_Api::_()->getDbTable('orders', 'sesevent')->getOrders(array('view_type' => 'current','viewer_id'=>$viewer->getIdentity()))->getTotalItemCount();
			}			
		}
		if ($is_ajax) {
			$this->getElement()->removeDecorator('Container');
		} 
  }
}