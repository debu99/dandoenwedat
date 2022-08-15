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
class Sespwa_Widget_MenuLogoController extends Engine_Content_Widget_Abstract {

  public function indexAction() {
    $this->view->logo = Engine_Api::_()->getApi('settings', 'core')->getSetting('sespwa.logo', '');
    $this->getElement()->removeDecorator('Container');
  }

}
