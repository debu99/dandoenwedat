<?php
/**
 * SocialEngineSolutions
 *
 * @category   Application_Sesevent
 * @package    Sesevent
 * @copyright  Copyright 2015-2016 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: Create.php 2016-07-26 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */
class Sesevent_Form_Sponsorship_Create extends Engine_Form {


  public function init() {
		$item_id = Zend_Controller_Front::getInstance()->getRequest()->getParam('id');
		if($item_id){
			 $item = Engine_Api::_()->getItem('sesevent_sponsorship', $item_id);
			$title = 'Edit Sponsorship';
		}else{
			$item = false;
			$title = 'Create New Sponsorship';
		}
    
    $user = Engine_Api::_()->user()->getViewer();
    $this->setTitle($title)
            ->setAttrib('id', 'sesevent_ajax_form_submit')
            ->setMethod("POST")
            ->setAction(Zend_Controller_Front::getInstance()->getRouter()->assemble(array()));
    // Title
    $this->addElement('Text', 'title', array(
        'label' => 'Sponsorship Title',
        'autocomplete' => 'off',
        'allowEmpty' => false,
        'required' => true,
        'validators' => array(
            array('NotEmpty', true),
            array('StringLength', false, array(1, 255)),
        ),
        'filters' => array(
            'StripTags',
            new Engine_Filter_Censor(),
        ),
    ));
		
    // Description
    $this->addElement('Textarea', 'description', array(
        'label' => 'Sponsorship Description',
        'maxlength' => '10000',
        'filters' => array(
            'StripTags',
            new Engine_Filter_Censor(),
            new Engine_Filter_EnableLinks(),
            new Engine_Filter_StringLength(array('max' => 10000)),
        ),
    ));
		$defaultCurrency = Engine_Api::_()->sesevent()->defaultCurrency();
		//Event Currency
		$this->addElement('Select', 'currency', array(
		 		'label'=>'Currency',
        'multiOptions' => array($defaultCurrency=>$defaultCurrency),
    ));
		// price
    $this->addElement('Text', 'price', array(
        'label' => 'Sponsorship Amount',
        'autocomplete' => 'off',
        'allowEmpty' => false,
        'required' => true,
        'validators' => array(
            array('NotEmpty', false),
        ),
        'filters' => array(
            'StripTags',
            new Engine_Filter_Censor(),
        ),
    ));
		// Total Quantity
    $this->addElement('Text', 'total', array(
        'label' => 'Total Quantity',
				'allowEmpty' => false,
        'required' => true,
    ));
    //Main Photo
    $this->addElement('File', 'photo', array(
        'label' => 'Sponsorship Photo'
    ));
		$this->photo->addValidator('Extension', false, 'jpg,png,gif,jpeg');
		if ($item && $item->photo_id) {
      $img_path = Engine_Api::_()->storage()->get($item->photo_id, '')->getPhotoUrl();
      $path = 'http://' . $_SERVER['HTTP_HOST'] . $img_path;
      if (isset($path) && !empty($path)) {
        $this->addElement('Image', 'thumbnail_photo_preview', array(
            'src' => $path,
						'style'=>'height:100px;width:100px;',
            'class' => 'sesevent_sponsorship_thumb_preview sesbd',
        ));
      }
			$this->addElement('Checkbox', 'remove_sponsorship_photo', array(
					'label' => 'Yes, remove sponsorship photo.'
			));
    }
    $this->addElement('Select', 'status', array(
        'label' => 'Draft',
        'description' => 'draft/publish?',
        'multiOptions' => array('0' => 'Draft', '1' => 'Publish'),
        'value' => 1
    ));
    // Buttons
    $this->addElement('Button', 'submit', array(
        'label' => 'Save Changes',
        'type' => 'submit',
        'ignore' => true,
        'decorators' => array(
            'ViewHelper',
        ),
    ));
    $this->addElement('Cancel', 'cancel', array(
        'label' => 'cancel',
        'link' => true,
				'onclick'=>'javascript:manageSponsorship();return false;',
        'prependText' => ' or ',
        'decorators' => array(
            'ViewHelper',
        ),
    ));
    $this->addDisplayGroup(array('submit', 'cancel'), 'buttons', array(
        'decorators' => array(
            'FormElements',
            'DivDivDivWrapper',
        ),
    ));
  }

}
