<?php

/**
 * SocialEngineSolutions
 *
 * @category   Application_Sessociallogin
 * @package    Sessociallogin
 * @copyright  Copyright 2015-2016 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: Yahoo.php 2017-07-04 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */

class Sessociallogin_Form_Admin_Settings_Yahoo extends Engine_Form {

    public function init() {

        $description = $this->getTranslator()->translate(
        'Here, you can integrate SocialEngine to Yahoo for allowing users to login into your website using their Yahoo accounts. To do so, create an Application through the ');
        $moreinfo = $this->getTranslator()->translate('<a href="%1$s" target="_blank">Yahoo Developers</a> page.<br />');
        $moreinfo1 = $this->getTranslator()->translate('More Info: <a href="%2$s" target="_blank">KB Article</a>');
        $description = vsprintf($description.$moreinfo.$moreinfo1, array('https://developer.yahoo.com/apps/', 
        'https://www.socialenginesolutions.com/guidelines-social-login-yahoo-api-key/',
        ));
        $this->loadDefaultDecorators();
        $this->getDecorator('Description')->setOption('escape', false);
    
        $settings = Engine_Api::_()->getApi('settings', 'core');
        $this->setTitle('Yahoo Integration')
                ->setDescription($description);

        //yahoo
        $this->addElement('Text', 'sessociallogin_yahooconsumerkey', array(
            'label' => 'Yahoo Consumer Key',
            'description' => '',
            'value' => $settings->getSetting('sessociallogin.yahooconsumerkey', ''),
        ));
        $this->addElement('Text', 'sessociallogin_yahooconsumersecret', array(
            'label' => 'Yahoo Consumer Secret',
            'description' => '',
            'value' => $settings->getSetting('sessociallogin.yahooconsumersecret', ''),
        ));
        $this->addElement('Text', 'sessociallogin_yahooappid', array(
            'label' => 'Yahoo App Id',
            'description' => '',
            'value' => $settings->getSetting('sessociallogin.yahooappid', ''),
        ));

        $this->addElement('Radio', "sessociallogin_yahoo_enable", array(
            'label' => 'Enable Login',
            'description' => 'Do you want to enable login on your website through this provider?',
            'allowEmpty' => true,
            'required' => false,
            'multiOptions' => array(1 => 'Yes', '0' => 'No'),
            'value' => $settings->getSetting('sessociallogin.yahoo.enable', 0),
        ));

        $this->addElement('Button', 'submit', array(
            'label' => 'Save Changes',
            'type' => 'submit',
            'ignore' => true
        ));
    }

}
