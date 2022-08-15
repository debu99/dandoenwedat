<?php
/**
 * SocialEngineSolutions
 *
 * @category   Application_Seseventreview
 * @package    Seseventreview
 * @copyright  Copyright 2015-2016 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: Add.php 2016-07-26 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */
class Seseventreview_Form_Admin_Category_Add extends Engine_Form {

  public function init() {

    $category_id = Zend_Controller_Front::getInstance()->getRequest()->getParam('id', 0);
    if ($category_id)
      $category = Engine_Api::_()->getItem('sesevent_category', $category_id);

    $this->setMethod('post');

    
    $profiletype = array();
    $topStructure = Engine_Api::_()->fields()->getFieldStructureTop('eventreview');
    if (count($topStructure) == 1 && $topStructure[0]->getChild()->type == 'profile_type') {
      $profileTypeField = $topStructure[0]->getChild();
      $options = $profileTypeField->getOptions();
      $options = $profileTypeField->getElementParams('eventreview');
      unset($options['options']['order']);
      unset($options['options']['multiOptions']['0']);
      $profiletype = $options['options']['multiOptions'];
    }
    /* $parentArray[''] = 'None';
      $categorys = Engine_Api::_()->getDbtable('categories', 'sesevent')->getCategory(array('column_name' => '*','profile_type'=>true));
      foreach ($categorys as $categoryData){
      if($categoryData->category_id == 0) {
      continue;
      }
      if($category->category_id == $categoryData->category_id)
      continue;
      $parentArray[$categoryData->category_id] = $categoryData->category_name;
      $subcategory = Engine_Api::_()->getDbtable('categories', 'sesevent')->getModuleSubcategory(array('column_name' => "*", 'category_id' => $categoryData->category_id));          foreach ($subcategory as $sub_category){
      if($category->category_id == $sub_category->category_id)
      continue;
      $parentArray[$sub_category->category_id] = '-'.$category->category_name;
      $subsubcategory = Engine_Api::_()->getDbtable('categories', 'sesevent')->getModuleSubsubcategory(array('column_name' => "*", 'category_id' => $sub_category->category_id));
      foreach ($subsubcategory as $subsub_category){
      if($category->category_id == $subsub_category->category_id)
      continue;
      $parentArray[$subsub_category->category_id] = '--'.$subsub_category->category_name;
      }
      }
      }
      $this->addElement('Select', 'parent', array(
      'label' =>'Parent',
      'allowEmpty' => true,
      'required' => false,
      'multiOptions' =>$parentArray
      )); */
		if(isset($category) && $category->category_id != 0){
			$this->addElement('Select', 'profile_type_review', array(
					'label' => 'Select Profile Type',
					'description' => 'Select a profile type to be mapped with this category.',
					'allowEmpty' => true,
					'required' => false,
					'multiOptions' => $profiletype
			));
		}
   
    
    $this->addElement('Button', 'submit', array(
        'label' => 'Add',
        'type' => 'submit',
        'ignore' => true,
        'decorators' => array('ViewHelper')
    ));

    $this->addElement('Cancel', 'cancel', array(
        'label' => 'Cancel',
        'link' => true,
        'prependText' => ' or ',
        'href' => Zend_Controller_Front::getInstance()->getRouter()->assemble(array('action' => 'index')),
        'decorators' => array(
            'ViewHelper'
        )
    ));
    $this->addDisplayGroup(array('submit', 'cancel'), 'buttons');
  }

}
