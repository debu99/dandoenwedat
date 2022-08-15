<?php
/**
 * SocialEngineSolutions
 *
 * @category   Application_Sesevent
 * @package    Sesevent
 * @copyright  Copyright 2015-2016 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: Printticketinfo.php 2016-07-26 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */
class Sesevent_Form_Dashboard_Printticketinfo extends Engine_Form {


  public function init() {
		$item_id = Zend_Controller_Front::getInstance()->getRequest()->getParam('id');
		$title = 'Additional Details on Print';
		
    $item = Engine_Api::_()->core()->getSubject();
    $user = Engine_Api::_()->user()->getViewer();
    $this->setTitle($title)
		->setDescription('Below, you can enter the additional details to be printed on the ticket.')
            ->setAttrib('id', 'sesevent_ajax_form_submit')
            ->setMethod("POST")
            ->setAction(Zend_Controller_Front::getInstance()->getRouter()->assemble(array()));
		
    // Description
    $this->addElement('Textarea', 'ticket_description', array(
        'label' => 'Description',
        'maxlength' => '10000',
        'filters' => array(
            'StripTags',
            new Engine_Filter_Censor(),
            new Engine_Filter_EnableLinks(),
            new Engine_Filter_StringLength(array('max' => 10000)),
        ),
    ));
		 // Title
    $this->addElement('Text', 'logo_description', array(
        'label' => 'Ticket',
        'autocomplete' => 'off',
        'allowEmpty' => true,
        'required' => false,
        'validators' => array(
            array('NotEmpty', true),
            array('StringLength', false, array(1, 255)),
        ),
        'filters' => array(
            'StripTags',
            new Engine_Filter_Censor(),
        ),
    ));
		//Main Photo
    $this->addElement('File', 'logo', array(
        'label' => 'Logo'
    ));
		$this->logo->addValidator('Extension', false, 'jpg,png,gif,jpeg');
		if ($item && $item->ticket_logo) {
      $img_path = Engine_Api::_()->storage()->get($item->ticket_logo, '')->getPhotoUrl();
       if(strpos($img_path,'http') === FALSE)
				$path = 'http://' . $_SERVER['HTTP_HOST'] . $img_path;
			 else
				$path = $img_path;
      if (isset($path) && !empty($path)) {
        $this->addElement('Image', 'thumbnail_photo_preview', array(
            'src' => $path,
						'style'=>'height:100px;width:100px;',
            'class' => 'sesevent_logo_thumb_preview sesbd',
        ));
      }
			$this->addElement('Checkbox', 'remove', array(
					'label' => 'Yes, remove logo.'
			));
    }
   
    // Buttons
    $this->addElement('Button', 'submit', array(
        'label' => 'Save Changes',
        'type' => 'submit',
        'ignore' => true,
        'decorators' => array(
            'ViewHelper',
        ),
    ));
    $this->addDisplayGroup(array('submit'), 'buttons', array(
        'decorators' => array(
            'FormElements',
            'DivDivDivWrapper',
        ),
    ));
  }

}
