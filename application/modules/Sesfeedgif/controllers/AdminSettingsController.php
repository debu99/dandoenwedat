<?php

/**
 * SocialEngineSolutions
 *
 * @category   Application_Sesfeedgif
 * @package    Sesfeedgif
 * @copyright  Copyright 2017-2018 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: AdminSettingsController.php  2017-12-06 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */

class Sesfeedgif_AdminSettingsController extends Core_Controller_Action_Admin {

  public function indexAction() {

    $db = Engine_Db_Table::getDefaultAdapter();

    $this->view->navigation = Engine_Api::_()->getApi('menus', 'core')->getNavigation('sesadvancedactivity_admin_main', array(), 'sesfeedgif_admin_main_fegifsettings');

    $this->view->subnavigation = $navigation = Engine_Api::_()->getApi('menus', 'core')
      ->getNavigation('sesfeedgif_admin_main', array(), 'sesfeedgif_admin_main_settings');

    $this->view->form = $form = new Sesfeedgif_Form_Admin_Settings_Global();

    if ($this->getRequest()->isPost() && $form->isValid($this->_getAllParams())) {
      $values = $form->getValues();
      if (Engine_Api::_()->getApi('settings', 'core')->getSetting('sesfeedgif.pluginactivated')) {
        foreach ($values as $key => $value) {
          if($value == '') continue;
            Engine_Api::_()->getApi('settings', 'core')->setSetting($key, $value);
        }
        $form->addNotice('Your changes have been saved.');
//         if($error)
//           $this->_helper->redirector->gotoRoute(array());
      }
    }
  }
}
