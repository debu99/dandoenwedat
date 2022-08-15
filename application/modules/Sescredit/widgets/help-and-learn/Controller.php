<?php

/**
 * SocialEngineSolutions
 *
 * @category   Application_Sescredit
 * @package    Sescredit
 * @copyright  Copyright 2019-2020 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: Controller.php  2019-01-18 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */
class Sescredit_Widget_HelpAndLearnController extends Engine_Content_Widget_Abstract {

  public function indexAction() {

    $this->view->signupURL = (!empty($_SERVER["HTTPS"]) && strtolower($_SERVER["HTTPS"] == 'on') ? "https://" : "http://") . $_SERVER['HTTP_HOST'] . Zend_Controller_Front::getInstance()->getRouter()->assemble(array(), 'user_signup', true);
    $this->view->totalPoint = Engine_Api::_()->getDbTable('details','sescredit')->getTotalPoint();
  }

}
