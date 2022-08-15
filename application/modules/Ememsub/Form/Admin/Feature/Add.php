<?php

 /**
 * socialnetworking.solutions
 *
 * @category   Application_Modules
 * @package    Ememsub
 * @copyright  Copyright 2014-2020 Ahead WebSoft Technologies Pvt. Ltd.
 * @license    https://socialnetworking.solutions/license/
 * @version    $Id: Add.php 2020-01-17 00:00:00 socialnetworking.solutions $
 * @author     socialnetworking.solutions
 */

class Ememsub_Form_Admin_Feature_Add extends Engine_Form {

  public function init() {
    $settings = Engine_Api::_()->getApi('settings', 'core');
    $this->setTitle('Manage Feature Rows')
            ->setDescription('From here, you can manage content for all the feature rows for subscription plans on your website.');
    $featureIdentity = Zend_Controller_Front::getInstance()->getRequest()->getParam('feature_id', 0);
    if($featureIdentity){
      $feature = Engine_Api::_()->getDbtable('features', 'ememsub')->find($featureIdentity)->current();
    }
    $this->addElement('Dummy', 'row_content', array(
        'label' => "Row Content",
    ));
    $this->addElement('Dummy', 'expand_all', array(
        'description' => "<a href='javascript:void(0);' onclick=\"showAllOption()\">Expand all Rows</a>",
    ));
    $this->expand_all->addDecorator('Description', array('placement' => Zend_Form_Decorator_Abstract::PREPEND, 'escape' => false));
    $tabs_count = array();
    $rowCount = $settings->getSetting('ememsub.table.row',4);
    for ($i = 1; $i <= $rowCount; $i++) {
      $tabs_count[] = $i;
    }
    $localeObject = Zend_Registry::get('Locale');
    $languages = Zend_Locale::getTranslationList('language', $localeObject);
    $defaultLanguage = Engine_Api::_()->getApi('settings', 'core')->getSetting('core.locale.locale', 'en');
    $translate = Zend_Registry::get('Zend_Translate');
    $languageList = $translate->getList();
    $group = array();
    foreach ($tabs_count as $tab) {
      $id = "row".$tab;
      $labelName = '';
      $dummyid = $id . "_tabshowhide";
      $this->addElement('Dummy', $dummyid, array(
          'description' => "<a href='javascript:void(0);' onclick=\"showMoreOption('$dummyid', '', '')\" class=\"wrap\">Row $tab </a>",
      ));
      $this->$dummyid->addDecorator('Description', array('placement' => Zend_Form_Decorator_Abstract::PREPEND, 'escape' => false));
      $this->addElement('Text', $id . '_text', array(
          'label' => "Row $tab Content",
          'description' => "Enter the content for row $tab ",
          'class' => 'text_row',
      ));
      $this->addElement('Textarea', $id . '_description', array(
          'label' => "Row $tab Hint",
          'description' => "Enter the hint text for row $tab . [A question-mark icon will be shown to display this text on mouse-over of the icon.]",
          'maxlength' => '120',
          'class' => 'text_row',
      ));
    }
    $uploadImanges = array();
    foreach ($tabs_count as $tab) {
      $this->addElement('Dummy', 'icon_upload', array(
          'description' => "<a href='javascript:void(0);' onclick=\"showIconOption('')\" class=\"file-wrap\">Upload Icon For Rows</a>",
      ));
      $this->icon_upload->addDecorator('Description', array('placement' => Zend_Form_Decorator_Abstract::PREPEND, 'escape' => false));

      $iconId = 'row' . $tab . '_file_id';
      $previewId = 'row' . $tab . '_icon_preview';
      $this->addElement('File', $iconId, array(
          'label' => "Icon for Row $tab",
          'description' => "Upload an icon for row $tab. (Recommended dimensions of the icon are 16x16 px.)",
          'onchange' => "showReadImage(this,'$previewId')",
          'class' => 'upload_icon_row',
      ));
      $this->$iconId->addValidator('Extension', false, 'jpg,jpeg,png,gif,PNG,GIF,JPG,JPEG');
      if(!empty($feature)){
        if (isset($feature->$iconId) && $feature->$iconId) {
          $img_path = Engine_Api::_()->storage()->get($feature->$iconId, '')->getPhotoUrl();
          $path = 'http://' . $_SERVER['HTTP_HOST'] . $img_path;
          if (isset($path) && !empty($path)) {
            $this->addElement('Image', $previewId, array(
                'src' => $path,
                'class' => 'preview_icon_row',
            ));
          }
          $this->addElement('Checkbox', 'remove_row' . $tab . '_icon', array(
              'label' => 'Yes, delete this column icon.',
              'class' => 'remove_icon_row',
          ));
        } else {
          $this->addElement('Image', $previewId, array(
              'label' => "Preview Icon Preview for Row $tab",
              'width' => 16,
              'height' => 16,
              'disable' => true
          ));
        }
      }
    }
    // Add submit button
    $this->addElement('Button', 'submit', array(
        'label' => 'Save Changes',
        'type' => 'submit',
        'ignore' => true
    ));
   
  }
}
