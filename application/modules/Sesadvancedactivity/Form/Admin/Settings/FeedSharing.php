<?php

/**
 * SocialEngineSolutions
 *
 * @category   Application_Sesadvancedactivity
 * @package    Sesadvancedactivity
 * @copyright  Copyright 2016-2017 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: General.php  2017-01-12 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */

class Sesadvancedactivity_Form_Admin_Settings_FeedSharing extends Engine_Form {

  public function init() {

    $settings = Engine_Api::_()->getApi('settings', 'core');

    $this->setTitle('Feed Sharing Settings')
            ->setDescription('Here, you can choose to enable sharing of activity feeds from your website to other social networking services.');

		$view = Zend_Registry::isRegistered('Zend_View') ? Zend_Registry::get('Zend_View') : null;
		$description = sprintf('Do you want to allow your users to share activity feeds from your website on other social networking websites (Facebook, Twitter, Google Plus & LinkedIn)?');


    $this->addElement('Radio', 'sesadvancedactivity_enablesocialshare', array(
      'label' => 'Enable Social Sharing',
      'description' => $description,
      'multiOptions' => array(
          1 => 'Yes',
          0 => 'No'
      ),
      'onchange' => "enableShare(this.value)",
      'value' => $settings->getSetting('sesadvancedactivity.enablesocialshare', 1),
    ));
    $this->getElement('sesadvancedactivity_enablesocialshare')->getDecorator('Description')->setOptions(array('placement' => 'PREPEND', 'escape' => false));

		$view = Zend_Registry::isRegistered('Zend_View') ? Zend_Registry::get('Zend_View') : null;

		$description = sprintf('Do you want to allow your users to share activity feeds from your website on other social networking website (Facebook, Twitter, Pinterest, Skype, and many more …)?');

//     $this->addElement('Radio', 'sesadvancedactivity_enablesessocialshare', array(
//       'label' => 'Enable Advanced Social Sharing',
//       'description' => $description,
//       'multiOptions' => array(
//           1 => 'Yes',
//           0 => 'No'
//       ),
//       'onchange' => "enablesessocialshare(this.value)",
//       'value' => $settings->getSetting('sesadvancedactivity.enablesessocialshare', 0),
//     ));
//     $this->getElement('sesadvancedactivity_enablesessocialshare')->getDecorator('Description')->setOptions(array('placement' => 'PREPEND', 'escape' => false));
//
//
//     $this->addElement('Text', "sesadvancedactivity_iconlimit", array(
//         'label' => "Count For Social Sites To Show",
//         'description' => 'Enter the number of social networking sites to be shown while sharing the activity feeds. (If you enable More Icon, then other social site icons will display on clicking the more icon.)',
//         'value' => $settings->getSetting('sesadvancedactivity.iconlimit', 3),
//         'validators' => array(
//             array('Int', true),
//             array('GreaterThan', true, array(0)),
//         )
//     ));
//
//     $this->addElement('Select', "sesadvancedactivity_enableplusicon", array(
//       'label' => "Show More Icon",
//       'description' => 'Do you want to enable More icon to view all social networking sites’ share icons?',
//       'multiOptions' => array(
//         '1' => 'Yes',
//         '0' => 'No',
//       ),
//       'value' => $settings->getSetting('sesadvancedactivity.enableplusicon', 0),
//     ));

    // Add submit button
    $this->addElement('Button', 'submit', array(
        'label' => 'Save Changes',
        'type' => 'submit',
        'ignore' => true
    ));
  }
}
