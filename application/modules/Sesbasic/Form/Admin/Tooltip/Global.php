<?php

class Sesbasic_Form_Admin_Tooltip_Global extends Engine_Form {

  public function init() {
		$settings = Engine_Api::_()->getApi('settings', 'core');
		$this->addElement('Select','sesbasic_disable_tooltip', array(
      'label' => 'Want to disable Tooltip from your whole site',
      'multiOptions' => array('1'=>'Yes,want to disable','0'=>'No,don\'t want to disable'),
			'value' => $settings->getSetting('sesbasic.disable.tooltip', '0'),
    ));
		$this->addElement('MultiCheckbox', 'sesbasic_settings_tooltip', array(
      'label' => 'General Tooltip Settings',
      'multiOptions' => array('title'=>'Title','mainphoto'=>'Main Photo','coverphoto'=>'Cover Photo','category'=>'Category'),
			'value' => $settings->getSetting('sesbasic.settings.tooltip',  array('title','mainphoto','coverphoto','category')),
    ));
    $this->addElement('Button', 'submit', array(
				'label' => 'Save',
        'type' => 'submit',
        'ignore' => true,
        'decorators' => array('ViewHelper')
    ));
    $this->addDisplayGroup(array('submit'), 'buttons');
  }

}
