<?php

/**
 * SocialEngineSolutions
 *
 * @category   Application_Sesgdpr
 * @package    Sesgdpr
 * @copyright  Copyright 2018-2019 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: Cookie.php 2018-05-19 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */

class Sesgdpr_Form_Admin_Cookie extends Engine_Form {

  public function init() {

    $settings = Engine_Api::_()->getApi('settings', 'core');

    $this->setTitle('Cookies Consent Settings')
            ->setDescription('Here, you can configure the settings for the Cookie consents. You can enter the text for the cookie contest banner, choose colors for it. You can also choose the options to be displayed in the banner with the placement and design.');
          
      $this->addElement('Textarea', 'gdpr_bannertext', array(
        'label' => 'Cookie Consent Banner Text',
        'description' => 'Enter the text for the Cookie banner content in the box below.',
        'value' => $settings->getSetting('gdpr_bannertext', 'We use cookies to personalise site content, social media features and to analyse our traffic. We also share information about your use of this site with our advertising and social media partners.'),
      ));  
    
      $this->addElement('Radio', 'gdpr_bannerstyle', array(
        'label' => 'Banner Design Template',
        'description' => 'Choose the banner template from the designs below.',
        'multiOptions'=> array(
        'top_center'=>'Banner in Top Center',
        'bottom_center'=>'Banner in Bottom Center',
        //'full_bottom'=>'Full Screen Banner in Bottom',
        'left'=>'Left Banner',
        'right'=>'Right Banner',
        ),
        'value' => $settings->getSetting('gdpr_bannerstyle', 'top_center'),
      ));
      
      
      $this->addElement('MultiCheckbox', 'gdpr_banneroption', array(
        'label' => 'Banner Options',
        'description' => 'Choose the options to be shown in the cookie banner.',
        'multiOptions'=> array('changeSettings'=>'Change Settings','readMore'=>'Read More','accept'=>'Accept'),
        'value' => $settings->getSetting('gdpr_banneroption', array('changeSettings','readMore','accept')),
      ));
      
      $this->addElement('Text', 'gdpr_bannerbackgroundcolor', array(
        'label' => 'Background Color',
        'class'=>'SEScolor',
        'description' => 'Choose the background color of the banner.',
        'value' => $settings->getSetting('gdpr_bannerbackgroundcolor', '#fff'),
      ));
      
      $this->addElement('Text', 'gdpr_bannertextcolor', array(
        'label' => 'Banner Text Color',
        'class'=>'SEScolor',
        'description' => 'Choose the text color of the banner.',
        'value' => $settings->getSetting('gdpr_bannertextcolor', '#555'),
      ));
      
      $this->addElement('Text', 'gdpr_bannerlinkcolor', array(
        'label' => 'Banner Link Color',
        'class'=>'SEScolor',
        'description' => 'Choose the link color of the banner.',
        'value' => $settings->getSetting('gdpr_bannerlinkcolor', '#3960bb'),
      ));
      
       $this->addElement('Text', 'gdpr_privacyurl', array(
        'label' => 'Privacy Policy Page URL',
        'required'=>true,
        'allowEmpty'=>false,
        'description' => 'Enter the URL of the privacy Policy Page of your website.',
        'value' => $settings->getSetting('gdpr_privacyurl', 'help/privacy'),
      ));
   	 
			// Add submit button
			$this->addElement('Button', 'submit', array(
					'label' => 'Save Changes',
					'type' => 'submit',
					'ignore' => true
			));
  }

}
