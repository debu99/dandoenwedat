<?php

/**
 * SocialEngineSolutions
 *
 * @category   Application_Sesadvancedcomment
 * @package    Sesadvancedcomment
 * @copyright  Copyright 2016-2017 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: AddReaction.php 2017-01-19 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */
class Sesadvancedcomment_Form_Admin_Reaction_AddReaction extends Engine_Form {

  public function init() {
  
    $this->setTitle('Add a New Reaction')
            ->setDescription('Here, you can add new reactions which will show to users when they mouse over on Like button.');
    $id = Zend_Controller_Front::getInstance()->getRequest()->getParam('id', 0);
      
    $this->addElement('Text', 'title', array(
      'label' => 'Reaction Name',
      'description' => 'Enter the name for the reaction. (This name will also come when users mouse over the reaction icon.)',
      'required'=>true,
      'allowEmpty'=>false,
      'description' => '',
    ));     

    $this->addElement('File', 'file', array(
        'allowEmpty' => $all,
        'required' => $re,
        'label' => 'Reaction Photo',
        'description' => 'Upload a photo for this reaction. [Note: photos with extension: "jpg, png, jpeg and gif" only.]',
    ));
    $this->file->addValidator('Extension', false, 'jpg,png,jpeg,gif,GIF,PNG,JPG,JPEG');
    
    
    
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