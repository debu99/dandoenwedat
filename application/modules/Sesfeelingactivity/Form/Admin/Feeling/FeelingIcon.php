<?php

/**
 * SocialEngineSolutions
 *
 * @category   Application_Sesfeelingactivity
 * @package    Sesfeelingactivity
 * @copyright  Copyright 2017-2018 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: FeelingIcon.php  2017-08-28 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */

class Sesfeelingactivity_Form_Admin_Feeling_FeelingIcon extends Engine_Form {

  public function init() {
  
  
    $type = Zend_Controller_Front::getInstance()->getRequest()->getParam('type', 1);
    
    if($type == 1) { 
      $this->setTitle('Add Feeling/Activity List Item')
              ->setDescription('');
    } else {
      $this->setTitle('Add Modules for Feeling/Activity')
              ->setDescription('');
    }
    
    $id = Zend_Controller_Front::getInstance()->getRequest()->getParam('id', 0);
    
    $type = Zend_Controller_Front::getInstance()->getRequest()->getParam('type', 1);
    
    $feeling_id = Zend_Controller_Front::getInstance()->getRequest()->getParam('feeling_id', 0);
    
    if($type == 1) {
    $this->addElement('Text', 'title', array(
      'label' => 'Feeling/Activity Title',
      'required' => true,
      'allowEmpty' => false,
      'description' => '',
    ));
    
    if(!$id){
      $re = true;
      $all = false;  
    }else{
      $re = false;
      $all = true;
    }
    $this->addElement('File', 'file', array(
        'allowEmpty' => $all,
        'required' => $re,
        'label' => 'Feeling/Activity Icon',
        'description' => 'Upload a feeling/activity icon [Note: Icons with extension: "jpg, png, jpeg and gif" only. Recommended dimension is 32*32 px.]',
    ));
    $this->file->addValidator('Extension', false, 'jpg,png,jpeg,gif,GIF,PNG,JPG,JPEG');

    
    } elseif($type == 2) {
    
      $this->addElement('Text', 'title', array(
        'label' => 'Title (This is for indicative purpose in the admin panel only.)',
        'required' => true,
        'allowEmpty' => false,
        'description' => '',
      ));
      
      $integrateothermoduleId = $id;
      if (!$integrateothermoduleId) {
        $integrateothermoduleItem = array();
        $integrateothermoduleArray = array();
        $integrateothermoduleArray[] = '';
        //get all enabled modules
        $coreTable = Engine_Api::_()->getDbTable('modules', 'core');
        $select = $coreTable->select()
                ->from($coreTable->info('name'), array('name', 'title'))
                ->where('enabled =?', 1)
                ->where('type =?', 'extra');
        $resultsArray = $select->query()->fetchAll();
        if (!empty($resultsArray)) {
          foreach ($resultsArray as $result) {
            $integrateothermoduleArray[$result['name']] = $result['title'];
          }
        }
  
        if (!empty($integrateothermoduleArray)) {
          $this->addElement('Select', 'module_name', array(
              'label' => 'Choose Module',
              'description' => 'Choose a module belonging to which content will be shown in the auto-suggest box while updating status.',
              'allowEmpty' => false,
              'onchange' => 'changemodule(this.value)',
              'multiOptions' => $integrateothermoduleArray,
          ));
        } else {
          $description = "<div class='tip'><span>" . Zend_Registry::get('Zend_Translate')->_("Here are no module to configure with our plugin lightbox.") . "</span></div>";
          $this->addElement('Dummy', 'module', array(
              'description' => $description,
          ));
          $this->module->addDecorator('Description', array('placement' => Zend_Form_Decorator_Abstract::PREPEND, 'escape' => false));
        }
        
        $module = Zend_Controller_Front::getInstance()->getRequest()->getParam('module_name', null);
        if (!empty($module)) {
          $this->module_name->setValue($module);
          //get manifest item for given module
          $integrateothermodule = Engine_Api::_()->sesfeelingactivity()->getPluginItem($module);
          if (empty($integrateothermodule))
            $this->addElement('Dummy', 'dummy_title', array(
                'description' => 'No item type define for this plugin.',
            ));
        }
      }
      
      $param = false;
      if ($integrateothermoduleId)
        $param = true;
      elseif (@$integrateothermodule)
        $param = true;
        
      if ($param) {
        if (!$integrateothermoduleId) {
          $this->addElement('Select', 'resource_type', array(
              'label' => 'Item Type of Selected Module',
              'description' => 'Select the item type for selected module which is defined in its manifest.php file. You can also add more than 1 item type for a module, but only 1 at a time. For adding other item type, repeat the process of adding a module.',
              'multiOptions' => @$integrateothermodule,
          ));
        }
      }

    }
    
    $this->addElement('Button', 'submit', array(
      'label' => 'Save Changes',
      'type' => 'submit',
      'ignore' => true,
      'decorators' => array('ViewHelper')
    ));
    $this->addElement('Cancel', 'cancel', array(
        'label' => 'Cancel',
        'link' => true,
        'prependText' => ' or ',
        'onclick' => 'javascript:parent.Smoothbox.close()',
        'decorators' => array(
            'ViewHelper',
        ),
    ));
    $this->addDisplayGroup(array('submit', 'cancel'), 'buttons');
  }
}