<?php

/**
 * SocialEngineSolutions
 *
 * @category   Application_Sesbasic
 * @package    Sesbasic
 * @copyright  Copyright 2015-2016 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: Global.php 2015-07-25 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */
class Sesbasic_Form_Admin_Settings_Global extends Engine_Form
{

  public function init()
  {

    $this->setTitle('Global Settings')
      ->setDescription('These settings affect all members in your community.');
    $guidlines = 'Enable below APIs:
1. Google Maps JavaScript API
2. Google Maps Embed API
3. Google Static Maps API
4. Google Places API Web Service
5. Google Maps Directions API
6. Google Maps Geolocation API';

    $coreSetting = Engine_Api::_()->getApi('settings', 'core');

    $this->addElement('Radio', "enableglocation", array(
      'label' => 'Allow Google Location',
      'description' => 'Do you want enable google location on your website?',
      'allowEmpty' => true,
      'required' => false,
      'multiOptions' => array(
        1 => 'Yes',
        0 => 'No'
      ),
      'onchange' => 'enablelocation(this.value);',
      'value' => Engine_Api::_()->getApi('settings', 'core')->getSetting('enableglocation', '1'),
    ));


    $this->addElement('Text', "ses_mapApiKey", array(
      'label' => 'Google Map API Key',
      'description' => 'Enter the Google map API key for displaying Google map on your website.<a href="https://console.developers.google.com/project" target="_blank">Click Here</a> to generate the API key.' . '<a href="javascript:;" class="sesbasic_form_help_icon" title="' . $guidlines . '"><img onclick="showPopUp();" src="application/modules/Sesbasic/externals/images/icons/question.png" alt="Question" /></a>',
      'allowEmpty' => true,
      'required' => false,
      'value' => $coreSetting->getSetting('ses.mapApiKey', ''),
    ));
    $this->ses_mapApiKey->addDecorator('Description', array('placement' => Zend_Form_Decorator_Abstract::PREPEND, 'escape' => false));

    $this->addElement('MultiCheckbox', 'optionsenableglotion', array(
      'label' => 'Location Fields',
      'description' => 'Choose from the below fields which you want to enable for the location on your website. (This setting will apply on create, edit and search form only.)',
      'multiOptions' => array(
        'country' => 'Country',
        'state' => 'State',
        'city' => 'City',
        'zip' => 'Zip Code',
        'lat' => 'Latitude',
        'lng' => 'Longitude',
      ),
      'value' => unserialize(Engine_Api::_()->getApi('settings', 'core')->getSetting('optionsenableglotion', 'a:6:{i:0;s:7:"country";i:1;s:5:"state";i:2;s:4:"city";i:3;s:3:"zip";i:4;s:3:"lat";i:5;s:3:"lng";}')),
    ));

    $this->addElement('Text', "ses_addthis", array(
      'label' => 'Add This Publisher Id',
      'description' => 'Enter the Add This Publisher Id for displaying Add This Widget on your website.<a href="https://www.addthis.com/dashboard" target="_blank">Click Here</a> to generate the Publisher Id.',
      'allowEmpty' => true,
      'required' => false,
      'value' => $coreSetting->getSetting('ses.addthis', ''),
    ));
    $this->ses_addthis->addDecorator('Description', array('placement' => Zend_Form_Decorator_Abstract::PREPEND, 'escape' => false));

    //check ses video and album
    $sesalbum_enable_module = Engine_Api::_()->getApi('core', 'sesbasic')->isModuleEnable(array('sesalbum'));
    $sesvideo_enable_module = Engine_Api::_()->getApi('core', 'sesbasic')->isModuleEnable(array('sesvideo'));
    $seslivestream_enable_module = Engine_Api::_()->getApi('core', 'sesbasic')->isModuleEnable(array('seslivestream'));
    if ($sesalbum_enable_module || $sesvideo_enable_module || $seslivestream_enable_module) {
      $this->addElement('Select', "ses_allow_adult_filtering", array(
        'label' => 'Allow Adult Filtering',
        'description' => 'Do you want member on your website mark content adult in "Advanced Photos & Albums Plugin","Video & Audio Live Streaming" & "Advanced Videos & Channels Plugin"?',
        'allowEmpty' => true,
        'required' => false,
        'multiOptions' => array('1' => 'Yes, allow adult filtering', '0' => 'No, do not allow adult filtering'),
        'value' => $coreSetting->getSetting('ses.allow.adult.filtering', '0'),
      ));
      $this->ses_allow_adult_filtering->addDecorator('Description', array('placement' => Zend_Form_Decorator_Abstract::PREPEND, 'escape' => false));
    }
    $sesproduct_enable_module = Engine_Api::_()->getDbTable('modules', 'core')->isModuleEnabled('sesproduct');
    $courses_enable_module = Engine_Api::_()->getDbTable('modules', 'core')->isModuleEnabled('courses');
    if ($sesproduct_enable_module && $courses_enable_module) {
      $this->addElement('Radio', 'site_enble_singlecart', array(
        'label' => 'Enable Single Cart',
        'description' => 'Do you want to enable single Cart for your website?
          [ If this setting Yes, then there will be a single cart for all the payment related plugins and will display with separate checkout and delete button for each item/product/course and when selected then there will display more than 1 Cart.]',
        'multiOptions' => array(1 => 'Yes', 0 => 'No'),
        'value' => $coreSetting->getSetting('site.enble.singlecart', 0),
      ));
    }
    // Add submit button
    $this->addElement('Button', 'submit', array(
      'label' => 'Save Changes',
      'type' => 'submit',
      'ignore' => true
    ));
  }
}
