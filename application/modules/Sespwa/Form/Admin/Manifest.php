<?php

/**
 * SocialEngineSolutions
 *
 * @category   Application_Sespwa
 * @package    Sespwa
 * @copyright  Copyright 2018-2019 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: Manifest.php  2018-11-24 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */

class Sespwa_Form_Admin_Manifest extends Engine_Form {

  public function init() {
      $headScript = new Zend_View_Helper_HeadScript();
      $headScript->appendFile(Zend_Registry::get('StaticBaseUrl') . 'application/modules/Sesbasic/externals/scripts/jscolor/jscolor.js');
      $headScript->appendFile(Zend_Registry::get('StaticBaseUrl') . 'application/modules/Sesbasic/externals/scripts/jquery.min.js');

      $settings = Engine_Api::_()->getApi('settings', 'core');
    $this
        ->setTitle('Manifest Settings')
        ->setDescription('From below form you can create manifest file for your website.');

        $this->addElement('Text', "appname", array(
            'label' => 'App Name',
            'allowEmpty' => false,
            'required' => true,
        ));
      $this->addElement('Text', "shotname", array(
          'label' => 'Short Name',
          'allowEmpty' => false,
          'required' => true,
      ));
      $this->addElement('Textarea', "description", array(
          'label' => 'App Description',
          'allowEmpty' => false,
          'required' => true,
      ));
      $this->addElement('Text', "themecolor", array(
          'label' => 'App Theme Color',
          'allowEmpty' => false,
          'class'=>'sescolor',
          'required' => true,
          'value' =>  '#fffff',
      ));
      $this->addElement('Text', "backgroundcolor", array(
          'label' => 'App Background Color',
          'allowEmpty' => false,
          'class'=>'sescolor',
          'required' => true,
          'value' => '#fffff',
      ));

      $this->addElement('File', 'photo', array(
          'label' => 'App Icon',
		  'description' => 'Upload an icon for the PWA app of your website. Note: The support extension is .png only.',
      ));
      $this->photo->addValidator('Extension', false, 'png');

      // Add submit button
        $this->addElement('Button', 'submit', array(
            'label' => 'Save Changes',
            'type' => 'submit',
            'ignore' => true
        ));

  }

}
