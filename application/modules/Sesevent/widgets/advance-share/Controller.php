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
class Sesevent_Widget_AdvanceShareController extends Engine_Content_Widget_Abstract {
  public function indexAction() {
    $this->view->viewer = Engine_Api::_()->user()->getViewer();
    $coreApi = Engine_Api::_()->core();
      if (!$coreApi->hasSubject() && !isset($this->dashboard))
        return $this->setNoRender();
    	$this->view->allowAdvShareOptions = $allowAdvShareOptions = $this->_getParam('advShareOptions',array('privateMessage','siteShare','quickShare','addThis','tellAFriend'));
			
			 $viewer = Engine_Api::_()->user()->getViewer();
       $viewr_id = $viewer->getIdentity();
			 if(!$viewr_id && !in_array('tellAFriend',$allowAdvShareOptions) && (!in_array('addThis',$allowAdvShareOptions) || !Engine_Api::_()->getApi('settings', 'core')->getSetting('ses.addthis',0))){
					 return $this->setNoRender();
			 }
    }
}