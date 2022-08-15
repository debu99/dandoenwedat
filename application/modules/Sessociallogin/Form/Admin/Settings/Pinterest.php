<?php

/**
 * SocialEngineSolutions
 *
 * @category   Application_Sessociallogin
 * @package    Sessociallogin
 * @copyright  Copyright 2015-2016 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: Pinterest.php 2017-07-04 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */

class Sessociallogin_Form_Admin_Settings_Pinterest extends Engine_Form {

    public function init() {

    
        $description = $this->getTranslator()->translate(
        'Here, you can integrate SocialEngine to Pinterest for allowing users to login into your website using their Pinterest accounts. To do so, create an Application through the ');
        $moreinfo = $this->getTranslator()->translate('<a href="%1$s" target="_blank">Pinterest Developers</a> page.<br />');
        $moreinfo1 = $this->getTranslator()->translate('More Info: <a href="%2$s" target="_blank">KB Article</a>');
        $description = vsprintf($description.$moreinfo.$moreinfo1, array('https://developers.pinterest.com/apps', 
        'https://www.socialenginesolutions.com/guidelines-social-login-pinterest-api-key/',
        ));
        $this->loadDefaultDecorators();
        $this->getDecorator('Description')->setOption('escape', false);
        
        $settings = Engine_Api::_()->getApi('settings', 'core');

        $this->setTitle('Pinterest Integration')
                ->setDescription($description);

        $this->addElement('Text', "sessociallogin_pinterest_appid", array(
            'label' => 'Pinterest App ID',
            'value' => $settings->getSetting('sessociallogin.pinterest.appid', ''),
            'required' => true,
            'allowEmpty' => false,
        ));
        $this->addElement('Text', "sessociallogin_pinterest_secret", array(
            'label' => 'Pinterest App secret',
            'value' => $settings->getSetting('sessociallogin.pinterest.secret', ''),
            'required' => true,
            'allowEmpty' => false,
        ));

        $this->addElement('Radio', "sessociallogin_pinterest_enable", array(
            'label' => 'Enable Login',
            'description' => 'Do you want to enable login on your website through this provider?',
            'allowEmpty' => true,
            'required' => false,
            'multiOptions' => array(1 => 'Yes', '0' => 'No'),
            'value' => $settings->getSetting('sessociallogin.pinterest.enable', 0),
        ));

        $this->addElement('Button', 'submit', array(
            'label' => 'Save Changes',
            'type' => 'submit',
            'ignore' => true
        ));
    }

}
