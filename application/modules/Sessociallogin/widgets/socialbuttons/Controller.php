<?php

/**
 * SocialEngineSolutions
 *
 * @category   Application_Sessociallogin
 * @package    Sessociallogin
 * @copyright  Copyright 2015-2016 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: Controller.php 2017-07-04 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */

class Sessociallogin_Widget_SocialButtonsController extends Engine_Content_Widget_Abstract {

  public function indexAction() {
    
    $this->view->btnwidth = $this->_getParam('btnwidth', 250);
    $this->getElement()->removeDecorator('Title');
    $this->view->title = $this->_getParam('title','');
		$this->view->design = $this->_getParam('design',1);
    $this->view->position = $this->_getParam('position',1);
    $this->view->label = $this->_getParam('label',1);
    $this->view->butontext = $this->_getParam('butontext', "Login with %s");
    $viewer = Engine_Api::_()->user()->getViewer()->getIdentity();
    if(!empty($viewer)) 
      return $this->setNoRender();
  }

}
