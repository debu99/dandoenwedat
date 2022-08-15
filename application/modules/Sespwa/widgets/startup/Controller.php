<?php

/**
 * SocialEngineSolutions
 *
 * @category   Application_Sespwa
 * @package    Sespwa
 * @copyright  Copyright 2018-2019 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: Controller.php  2018-11-24 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */
class Sespwa_Widget_StartupController extends Engine_Content_Widget_Abstract {

  public function indexAction() {
    $this->view->title = $this->_getParam('title',Engine_Api::_()->getApi('settings', 'core')->getSetting('core_general_site_title'));
      $this->view->logo = $this->_getParam('logo','');
      $this->view->copyright = $this->_getParam('copyright','1');
  }

}
