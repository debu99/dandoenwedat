<?php

/**
 * SocialEngineSolutions
 *
 * @category   Application_Sessociallogin
 * @package    Sessociallogin
 * @copyright  Copyright 2015-2016 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: Flickr.php 2017-07-04 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */

class Sessociallogin_Form_Admin_Settings_Flickr extends Engine_Form {

    public function init() {

    
        $description = $this->getTranslator()->translate(
        'Here, you can integrate SocialEngine to Flickr for allowing users to login into your website using their Flickr accounts. To do so, create an Application through the ');
        $moreinfo = $this->getTranslator()->translate('<a href="%1$s" target="_blank">Flickr Developers</a> page.<br />');
        $moreinfo1 = $this->getTranslator()->translate('More Info: <a href="%2$s" target="_blank">KB Article</a>');
        $description = vsprintf($description.$moreinfo.$moreinfo1, array('https://www.flickr.com/services/apps/create/apply/', 
        'https://www.socialenginesolutions.com/guidelines-social-login-flickr-api-key/',
        ));
        $this->loadDefaultDecorators();
        $this->getDecorator('Description')->setOption('escape', false);
    
        $settings = Engine_Api::_()->getApi('settings', 'core');
        $this->setTitle('Flickr Integration')
                ->setDescription($description);

        //flickr
        $this->addElement('Text', 'sessociallogin_flickrkey', array(
            'label' => 'Flickr Key',
            'description' => '',
            'value' => $settings->getSetting('sessociallogin.flickrkey', ''),
        ));
        $this->addElement('Text', 'sessociallogin_flickrsecret', array(
            'label' => 'Flickr Secret',
            'description' => '',
            'value' => $settings->getSetting('sessociallogin.flickrsecret', ''),
        ));
        
        $this->addElement('Radio', "sessociallogin_flickr_enable", array(
            'label' => 'Enable Login',
            'description' => 'Do you want to enable login on your website through this provider?',
            'allowEmpty' => true,
            'required' => false,
            'multiOptions' => array(1 => 'Yes', '0' => 'No'),
            'value' => $settings->getSetting('sessociallogin.flickr.enable', 0),
        ));

        $this->addElement('Button', 'submit', array(
            'label' => 'Save Changes',
            'type' => 'submit',
            'ignore' => true
        ));
    }

}
