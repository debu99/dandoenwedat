<?php

/**
 * SocialEngineSolutions
 *
 * @category   Application_Sessociallogin
 * @package    Sessociallogin
 * @copyright  Copyright 2015-2016 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: Vk.php 2017-07-04 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */

class Sessociallogin_Form_Admin_Settings_Vk extends Engine_Form {

    public function init() {
    
        $description = $this->getTranslator()->translate(
        'Here, you can integrate SocialEngine to VKontakte for allowing users to login into your website using their VKontakte accounts. To do so, create an Application through the ');
        $moreinfo = $this->getTranslator()->translate('<a href="%1$s" target="_blank">VKontakte Developers</a> page.<br />');
        $moreinfo1 = $this->getTranslator()->translate('More Info: <a href="%2$s" target="_blank">KB Article</a>');
        $description = vsprintf($description.$moreinfo.$moreinfo1, array('https://vk.com/dev/products', 
        'https://www.socialenginesolutions.com/guidelines-social-login-vk-login-api-key/',
        ));
        $this->loadDefaultDecorators();
        $this->getDecorator('Description')->setOption('escape', false);
        
        $settings = Engine_Api::_()->getApi('settings', 'core');
        $this->setTitle('VKontakte (VK) Integration')
                ->setDescription($description);

         //yahoo
        $this->addElement('Text', 'sessociallogin_vkkey', array(
            'label' => 'Application ID',
            'description' => '',
            'value' => $settings->getSetting('sessociallogin.vkkey', ''),
        ));
        $this->addElement('Text', 'sessociallogin_vksecret', array(
            'label' => 'Secure key',
            'description' => '',
            'value' => $settings->getSetting('sessociallogin.vksecret', ''),
        ));
        

        $this->addElement('Radio', "sessociallogin_vk_enable", array(
            'label' => 'Enable Login',
            'description' => 'Do you want to enable login on your website through this provider?',
            'allowEmpty' => true,
            'required' => false,
            'multiOptions' => array(1 => 'Yes', '0' => 'No'),
            'value' => $settings->getSetting('sessociallogin.vk.enable', 0),
        ));

        $this->addElement('Button', 'submit', array(
            'label' => 'Save Changes',
            'type' => 'submit',
            'ignore' => true
        ));
    }

}
