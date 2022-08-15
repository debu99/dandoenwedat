<?php

/**
 * SocialEngineSolutions
 *
 * @category   Application_Sesfeedbg
 * @package    Sesfeedbg
 * @copyright  Copyright 2017-2018 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: Create.php  2017-08-28 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */

class Sesfeedbg_Form_Admin_Background_Create extends Engine_Form {

  public function init() {
  
    $this->setTitle('Upload New Background Image')
            ->setDescription('');
            
    $id = Zend_Controller_Front::getInstance()->getRequest()->getParam('id', 0);

    if(!$id){
      $re = true;
      $all = false;  
    } else{
      $re = false;
      $all = true;
    }
    $this->addElement('File', 'file', array(
        'allowEmpty' => $all,
        'required' => $re,
        'label' => 'Choose Image',
        'description' => 'Below, choose a background image. [Note: photos with extension: "jpg, png and jpeg" only.]',
        'accept'=>"image/*",
        'onchange' => 'validFileSize(this.value)',
    ));
    $this->file->addValidator('Extension', false, 'jpg,png,jpeg,PNG,JPG,JPEG');

    $start = new Engine_Form_Element_CalendarDateTime('starttime');
    $start->setLabel("Choose Start Date");
    $start->setDescription("Choose a start date for this background image.");
    $start->setAllowEmpty(false);
    $start->setRequired(true);
    $this->addElement($start);
    
    $this->addElement('Radio', 'enableenddate', array(
      'label' => ' Enable End Date',
      'description' => 'Do you want to enable an end date for this background image?',
      'multiOptions' => array(
          '1' => 'Yes',
          '0' => 'No',
      ),
      'onclick' => 'enableenddatse(this.value)',
      'value' => 0,
    ));

    $end = new Engine_Form_Element_CalendarDateTime('endtime');
    $end->setLabel("Choose End Date");
    $end->setDescription('Choose an end date for this background image. (Works only if you have choose Yes above for "Enable End Date" field.)');
    //$end->setRequired(true);
    //$end->setAllowEmpty(false);
    $end->setValue(date("Y-m-d",time() + 86400)); 
    $this->addElement($end);
    
    $this->addElement('Button', 'submit', array(
      'label' => 'Upload',
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