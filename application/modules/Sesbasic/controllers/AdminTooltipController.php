<?php

/**
 * SocialEngineSolutions
 *
 * @category   Application_Sesbasic
 * @package    Sesbasic
 * @copyright  Copyright 2015-2016 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: AdminTooltipController.php 2015-07-25 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */
class Sesbasic_AdminTooltipController extends Core_Controller_Action_Admin {
	public function indexAction(){
		$module = $this->_getParam('modulename',false);
		//echo print_r($this->_getAllParams());die;
		if($module && $module != 'generaltooltip'){
			if($module == 'sesevent'){
				$form = new Sesbasic_Form_Admin_Tooltip_Event();
				$submenu = 'sesbasic_admin_main_sesevent';
			}
			elseif($module == 'sesmember'){
				$form = new Sesbasic_Form_Admin_Tooltip_Member();
				$submenu = 'sesbasic_admin_main_sesmember';
			}
            elseif($module == 'sescontest'){
				$form = new Sesbasic_Form_Admin_Tooltip_Contest();
				$submenu = 'sesbasic_admin_main_sescontest';
			} elseif($module == 'sescrowdfunding'){
				$form = new Sesbasic_Form_Admin_Tooltip_Crowdfunding();
				$submenu = 'sesbasic_admin_main_sescrowdfunding';
			}
		}else{
			$form = new Sesbasic_Form_Admin_Tooltip_Global();
			$submenu = 'sesbasic_admin_main_generaltooltip';
		}
		$settings = Engine_Api::_()->getApi('settings', 'core');
		$this->view->form = $form;
		//Check post
		$this->view->navigation = Engine_Api::_()->getApi('menus', 'core')->getNavigation('sesbasic_admin_main', array(), 'sesbasic_admin_tooltip');
    $this->view->subNavigation = Engine_Api::_()->getApi('menus', 'core')->getNavigation('sesbasic_admin_tooltipsettings', array(), $submenu);
    if (!$this->getRequest()->isPost())
      return;

		if ($this->getRequest()->isPost() && $form->isValid($this->getRequest()->getPost())) {
	      $values = $form->getValues();

				foreach ($values as $key => $value) {
					if($settings->hasSetting($key))
          	$settings->removeSetting($key);
					Engine_Api::_()->getApi('settings', 'core')->setSetting($key, $value);
				}
				$form->addNotice('Your changes have been saved.');
				$this->_helper->redirector->gotoRoute(array());
    }
	}
}
