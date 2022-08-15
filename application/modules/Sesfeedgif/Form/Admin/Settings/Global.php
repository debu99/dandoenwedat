<?php

/**
 * SocialEngineSolutions
 *
 * @category   Application_Sesfeedgif
 * @package    Sesfeedgif
 * @copyright  Copyright 2017-2018 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: Global.php  2017-12-06 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */

class Sesfeedgif_Form_Admin_Settings_Global extends Engine_Form {

  public function init() {

    $settings = Engine_Api::_()->getApi('settings', 'core');
    $this
            ->setTitle('Global Settings')
            ->setDescription('These settings affect all members in your community.');

		$view = Zend_Registry::isRegistered('Zend_View') ? Zend_Registry::get('Zend_View') : null;

    if ($settings->getSetting('sesfeedgif.pluginactivated')) {

      $this->addElement('Text', "sesfeedgif_giphyapi", array(
        'label' => 'GIPHY API Key',
        'description' => "Enter the GIPHY API key. <a target='_blank' href='https://developers.giphy.com/docs/api#quick-start-guide'>Click Here</a> to get the guidelines on how to create the key. If you already know, then simply <a target='_blank' href='https://developers.giphy.com'>get started</a>.",
        'allowEmpty' => false,
        'required' => true,
        'value' => $settings->getSetting('sesfeedgif.giphyapi', ''),
      ));
      $this->getElement('sesfeedgif_giphyapi')->getDecorator('Description')->setOptions(array('placement' => 'PREPEND', 'escape' => false));


      // Add submit button
      $this->addElement('Button', 'submit', array(
          'label' => 'Save Changes',
          'type' => 'submit',
          'ignore' => true
      ));
    } else {
      //Add submit button
      $this->addElement('Button', 'submit', array(
          'label' => 'Activate your plugin',
          'type' => 'submit',
          'ignore' => true
      ));
    }
  }

}
