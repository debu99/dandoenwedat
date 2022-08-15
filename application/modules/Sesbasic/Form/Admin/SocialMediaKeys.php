<?php


class Sesbasic_Form_Admin_SocialMediaKeys extends Engine_Form {

  public function init() {
  
    $this->setTitle('Manage Social Media Keys')
          ->setDescription('You can enter social media keys here for our plugins. If you do not enter any key then corrosponding option will not display in SocialEngineSolutions Plugins.');
          
    $settings = Engine_Api::_()->getApi('settings', 'core');
    
    //facebook
    $this->addElement('Dummy', 'sesbasic_facebook', array(
        'label' => 'Facebook',
    ));
    $this->addElement('Text', 'sesbasic_facebookclientid', array(
        'label' => 'Client Id',
        'description' => '',
        'value' => $settings->getSetting('sesbasic.facebookclientid', ''),
    ));
    $this->addElement('Text', 'sesbasic_facebookclientsecret', array(
        'label' => 'Client Secret',
        'description' => '',
        'value' => $settings->getSetting('sesbasic.facebookclientsecret', ''),
    ));
    
    //twitter
    $this->addElement('Dummy', 'sesbasic_twitter', array(
        'label' => 'Twitter',
    ));
    $this->addElement('Text', 'sesbasic_twitterclientid', array(
        'label' => 'Client Id',
        'description' => '',
        'value' => $settings->getSetting('sesbasic.twitterclientid', ''),
    ));
    $this->addElement('Text', 'sesbasic_twitterclientsecret', array(
        'label' => 'Client Secret',
        'description' => '',
        'value' => $settings->getSetting('sesbasic.twitterclientsecret', ''),
    ));

    //gmail
    $this->addElement('Dummy', 'sesbasic_gmail', array(
        'label' => 'Gmail',
    ));
    $this->addElement('Text', 'sesbasic_gmailclientid', array(
        'label' => 'Client Id',
        'description' => '',
        'value' => $settings->getSetting('sesbasic.gmailclientid', ''),
    ));
    $this->addElement('Text', 'sesbasic_gmailclientsecret', array(
        'label' => 'Client Secret',
        'description' => '',
        'value' => $settings->getSetting('sesbasic.gmailclientsecret', ''),
    ));
    
    //yahoo
    $this->addElement('Dummy', 'sesbasic_yahoo', array(
        'label' => 'Yahoo',
    ));
    $this->addElement('Text', 'sesbasic_yahooconsumerkey', array(
      'label' => 'Consumer Key',
      'description' => '',
      'value' => $settings->getSetting('sesbasic.yahooconsumerkey', ''),
    ));
    $this->addElement('Text', 'sesbasic_yahooconsumersecret', array(
      'label' => 'Consumer Secret',
      'description' => '',
      'value' => $settings->getSetting('sesbasic.yahooconsumersecret', ''),
    ));
    $this->addElement('Text', 'sesbasic_yahooappid', array(
        'label' => 'App Id',
        'description' => '',
        'value' => $settings->getSetting('sesbasic.yahooappid', ''),
    ));
    
    //hotmail
    $this->addElement('Dummy', 'sesbasic_hotmail', array(
        'label' => 'Hotmail',
    ));
    $this->addElement('Text', 'sesbasic_hotmailclientid', array(
        'label' => 'Client Id',
        'description' => '',
        'value' => $settings->getSetting('sesbasic.hotmailclientid', ''),
    ));
    $this->addElement('Text', 'sesbasic_hotmailclientsecret', array(
        'label' => 'Client Secret',
        'description' => '',
        'value' => $settings->getSetting('sesbasic.hotmailclientsecret', ''),
    ));
    
    // Add submit button
    $this->addElement('Button', 'submit', array(
        'label' => 'Save Changes',
        'type' => 'submit',
        'ignore' => true
    ));
  }
}