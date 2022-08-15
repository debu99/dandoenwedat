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

class Sescredit_Widget_BrowseSearchController extends Engine_Content_Widget_Abstract {

  public function indexAction() {

    $searchForm = $this->view->form = new Sescredit_Form_Transaction_Search();
    $request = Zend_Controller_Front::getInstance()->getRequest();
    $searchForm->setMethod('get')->populate($request->getParams());
  }

}
