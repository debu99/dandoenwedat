<?php
/**
 * SocialEngineSolutions
 *
 * @category   Application_Sesevent
 * @package    Sesevent
 * @copyright  Copyright 2015-2016 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: Contactinformation.php 2016-07-26 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */
class Sesevent_Form_Dashboard_Contactinformation extends Engine_Form {
	 public function init() {
			$this->setTitle('Event Contact Information')
					->setAttrib('id', 'sesevent_ajax_form_submit')
					->setMethod("POST")
					->setAction(Zend_Controller_Front::getInstance()->getRouter()->assemble(array()));		
		// Event Contact Name
    $this->addElement('Text', 'event_contact_name', array(
        'label' => 'Event Contact Name',
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
    $this->addElement('Text', 'event_contact_email', array(
        'label' => 'Event Contact Email',
    ));
		// Event Contact Phone
    $this->addElement('Text', 'event_contact_phone', array(
        'label' => 'Event Contact Phone',
    ));
		// Event Contact Facebook
    $this->addElement('Text', 'event_contact_facebook', array(
        'label' => 'Event Contact Facebook URL',
    ));
		// Event Contact Linkedin
    $this->addElement('Text', 'event_contact_linkedin', array(
        'label' => 'Event Contact Linkedin URL',
    ));
			// Event Contact twitter
    $this->addElement('Text', 'event_contact_twitter', array(
        'label' => 'Event Contact Twitter URL',
    ));
			// Event Contact Website
    $this->addElement('Text', 'event_contact_website', array(
        'label' => 'Event Contact Website URL',
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