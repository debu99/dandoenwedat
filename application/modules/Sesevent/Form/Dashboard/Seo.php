<?php
/**
 * SocialEngineSolutions
 *
 * @category   Application_Sesevent
 * @package    Sesevent
 * @copyright  Copyright 2015-2016 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: Seo.php 2016-07-26 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */
class Sesevent_Form_Dashboard_Seo extends Engine_Form {
	 public function init() {
			$this->setTitle('Add Seo')
					->setAttrib('id', 'sesevent_ajax_form_submit')
					->setMethod("POST")
					->setAction(Zend_Controller_Front::getInstance()->getRouter()->assemble(array()));		
		// Event Contact Name
    $this->addElement('Text', 'seo_title', array(
        'label' => 'Event Seo Title',
        'allowEmpty' => false,
        'required' => true,
        'validators' => array(
            array('NotEmpty', true),
            array('StringLength', false, array(1, 64)),
        ),
        'filters' => array(
            'StripTags',
            new Engine_Filter_Censor(),
        ),
    ));
		// Event Contact Email
    $this->addElement('Text', 'seo_keywords', array(
				'description'=>'Enter list of keywords seperated by a comma (,)',
        'label' => 'Event Seo Keywords',
    ));
		// Event Contact Phone
    $this->addElement('Textarea', 'seo_description', array(
        'label' => 'Event Seo Description',
    ));
		
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