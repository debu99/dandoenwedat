
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
class Sesevent_Widget_AddToCalendarController extends Engine_Content_Widget_Abstract {
    public function indexAction() {
        if (Engine_Api::_()->core()->hasSubject('sesevent_event')) {
            $this->view->event = $event = Engine_Api::_()->core()->getSubject('sesevent_event');
        }else if(($this->_getParam('event_id','0'))){
					 $this->view->event = $event = Engine_Api::_()->getItem('sesevent_event',$this->_getParam('event_id','0'));
					 $this->getElement()->removeDecorator('Container');
				}else
					return $this->setNoRender();
        $this->view->options = $options = $this->_getParam('options',array('google','yahoo','msn','outlook','ical'));
        if (empty($options))
            return $this->setNoRender();
				if (in_array('google', $options)) {
            $this->view->google = Engine_Api::_()->sesevent()->getGoogleCalendarLink($event);
        }
        if (in_array('yahoo', $options)) {
            $this->view->yahoo = Engine_Api::_()->sesevent()->getYahooLink($event);
        }
				if (in_array('msn', $options)) {
            $this->view->msn = Engine_Api::_()->sesevent()->getMSNlink($event);
        }	
    }
}