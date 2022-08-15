<?php
/**
 * SocialEngineSolutions
 *
 * @category   Application_Sesevent
 * @package    Sesevent
 * @copyright  Copyright 2015-2016 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: Edit.php 2016-07-26 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */
class Sesevent_Form_Album_Edit extends Engine_Form {
	
  public function init() {
    $user = Engine_Api::_()->user()->getViewer();
    $user_level = Engine_Api::_()->user()->getViewer()->level_id;
    $this->setTitle('Edit Album Settings')
            ->setAttrib('name', 'albums_edit');
    $this->addElement('Text', 'title', array(
        'label' => 'Album Title',
        'required' => true,
        'notEmpty' => true,
        'validators' => array(
            'NotEmpty',
        ),
        'filters' => array(
            new Engine_Filter_Censor(),
            'StripTags',
            new Engine_Filter_StringLength(array('max' => '63'))
        )
    ));
    $this->title->getValidator('NotEmpty')->setMessage("Please specify an album title");
		
    $this->addElement('Textarea', 'description', array(
        'label' => 'Album Description',
        'rows' => 2,
        'filters' => array(
            new Engine_Filter_Censor(),
            'StripTags',
            new Engine_Filter_EnableLinks(),
        )
    ));
   
    // Submit or succumb!
    $this->addElement('Button', 'submit', array(
        'label' => 'Save Album',
        'type' => 'submit',
        'decorators' => array(
            'ViewHelper'
        )
    ));
    $this->addElement('Cancel', 'cancel', array(
        'label' => 'cancel',
        'link' => true,
        'prependText' => ' or ',
        'decorators' => array(
            'ViewHelper'
        )
    ));
    $this->addDisplayGroup(array('submit', 'cancel'), 'buttons');
  }
}