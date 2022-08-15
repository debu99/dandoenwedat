<?php

/**
 * SocialEngineSolutions
 *
 * @category   Application_Sescredit
 * @package    Sescredit
 * @copyright  Copyright 2019-2020 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: Add.php  2019-01-18 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */

class Sescredit_Form_Admin_Badge_Add extends Engine_Form {

  public function init() {

    $this->setTitle('Add a New Badge')
            ->setDescription("Here, add a new badge which will be given to the members of your website based on their earned credits.")
            ->setMethod('POST');
    $this->addElement('Text', "title", array(
        'label' => 'Title',
        'description' => "Enter the title of this badge.",
        'allowEmpty' => false,
        'required' => true,
    ));
    $this->addElement('Textarea', "description", array(
        'label' => 'Description',
        'description' => "Enter the description about this badge. [Only 300 characters supported.]",
        'maxlength' => '300',
        'filters' => array(
            'StripTags',
            new Engine_Filter_Censor(),
            new Engine_Filter_StringLength(array('max' => '300')),
            new Engine_Filter_EnableLinks(),
        ),
    ));
    $this->addElement('Text', 'credit_value', array(
        'label' => 'Credit Points Total',
        'description' => "Enter the credit points for this badge. Users will get this badge when they will earn the entered amount of total credit points.",
        'validators' => array(
            array('Int', true),
            new Engine_Validate_AtLeast(0),
        ),
    ));
    $this->addElement('File', 'photo_id', array(
        'label' => 'Badge Photo',
        'description' => "Upload a photo for this badge.",
        'allowEmpty' => false,
        'required' => true,
    ));
    $this->photo_id->addValidator('Extension', false, 'jpg,jpeg,png,gif,PNG,GIF,JPG,JPEG');

    //Add Element: Submit
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
        'onclick' => 'javascript:parent.Smoothbox.close()',
        'decorators' => array(
            'ViewHelper',
        ),
    ));
    $this->addDisplayGroup(array('submit', 'cancel'), 'buttons');
  }

}
