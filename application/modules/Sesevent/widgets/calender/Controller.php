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
class Sesevent_Widget_CalenderController extends Engine_Content_Widget_Abstract {
  public function indexAction(){
		$this->view->is_ajax = $is_ajax = isset($_POST['is_ajax']) ? true : false;
		if(isset($_POST['month']) && isset($_POST['year'])){
			if($_POST['type'] == 'prev'){
				$dateCheck = date('Y-m-d',strtotime('last day of previous month',strtotime(date($_POST['year'].'-'.$_POST['month'].'-10'))));
			}else{
				$dateCheck = date('Y-m-d',strtotime('first day of next month',strtotime(date($_POST['year'].'-'.$_POST['month'].'-10'))));
			}			
			$params['month']=	(int)  date('m',strtotime($dateCheck));
			$params['year']	= (int)   date('Y',strtotime($dateCheck));
		}else{
			$params['month']=	(int) (isset($_POST['month']) ? $_POST['month'] : date('m'));
			$params['year']	= (int)  (isset($_POST['year']) ? $_POST['year'] : date('Y'));
		}		
		$params['month'] = strlen($params['month']) == 1 ? '0'.$params['month'] : $params['month'];
		$this->view->can_create =  Engine_Api::_()->authorization()->isAllowed('sesevent_event', null, 'create');
		$this->view->month = strlen($params['month']) == 1 ? '0'.$params['month'] : $params['month'];
		$this->view->year = $params['year'] ;
		$this->view->viewMoreAfter = $this->_getParam('viewmore','3');
		$this->view->loadData = $this->_getParam('loadData','auto');
		$params['fetchAll'] = $params['calanderWidget'] = true;
		$events = Engine_Api::_()->getDbtable('events', 'sesevent')->getEventSelect($params);
		$eventObj = array();
		if(count($events)){
			foreach($events as $valueEvent){
				$eventObj[date('Y-m-d',strtotime($valueEvent['starttime']))][] = $valueEvent;	
			}
		}
		$this->view->events = $eventObj;
		$this->view->widgetName = 'calender';
  }
}