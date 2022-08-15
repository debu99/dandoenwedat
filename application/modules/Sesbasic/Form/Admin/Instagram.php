<?php

/**
 * SocialEngineSolutions
 *
 * @category   Application_Sesbasic
 * @package    Sesbasic
 * @copyright  Copyright 2015-2016 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: Instagram.tpl 2015-07-25 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */

class Sesbasic_Form_Admin_Instagram extends Engine_Form {

  public function init() {
  
    $settings = Engine_Api::_()->getApi('settings', 'core');
    
    $this->setTitle('Instagram App Settings')
            ->setDescription('These settings affect all members in your community.');
		$view = Zend_Registry::isRegistered('Zend_View') ? Zend_Registry::get('Zend_View') : null;
		
    $this->addElement('Text', "sesbasic_instagram_clientid", array(
        'label' => 'Client ID',
        'value' => $settings->getSetting('sesbasic.instagram.clientid',''),
        'required'=>true,
        'allowEmpty'=>false,
    ));
    
    $this->addElement('Text', "sesbasic_instagram_clientsecret", array(
        'label' => 'Client Secret',
        'value' => $settings->getSetting('sesbasic.instagram.clientsecret',''),
        'required'=>true,
        'allowEmpty'=>false,
    ));
    
    // Buttons
    $this->addElement('Button', 'submit', array(
        'label' => 'Save Settings',
        'type' => 'submit',
        'ignore' => true,
        'decorators' => array('ViewHelper')
    ));
  }
}