<?php

/**
 * SocialEngineSolutions
 *
 * @category   Application_Sessociallogin
 * @package    Sessociallogin
 * @copyright  Copyright 2015-2016 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: AdminSettingsController.php 2017-07-04 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */

class Sessociallogin_AdminSettingsController extends Core_Controller_Action_Admin {

    public function indexAction() {

      $db = Engine_Db_Table::getDefaultAdapter();

      $this->view->navigation = Engine_Api::_()->getApi('menus', 'core')->getNavigation('sessociallogin_admin_main', array(), 'sessociallogin_admin_main_settings');
      
      $settings = Engine_Api::_()->getApi('settings', 'core');
      
      $this->view->form = $form = new Sessociallogin_Form_Admin_Settings_General();

      if ($this->getRequest()->isPost() && $form->isValid($this->getRequest()->getPost())) {
        $values = $form->getValues();
        unset($values['activation_tip']);
        include_once APPLICATION_PATH . "/application/modules/Sessociallogin/controllers/License.php";
        if ($settings->getSetting('sessociallogin.pluginactivated')) {
        foreach ($values as $key => $value) {
          if(Engine_Api::_()->getApi('settings', 'core')->hasSetting($key, $value))
          Engine_Api::_()->getApi('settings', 'core')->removeSetting($key);
          if(!$value && strlen($value) == 0)
            continue;
          Engine_Api::_()->getApi('settings', 'core')->setSetting($key, $value);
        }
          $form->addNotice('Your changes have been saved.');
          if ($error)
            $this->_helper->redirector->gotoRoute(array());
        }
      }
    }

    public function instagramAction() {

        $this->view->navigation = Engine_Api::_()->getApi('menus', 'core')->getNavigation('sessociallogin_admin_main', array(), 'sessociallogin_admin_main_instagram');

        $this->view->form = $form = new Sessociallogin_Form_Admin_Settings_Instagram();
        if( _ENGINE_ADMIN_NEUTER ) {
          $form->populate(array(
              'sessociallogin_instagram_clientid' => '******',
              'sessociallogin_instagram_clientsecret' => '******',
          ));
        }
        if ($this->getRequest()->isPost() && $form->isValid($this->getRequest()->getPost())) {
            $values = $form->getValues();
            foreach ($values as $key => $value) {
                if ($value != '')
                    Engine_Api::_()->getApi('settings', 'core')->setSetting($key, $value);
            }
            $form->addNotice('Your changes have been saved.');
        }
    }
     public function facebookAction() {
        $this->view->navigation = Engine_Api::_()->getApi('menus', 'core')->getNavigation('sessociallogin_admin_main', array(), 'sessociallogin_admin_main_facebook');
        $this->view->form = $form = new Sessociallogin_Form_Admin_Settings_Facebook();
        if( _ENGINE_ADMIN_NEUTER ) {
          $form->populate(array(
              'sessociallogin_facebook_clientid' => '******',
              'sessociallogin_facebook_clientsecret' => '******',
          ));
        }
        if ($this->getRequest()->isPost() && $form->isValid($this->getRequest()->getPost())) {
            $values = $form->getValues();
            foreach ($values as $key => $value) {
                if ($value != '')
                    Engine_Api::_()->getApi('settings', 'core')->setSetting($key, $value);
            }
            $form->addNotice('Your changes have been saved.');
        }
    }
    public function vkAction() {

        $this->view->navigation = Engine_Api::_()->getApi('menus', 'core')->getNavigation('sessociallogin_admin_main', array(), 'sessociallogin_admin_main_vk');

        $this->view->form = $form = new Sessociallogin_Form_Admin_Settings_Vk();
        if( _ENGINE_ADMIN_NEUTER ) {
          $form->populate(array(
              'sessociallogin_vkkey' => '******',
              'sessociallogin_vksecret' => '******',
          ));
        }
        if ($this->getRequest()->isPost() && $form->isValid($this->getRequest()->getPost())) {
            $values = $form->getValues();
            foreach ($values as $key => $value) {
                if ($value != '')
                    Engine_Api::_()->getApi('settings', 'core')->setSetting($key, $value);
            }
            $form->addNotice('Your changes have been saved.');
        }
    }
    
    public function flickrAction() {

        $this->view->navigation = Engine_Api::_()->getApi('menus', 'core')->getNavigation('sessociallogin_admin_main', array(), 'sessociallogin_admin_main_flickr');

        $this->view->form = $form = new Sessociallogin_Form_Admin_Settings_Flickr();
        if( _ENGINE_ADMIN_NEUTER ) {
          $form->populate(array(
              'sessociallogin_flickrkey' => '******',
              'sessociallogin_flickrsecret' => '******',
          ));
        }
        if ($this->getRequest()->isPost() && $form->isValid($this->getRequest()->getPost())) {
            $values = $form->getValues();
            foreach ($values as $key => $value) {
                if ($value != '')
                    Engine_Api::_()->getApi('settings', 'core')->setSetting($key, $value);
            }
            $form->addNotice('Your changes have been saved.');
        }
    }
    
    public function linkedinAction() {

        $this->view->navigation = Engine_Api::_()->getApi('menus', 'core')->getNavigation('sessociallogin_admin_main', array(), 'sessociallogin_admin_main_linkedin');

        $this->view->form = $form = new Sessociallogin_Form_Admin_Settings_Linkedin();
        if( _ENGINE_ADMIN_NEUTER ) {
          $form->populate(array(
              'sessociallogin_linkedin_access' => '******',
              'sessociallogin_linkedin_secret' => '******',
          ));
        }
        if ($this->getRequest()->isPost() && $form->isValid($this->getRequest()->getPost())) {
            $values = $form->getValues();
            foreach ($values as $key => $value) {
                if ($value != '')
                    Engine_Api::_()->getApi('settings', 'core')->setSetting($key, $value);
            }
            $form->addNotice('Your changes have been saved.');
        }
    }

    public function googleAction() {

        $this->view->navigation = Engine_Api::_()->getApi('menus', 'core')->getNavigation('sessociallogin_admin_main', array(), 'sessociallogin_admin_main_google');

        $this->view->form = $form = new Sessociallogin_Form_Admin_Settings_Google();
        if( _ENGINE_ADMIN_NEUTER ) {
          $form->populate(array(
              'sessociallogin_google_clientid' => '******',
              'sessociallogin_google_clientsecret' => '******',
          ));
        }
        if ($this->getRequest()->isPost() && $form->isValid($this->getRequest()->getPost())) {
            $values = $form->getValues();
            foreach ($values as $key => $value) {
                if ($value != '')
                    Engine_Api::_()->getApi('settings', 'core')->setSetting($key, $value);
            }
            $form->addNotice('Your changes have been saved.');
        }
    }

    public function pinterestAction() {

        $this->view->navigation = Engine_Api::_()->getApi('menus', 'core')->getNavigation('sessociallogin_admin_main', array(), 'sessociallogin_admin_main_pinterest');

        $this->view->form = $form = new Sessociallogin_Form_Admin_Settings_Pinterest();
        if( _ENGINE_ADMIN_NEUTER ) {
          $form->populate(array(
              'sessociallogin_pinterest_appid' => '******',
              'sessociallogin_pinterest_secret' => '******',
          ));
        }
        if ($this->getRequest()->isPost() && $form->isValid($this->getRequest()->getPost())) {
            $values = $form->getValues();
            foreach ($values as $key => $value) {
                if ($value != '')
                    Engine_Api::_()->getApi('settings', 'core')->setSetting($key, $value);
            }
            $form->addNotice('Your changes have been saved.');
            if ($error)
                $this->_helper->redirector->gotoRoute(array());
        }
    }

    public function yahooAction() {

        $this->view->navigation = Engine_Api::_()->getApi('menus', 'core')->getNavigation('sessociallogin_admin_main', array(), 'sessociallogin_admin_main_yahoo');

        $this->view->form = $form = new Sessociallogin_Form_Admin_Settings_Yahoo();
        if( _ENGINE_ADMIN_NEUTER ) {
          $form->populate(array(
              'sessociallogin_yahooconsumerkey' => '******',
              'sessociallogin_yahooconsumersecret' => '******',
              'sessociallogin_yahooappid' => '******',
          ));
        }
        if ($this->getRequest()->isPost() && $form->isValid($this->getRequest()->getPost())) {
            $values = $form->getValues();
            foreach ($values as $key => $value) {
                if ($value != '')
                    Engine_Api::_()->getApi('settings', 'core')->setSetting($key, $value);
            }
            $form->addNotice('Your changes have been saved.');
            if ($error)
                $this->_helper->redirector->gotoRoute(array());
        }
    }

    public function hotmailAction() {

        $this->view->navigation = Engine_Api::_()->getApi('menus', 'core')->getNavigation('sessociallogin_admin_main', array(), 'sessociallogin_admin_main_hotmail');

        $this->view->form = $form = new Sessociallogin_Form_Admin_Settings_Hotmail();
        if( _ENGINE_ADMIN_NEUTER ) {
          $form->populate(array(
              'sessociallogin_hotmailclientid' => '******',
              'sessociallogin_hotmailclientsecret' => '******',
          ));
        }
        if ($this->getRequest()->isPost() && $form->isValid($this->getRequest()->getPost())) {
            $values = $form->getValues();
            foreach ($values as $key => $value) {
                if ($value != '')
                    Engine_Api::_()->getApi('settings', 'core')->setSetting($key, $value);
            }
            $form->addNotice('Your changes have been saved.');
            if ($error)
                $this->_helper->redirector->gotoRoute(array());
        }
    }
    
    public function statisticAction() {
    
      $this->view->navigation = Engine_Api::_()->getApi('menus', 'core')->getNavigation('sessociallogin_admin_main', array(), 'sessociallogin_admin_main_statistic');


    }
    
    public function codewriteAction() {
      Engine_Api::_()->sessociallogin()->writeCodeForAdminSignupSteps();
      $this->_redirect('admin/sessociallogin/settings/faq');
    }
    
    public function faqAction() {
      $this->view->navigation = Engine_Api::_()->getApi('menus', 'core')->getNavigation('sessociallogin_admin_main', array(), 'sessociallogin_admin_main_faq');
    }
    public function orderAction()
    {
      $table = Engine_Api::_()->getDbtable('signup', 'user');
      if( !$this->getRequest()->isPost() ) {
        return;
      }
      // Process
      $params = $this->getRequest()->getParams();
      $steps = $table->fetchAll($table->select());
      foreach( $steps as $step ) {
        $step->order = $this->getRequest()->getParam('step_' . $step->signup_id);
        $step->save();
      }
    }
}
