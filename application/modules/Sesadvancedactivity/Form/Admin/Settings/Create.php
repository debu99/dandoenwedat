<?php

/**
 * SocialEngineSolutions
 *
 * @category   Application_Sesadvancedactivity
 * @package    Sesadvancedactivity
 * @copyright  Copyright 2016-2017 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: Create.php  2017-01-12 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */

class Sesadvancedactivity_Form_Admin_Settings_Create extends Engine_Form {
  public function init() {
    $this->setTitle('Create New Filter')
            ->setDescription('Choose a module for which you want to create a filter in the feeds.');
    $id = Zend_Controller_Front::getInstance()->getRequest()->getParam('id', 0);
      $integrateothermoduleArray = array();
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
        $this->addElement('Select', 'filtertype', array(
            'label' => 'Choose Module',
            'description' => 'Below, you can choose the plugin to be integrated.',
            'allowEmpty' => false,
            'onchange'=>'setModuleName(this.options[this.selectedIndex].text);',
            'multiOptions' => $integrateothermoduleArray,
        ));
      } else {
        $description = "<div class='tip'><span>" . Zend_Registry::get('Zend_Translate')->_("Here are no module enabled to configure.") . "</span></div>";
        $this->addElement('Dummy', 'filtertype', array(
            'description' => $description,
        ));
        $this->filtertype->addDecorator('Description', array('placement' => Zend_Form_Decorator_Abstract::PREPEND, 'escape' => false));
      }
      $this->addElement('hidden','module',array('order'=>998));
      
      $this->addElement('Text', 'title', array(
        'label' => 'Filter Title',
        'description' => 'Enter the title for the filter.',
      ));     
      
      //file
      $this->addElement('File', 'file', array(
        'allowEmpty' => true,
        'required' => false,
        'label' => 'Icon',
        'description' => 'Upload a icon for this filtering option. [Note: icon with extension: "jpg, png, jpeg" only.]',
      ));
      $this->file->addValidator('Extension', false, 'jpg,png,jpeg,PNG,JPG,JPEG');
      if($id){
    $filtertable = Engine_Api::_()->getDbTable('filterlists', 'sesadvancedactivity');
      $filtertableName = $filtertable->info('name');
      $selectFilterQuery = $filtertable->select()
              ->from($filtertableName,'*')
              ->where($filtertableName.'.filterlist_id =?',$id);
       $resultsFilterArray = $selectFilterQuery->query()->fetchAll();
      if (!empty($resultsFilterArray)) {
        foreach ($resultsFilterArray as $resultFilter) {
         $file_id = $resultFilter['file_id'];
        }
      }
      $this->addElement('hidden', 'file_id', array(
      'value' =>$file_id ,
        ));
        if(!empty($file_id)){
          $storage = Engine_Api::_()->storage()->get($resultFilter['file_id'], '');
          $image = $storage->getPhotoUrl();
          $this->addElement('image', 'displayimage', array(
            'src'=>$image,
            'alt'=>'image',
            'height'=>70,
            'width'=>70,
        )); 
        $this->addElement('Checkbox', 'removeIcon', array(
      'label' => 'Check for remove icon',
      'description' => '',
      'value' => 0,
        ));
      }
    }
          $this->addElement('Checkbox', 'active', array(
        'label' => 'Yes, enable this filter.',
        'description' => '',
        'value' => 1,
           ));    
    $this->addElement('Button', 'submit', array(
      'label' => 'Save Changes',
      'type' => 'submit',
      'ignore' => true
    ));
  }
}