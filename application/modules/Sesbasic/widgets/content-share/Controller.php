<?php

/**
 * SocialEngineSolutions
 *
 * @category   Application_Sesbasic
 * @package    Sesbasic
 * @copyright  Copyright 2015-2016 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: Controller.php 2015-07-25 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */
class Sesbasic_Widget_ContentShareController extends Engine_Content_Widget_Abstract {

  public function indexAction() {
   $this->view->codeEnable = $this->_getParam('codeEnable','socialeShare');
	  
  }

}