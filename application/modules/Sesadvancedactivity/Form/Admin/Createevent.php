<?php

/**
 * SocialEngineSolutions
 *
 * @category   Application_Sesadvancedactivity
 * @package    Sesadvancedactivity
 * @copyright  Copyright 2016-2017 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: Createevent.php  2017-01-12 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */

class Sesadvancedactivity_Form_Admin_Createevent extends Engine_Form {
  public function init() {
    $settings = Engine_Api::_()->getApi('settings', 'core');
    $this
            ->setTitle('Create New Custom Notification')
            ->setDescription('');
    $view = Zend_Registry::isRegistered('Zend_View') ? Zend_Registry::get('Zend_View') : null;
    // Add submit button
     $this->addElement('Text', 'title', array(
        'label' => 'Title',
        'value'=>'',
    ));
		$this->addElement('Textarea', 'description', array(
          'label' => 'Notification Description',
					'value'=>'',
    ));
    // Start time
    $start = new Engine_Form_Element_CalendarDateTime('date');
    $start->setLabel("Display Date");
    $start->setDescription("Choose the display date for this custom notification.");
    $start->setAllowEmpty(false);
    $this->addElement($start);
    
    $this->addElement('Radio', 'recurring', array(
        'label' => 'Repeat Display',
        'description'=>'Do you want to repeat the display of this custom notification on same date of each month?',
        'multiOptions'=>array('1'=>'Yes','0'=>'No'),
        'value'=>'1',
    ));
    
    $this->addElement('Radio', 'visibility', array(
        'label' => 'Visibility Duration',
        'description'=>'Choose the duration for which this notification will be visible to members in their feeds on member home page.',
        'multiOptions' => array(
          '1' => 'Full Day (notification will display for 24 hours from its creation time.)', 
          '2'=> '1 Time (notification will display until the member view it 1 time.)',
          '3'=> '2 Times (notification will display until the member view it 2 times.)',
          '4' => 'Upto Time Chosen (choose start and end date for the display of this notification.)',
        ),
        'onclick' => 'choosedate(this.value)',
        'value'=>'2',
    ));
    
    $starttime = new Engine_Form_Element_CalendarDateTime('starttime');
    $starttime->setLabel("Start Date");
    $starttime->setAllowEmpty(true);
    $starttime->setRequired(false);
    $starttime->setValue(date("Y-m-d"));
    $this->addElement($starttime);

    $endtime = new Engine_Form_Element_CalendarDateTime('endtime');
    $endtime->setLabel("End Date");
    $endtime->setAllowEmpty(true);
    $endtime->setRequired(false);
    $endtime->setValue(date("Y-m-d",time() + 86400));
    $this->addElement($endtime);
    
		$id = Zend_Controller_Front::getInstance()->getRequest()->getParam('id', 0);
    if ($id)
      $event = Engine_Api::_()->getItem('sesadvancedactivity_event', $id);
		
		$this->addElement('File', 'file', array(
        'label' => 'Upload Image',
        'description' => ''
    ));
    $this->file->addValidator('Extension', false, 'jpg,jpeg,png,gif,PNG,GIF,JPG,JPEG');
		if (isset($event) && $event->file_id) {
      $img_path = Engine_Api::_()->storage()->get($event->file_id, '')->getPhotoUrl();
			if(strpos($img_path,'http')  === FALSE ){
      	$path = 'http://' . $_SERVER['HTTP_HOST'] . $img_path;
			}else
				$path = $img_path;
      if (isset($path) && !empty($path)) {
        $this->addElement('Image', 'event_image_preview', array(
            'src' => $path,
            'width' => 100,
            'height' => 100,
        ));
      }
    }
   $this->addElement('Button', 'submit', array(
        'type' => 'submit',
				'label' => 'Create',
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
