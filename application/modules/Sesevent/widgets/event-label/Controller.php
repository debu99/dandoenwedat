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
class Sesevent_Widget_EventLabelController extends Engine_Content_Widget_Abstract
{
  public function indexAction()
  {
		
		if(!Engine_Api::_()->core()->hasSubject('sesevent_event')) {
      return $this->setNoRender();
    }
   	$this->view->subject = $subject = Engine_Api::_()->core()->getSubject('sesevent_event');
		if(strtotime($this->view->enddate) < strtotime(date('Y-m-d')) && $this->view->offtheday == 1)
		 $offtheday = 0;
		else 
		 $offtheday = $subject->offtheday;
		if(!$subject->verified && !$subject->sponsored && !$subject->featured && !$subject->verified){
			return $this->setNoRender();
		}
		$this->view->option = $this->_getParam('option',array('offtheday','verified','sponsored','featured'));
  }
}