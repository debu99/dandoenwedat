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
class Sesevent_Widget_MainSlideshowsController extends Engine_Content_Widget_Abstract {
  public function indexAction() { 
	  $this->view->info = $this->_getParam('infoshow',array('searchForVenue','findVenue','getStarted'));
		$this->view->animationSpeed = $this->_getParam('animationSpeed','300');
		$this->view->height = $this->_getParam('height','480');
		$this->view->navigation = $this->_getParam('navigation',0);
		$this->view->isfullwidth = $this->_getParam('isfullwidth', '1');
		$this->view->margin_top = $this->_getParam('margin_top', '');
		if(!$this->view->isfullwidth){
				$this->view->margin_top = '';
		}
		$this->view->percentageWidth = $this->_getParam('percentageWidth','80');
		$this->view->getStartedLink = $this->_getParam('getStartedLink',1);
		$this->view->titleS = $this->_getParam('titleS','Find the perfect unique venue');
		
		//color settings
		$this->view->sfvtextcolor = $this->_getParam('sfvtextcolor','#fff');
		$this->view->sfvbtncolor = $this->_getParam('sfvbtncolor','#ea623d');
		$this->view->fvbtextcolor = $this->_getParam('fvbtextcolor','#fff');
		$this->view->fvbbtncolor = $this->_getParam('fvbbtncolor','#ea623d');
		$this->view->gsttextcolor = $this->_getParam('gsttextcolor','#fff');
		$this->view->gstbgcolor = $this->_getParam('gstbgcolor','#ea623d');
		
		$this->view->titlecolor = $this->_getParam('titlecolor','#fff');
		$this->view->descriptioncolor = $this->_getParam('descriptioncolor','#fff');
		$this->view->descriptionS = $this->_getParam('descriptionS','for meetings, conferences & special events');
		$this->view->heightChange = $this->view->height ;
		if(in_array('getStarted',$this->view->info)){
				$this->view->heightChange = $this->view->height + 80;
		}
    $this->view->slides = $slides = Engine_Api::_()->getDbTable('slidephotos', 'sesevent')->getSlides();
    $this->view->count = count($slides);
    if (empty($this->view->count) || $this->view->count < 4)
    	return $this->setNoRender();
  }

}