<?php

/**
 * SocialEngineSolutions
 *
 * @category   Application_Sessociallogin
 * @package    Sessociallogin
 * @copyright  Copyright 2015-2016 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: Instagram.php 2017-07-04 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */

class Sessociallogin_Form_Admin_Settings_Instagram extends Engine_Form {

  public function init() {

    $settings = Engine_Api::_()->getApi('settings', 'core');
    
    $description = $this->getTranslator()->translate(
    'Here, you can integrate SocialEngine to Instagram for allowing users to login into your website using their Instagram accounts. To do so, create an Application through the ');
    $moreinfo = $this->getTranslator()->translate('<a href="%1$s" target="_blank">Instagram Developers</a> page.<br />');
    $moreinfo1 = $this->getTranslator()->translate('More Info: <a href="%2$s" target="_blank">KB Article</a>');
    $description = vsprintf($description.$moreinfo.$moreinfo1, array('https://www.instagram.com/developer/', 
    'https://www.socialenginesolutions.com/guidelines-social-login-instagram-api-key/',
    ));
    $this->loadDefaultDecorators();
    $this->getDecorator('Description')->setOption('escape', false);

    $this->setTitle('Instagram Integration')
            ->setDescription($description);

    $this->addElement('Text', "sessociallogin_instagram_clientid", array(
        'label' => 'Instagram Client ID',
        'value' => $settings->getSetting('sessociallogin.instagram.clientid', ''),
        'required' => true,
        'allowEmpty' => false,
    ));
    $this->addElement('Text', "sessociallogin_instagram_clientsecret", array(
        'label' => 'Instagram Client Secret',
        'value' => $settings->getSetting('sessociallogin.instagram.clientsecret', ''),
        'required' => true,
        'allowEmpty' => false,
    ));

    $this->addElement('Radio', "sessociallogin_instagram_enable", array(
        'label' => 'Enable Login',
        'description' => 'Do you want to enable login on your website through this provider?',
        'allowEmpty' => true,
        'required' => false,
        'multiOptions' => array(1 => 'Yes', '0' => 'No'),
        'value' => $settings->getSetting('sessociallogin.instagram.enable', 0),
    ));

    $this->addElement('Button', 'submit', array(
        'label' => 'Save Changes',
        'type' => 'submit',
        'ignore' => true
    ));
  }

}
