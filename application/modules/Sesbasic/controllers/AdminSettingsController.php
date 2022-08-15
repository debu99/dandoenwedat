<?php

/**
 * SocialEngineSolutions
 *
 * @category   Application_Sesbasic
 * @package    Sesbasic
 * @copyright  Copyright 2015-2016 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: AdminSettingsController.php 2015-07-25 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */
class Sesbasic_AdminSettingsController extends Core_Controller_Action_Admin {
  
  public function instagramAction() {
    $db = Engine_Db_Table::getDefaultAdapter();
    $this->view->navigation = Engine_Api::_()->getApi('menus', 'core')->getNavigation('sesbasic_admin_main', array(), 'sesbasic_admin_main_instagram');
    $this->view->form = $form = new Sesbasic_Form_Admin_Instagram();
    if ($this->getRequest()->isPost() && $form->isValid($this->getRequest()->getPost())) {
      $values = $form->getValues(); 
       foreach ($values as $key => $value) {
          if($value != '')
          Engine_Api::_()->getApi('settings', 'core')->setSetting($key, $value);
      }
      $form->addNotice('Your changes have been saved.');
    }
  }
  
  public function socialMediaKeyAction() {
  
    $db = Engine_Db_Table::getDefaultAdapter();
    
    $this->view->navigation = Engine_Api::_()->getApi('menus', 'core')->getNavigation('sesbasic_admin_main', array(), 'sesbasic_admin_main_managesocialmedia');
    $this->view->form = $form = new Sesbasic_Form_Admin_SocialMediaKeys();
    if ($this->getRequest()->isPost() && $form->isValid($this->getRequest()->getPost())) {
      $values = $form->getValues();
      unset($values['sesbasic_facebook']);
      unset($values['sesbasic_twitter']);
      unset($values['sesbasic_hotmail']);
      unset($values['sesbasic_yahoo']);
      unset($values['sesbasic_gmail']);
       foreach ($values as $key => $value) {
          //if($value != '')
          Engine_Api::_()->getApi('settings', 'core')->setSetting($key, $value);
        }
        $form->addNotice('Your changes have been saved.');
        
    }
  }
  
  public function contactUsAction() {

    $this->view->navigation = Engine_Api::_()->getApi('menus', 'core')->getNavigation('sesbasic_admin_main', array(), 'sesbasic_admin_contactus');
  }

  public function overviewAction() {

    $this->view->navigation = Engine_Api::_()->getApi('menus', 'core')->getNavigation('sesbasic_admin_main', array(), 'sesbasic_admin_overview');
  }

  public function colorChooserAction() {

    $this->view->navigation = Engine_Api::_()->getApi('menus', 'core')->getNavigation('sesbasic_admin_main', array(), 'sesbasic_admin_colorpicker');

    $this->view->form = new Sesbasic_Form_Admin_Settings_ColorChooser();
  }
	public function currencyAction() {
    $this->view->navigation = $navigation = Engine_Api::_()->getApi('menus', 'core')
            ->getNavigation('sesbasic_admin_main', array(), 'sesbasic_admin_main_currency');
    // Populate currency options
    $fullySupportedCurrencies = Engine_Api::_()->sesbasic()->getSupportedCurrency();
    $this->view->fullySupportedCurrencies = $fullySupportedCurrencies;
  }
 //Enable Action
  public function activeAction() {
		$settings = Engine_Api::_()->getApi('settings', 'core');
    $id = $this->_getParam('id');
    if (!empty($id)) {
      $active = !$settings->getSetting('sesbasic.'.$id.'active','0');
      $settings->setSetting('sesbasic.'.$id.'active',$active);
    }
    $this->_redirect($_SERVER['HTTP_REFERER']);
  }
  public function editCurrencyAction() {
    $this->_helper->layout->setLayout('admin-simple');
    $id = $this->_getParam('id');
    $this->view->currency_symbol = $currency_symbol = $id;
    $this->view->form = $form = new Sesbasic_Form_Admin_Settings_EditCurrency();
    $settings = Engine_Api::_()->getApi('settings', 'core');
    $getSetting = $settings->getSetting('sesbasic.' . $currency_symbol);
    $form->getElement('currency_rate')->setValue($getSetting);
    $form->getElement('currency_symbol')->setValue($id);
    if ($this->getRequest()->isPost() && $form->isValid($this->getRequest()->getPost())) {
      $settings->setSetting('sesbasic.' . $_POST['currency_symbol'], $_POST['currency_rate']);
      $this->_forward('success', 'utility', 'core', array(
          'smoothboxClose' => 10,
          'parentRefresh' => 10,
          'messages' => array(Zend_Registry::get('Zend_Translate')->_('You have successfully edit currency.'))
      ));
    }
  }
  public function globalAction() {

    $this->view->navigation = Engine_Api::_()->getApi('menus', 'core')->getNavigation('sesbasic_admin_main', array(), 'sesbasic_admin_global');

    $this->view->form = $form = new Sesbasic_Form_Admin_Settings_Global();
    if( _ENGINE_ADMIN_NEUTER ) {
      $form->populate(array(
          'ses_mapApiKey' => '******',
          'ses_addthis' => '******',
      ));
    }
    if ($this->getRequest()->isPost() && $form->isValid($this->_getAllParams())) {
      $singlecart = Engine_Api::_()->getApi('settings', 'core')->getSetting('site.enble.singlecart', 0); 
      $values = $form->getValues();
      if (isset($values['ses_bottomtotop']) && !empty($values['ses_bottomtotop'])) {
        $db = Engine_Db_Table::getDefaultAdapter();
        $parent_content_id = $db->select()
                ->from('engine4_core_content', 'content_id')
                ->where('page_id = ?', "1")
                ->where('name = ?', "main")
                ->where('type = ?', "container")
                ->limit(1)
                ->query()
                ->fetchColumn();

        $db->insert('engine4_core_content', array(
            'type' => 'widget',
            'name' => 'sesbasic.bottom-to-top',
            'page_id' => 1,
            'order' => 998,
            'parent_content_id' => $parent_content_id,
        ));
      }
      if (isset($values['optionsenableglotion']))
        $values['optionsenableglotion'] = serialize($values['optionsenableglotion']);
      else
        $values['optionsenableglotion'] = serialize(array());
      foreach ($values as $key => $value) {
        Engine_Api::_()->getApi('settings', 'core')->setSetting($key, $value);
      } 
      if($singlecart != @$values['site_enble_singlecart']) { 
        Engine_Api::_()->sesbasic()->updateCart(Engine_Api::_()->getApi('settings', 'core')->getSetting('site.enble.singlecart', 0));
      }
      $form->addNotice('Your changes have been saved.');
    }
  }
  public function updateCurrencyAction(){
		ini_set('max_execution_time', 0);
		Engine_Api::_()->sesbasic()->updateCurrencyValues();
		$this->_redirect($_SERVER['HTTP_REFERER']);
	}
  public function sesPluginsArray() {
  
  
  return array(
  
    
  
    'sesadvsitenotification' => array('title' => 'Advanced Site Notifications in Popups Plugin', 'version' => '4.8.13', 'pluginpage_link' => 'https://www.socialenginesolutions.com/social-engine/advanced-site-notifications-in-popups-plugin/'),
  
    'sesadvancedcomment' => array('title' => 'Advanced Nested Comments with Attachments Plugin', 'version' => '4.8.13p6', 'pluginpage_link' => 'https://www.socialenginesolutions.com/social-engine/advanced-nested-comments-with-attachments-plugin/'),
    
    'sesadvancedactivity' => array('title' => 'Advanced News & Activity Feeds Plugin', 'version' => '4.8.13p11', 'pluginpage_link' => 'https://www.socialenginesolutions.com/social-engine/advanced-news-activity-feeds-plugin/'),

    'sesbrowserpush' => array('title' => 'Browser Push Notifications Plugin', 'version' => '4.8.13p1', 'pluginpage_link' => 'https://www.socialenginesolutions.com/social-engine/browser-push-notifications-plugin/'),

    'sesariana' => array('title' => 'Responsive Vertical Theme', 'version' => '4.8.13p1', 'pluginpage_link' => 'http://www.socialenginesolutions.com/social-engine/advanced-events-plugin/'),
    
    'seseventticket' => array('title' => 'Advanced Events – Events Tickets Selling & Booking System', 'version' => '4.8.12', 'pluginpage_link' => 'https://www.socialenginesolutions.com/social-engine/advanced-events-events-tickets-selling-booking-system/'),

  
    'sesevent' => array('title' => 'Advanced Events Plugin', 'version' => '4.8.13', 'pluginpage_link' => 'http://www.socialenginesolutions.com/social-engine/advanced-events-plugin/'),
    
    'sesblog' => array('title' => 'Advanced Members Plugin', 'version' => '4.8.12p3', 'pluginpage_link' => 'http://www.socialenginesolutions.com/social-engine/advanced-blog-plugin/'),
    
    'sesmember' => array('title' => 'Advanced Members Plugin', 'version' => '4.8.11p5', 'pluginpage_link' => 'http://www.socialenginesolutions.com/social-engine/advanced-members-plugin/'),
    
    'sesusercoverphoto' => array('title' => 'Member Profiles Cover Photo Plugin', 'version' => '4.8.12', 'pluginpage_link' => 'http://www.socialenginesolutions.com/social-engine/member-profiles-cover-photo-plugin/'),
    
    'sesprofilelock' => array('title' => 'User Accounts Privacy & Content Security with Password Plugin', 'version' => '4.8.10', 'pluginpage_link' => 'http://www.socialenginesolutions.com/social-engine/user-accounts-privacy-and-security-plugin/'),
  
    'sesvideoimporter' => array('title' => 'Advanced Videos & Channels – Video Importer & Search Extension', 'version' => '4.8.10', 'pluginpage_link' => 'http://www.socialenginesolutions.com/social-engine/advanced-videos-channels-video-importer-search-extension/'),
  
    'seselegant' => array('title' => 'Responsive Elegant Theme', 'version' => '4.8.10p1', 'pluginpage_link' => 'http://www.socialenginesolutions.com/social-engine/responsive-elegant-theme/'),

	  'sesmultipleform' => array('title' => 'All in One Multiple Forms Plugin', 'version' => '4.8.10', 'pluginpage_link' => 'http://www.socialenginesolutions.com/social-engine/all-in-one-multiple-forms-plugin-advanced-contact-us-feedback-query-forms-etc'),
  
    'sesfooter' => array('title' => 'Advanced Footer Plugin', 'version' => '4.8.10', 'pluginpage_link' => 'http://www.socialenginesolutions.com/social-engine/advanced-footer-plugin/'),
    
    'sesspectromedia' => array('title' => 'Responsive SpectroMedia Theme', 'version' => '4.8.13', 'pluginpage_link' => 'http://www.socialenginesolutions.com/social-engine/spectromedia-theme/'),
    
	  'seshtmlbackground' => array('title' => 'HTML5 Videos & Photos Background Plugin', 'version' => '4.8.10p1', 'pluginpage_link' => 'http://www.socialenginesolutions.com/social-engine/html5-videos-photos-background-plugin/'),
	  
	  'sesdemouser' => array('title' => 'Site Tour by Auto Logging With Test User Plugin', 'version' => '4.8.10', 'pluginpage_link' => 'http://www.socialenginesolutions.com/social-engine/site-tour-by-auto-logging-with-test-user-plugin/'),
	  
	  'sesvideo' => array('title' => 'Advanced Videos & Channels Plugin', 'version' => '4.8.13p1', 'pluginpage_link' => 'http://www.socialenginesolutions.com/social-engine/advanced-videos-channels-plugin/'),
	  
	  'sespagebuilder' => array('title' => 'Page Builder and Shortcodes Plugin', 'version' => '4.8.10', 'pluginpage_link' => 'http://www.socialenginesolutions.com/social-engine/page-builder-and-shortcodes-plugin/'),

	  'sespoke' => array('title' => 'Advanced Poke, Wink, Slap, etc & Gifts Plugin', 'version' => '4.8.13', 'pluginpage_link' => 'http://www.socialenginesolutions.com/social-engine/advanced-poke-wink-slap-etc-gifts-plugin/'),
	  
	  'sesalbum' => array('title' => 'Advanced Photos & Albums Plugin', 'version' => '4.8.13', 'pluginpage_link' => 'http://www.socialenginesolutions.com/social-engine/advanced-photos-albums-plugin/'),
	  
	  'sesteam' => array('title' => 'Team Showcase & Multi-Use Team Plugin', 'version' => '4.8.10', 'pluginpage_link' => 'http://www.socialenginesolutions.com/social-engine/team-showcase-multi-use-team-plugin/'),
   
	  'sesmusic' => array('title' => 'Advanced Music Albums, Songs & Playlists Plugin', 'version' => '4.8.13', 'pluginpage_link' => 'http://www.socialenginesolutions.com/social-engine/advanced-music-albums-songs-playlists-plugin/'),
	  
	  'seschristmas' => array('title' => 'Christmas & New Year Design Elements Plugin', 'version' => '4.8.9p1', 'pluginpage_link' => 'http://www.socialenginesolutions.com/social-engine/christmas-happy-new-year-design-elements/'),
	  
	  'sesbasic' => array('title' => 'SocialEngineSolutions Basic Required Plugin', 'version' => '4.8.13p8', 'pluginpage_link' => 'http://www.socialenginesolutions.com/social-engine/socialenginesolutions-basic-required-plugin/'),

	  );
  
  }
  
  public function faqwidgetAction() {

  }
  
  public function upgradePluginsAction() {
	  $this->view->navigation = Engine_Api::_()->getApi('menus', 'core')->getNavigation('sesbasic_admin_main', array(), 'sesbasic_admin_upgradeplugin');
	  $this->view->plugns_array = Engine_Api::_()->getDbTable('plugins', 'sesbasic')->getResults();
	  
	  
	  //$this->sesPluginsArray();
  }
  
  public function notinstalledPluginsAction() {
	  $this->view->navigation = Engine_Api::_()->getApi('menus', 'core')->getNavigation('sesbasic_admin_main', array(), 'sesbasic_admin_notinstalledplugin');
	  $this->view->plugns_array = $this->sesPluginsArray();
  }
  
  public function se410BeforeUpgradeAction() {
  
    $db = Engine_Db_Table::getDefaultAdapter();
    $table_exist_action = $db->query('SHOW TABLES LIKE \'engine4_activity_actions\'')->fetch();
    if (!empty($table_exist_action)) {
      $privacy = $db->query('SHOW COLUMNS FROM engine4_activity_actions LIKE \'privacy\'')->fetch();
      if (!empty($privacy)) {
        $db->query('ALTER TABLE `engine4_activity_actions` CHANGE `privacy` `ses_privacy` VARCHAR(500) CHARACTER SET latin1 COLLATE latin1_general_ci NULL DEFAULT NULL;');
      }
    }
  }
  
  public function se410AfterUpgradeAction() {
  
    $db = Engine_Db_Table::getDefaultAdapter();
    $table_exist_action = $db->query('SHOW TABLES LIKE \'engine4_activity_actions\'')->fetch();
    if (!empty($table_exist_action)) {
      $privacy = $db->query('SHOW COLUMNS FROM engine4_activity_actions LIKE \'privacy\'')->fetch();
      if (!empty($privacy)) {
        $db->query('ALTER TABLE `engine4_activity_actions` DROP `privacy`;');
        $db->query('ALTER TABLE `engine4_activity_actions` CHANGE `ses_privacy` `privacy` VARCHAR(500) CHARACTER SET latin1 COLLATE latin1_general_ci NULL DEFAULT NULL;');
      }
    }
  }
}
