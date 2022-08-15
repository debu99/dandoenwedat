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
class Sesevent_Widget_HostofthedayController extends Engine_Content_Widget_Abstract {

  public function indexAction() {
    
    $this->view->socialshare_enable_plusicon = $this->_getParam('socialshare_enable_plusicon', 1);
    $this->view->socialshare_icon_limit = $this->_getParam('socialshare_icon_limit', 2);
    
    $this->view->height = $this->_getParam('height', 200);
    $this->view->width = $this->_getParam('width', 200);
    $this->view->content_show = $this->_getParam('infoshow', array('displayname'));
    
    $this->view->contentInsideOutside = $this->_getParam('contentInsideOutside', 'in');
    $this->view->mouseOver = $this->_getParam('mouseOver', '1');

    $this->view->results = Engine_Api::_()->getDbtable('hosts', 'sesevent')->getOfTheDayResults();
    if (!$this->view->results)
      return $this->setNoRender();
  }

}