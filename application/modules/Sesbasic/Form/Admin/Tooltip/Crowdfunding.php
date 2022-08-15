<?php
class Sesbasic_Form_Admin_Tooltip_Crowdfunding extends Engine_Form {

  public function init() {

		$settings = Engine_Api::_()->getApi('settings', 'core');
    $this->addElement('MultiCheckbox', 'crowdfunding_settings_tooltip', array(
        'label' => 'General Tooltip Settings',
        'required'=>true,
        'empty'=>false,
        'multiOptions' => array('title'=>'Title','ownerhoto'=>'Owner Photo','coverphoto'=>'Cover Photo','category'=>'Category','socialshare'=>'Social Share','ownername'=>'Posted By','stats'=>'Statistics', 'des' => "Description", 'likebutton' => "Like Button"),
        'value' => $settings->getSetting('sesevent.event.settings.tooltip', array('title','ownerhoto','coverphoto','category','socialshare','ownername', 'stats')),
    ));

    //Social Share Plugin work
    if(Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled('sessocialshare')) {

      $this->addElement('Select', "socialshare_enable_plusiconevent", array(
        'label' => "Enable More Icon for social share buttons?",
          'multiOptions' => array(
          '1' => 'Yes',
          '0' => 'No',
        ),
        'value' => $settings->getSetting('socialshare.enable.plusiconevent', 1),
      ));

      $this->addElement('Text', "socialshare_icon_limitevent", array(
          'label' => 'Count (number of social sites to show). If you enable More Icon, then other social sharing icons will display on clicking this plus icon.',
          'value' => 2,
          'validators' => array(
              array('Int', true),
              array('GreaterThan', true, array(0)),
          ),
          'value' => $settings->getSetting('socialshare.icon.limitevent', 1),
      ));
    }
    //Social Share Plugin work

    $this->addElement('Button', 'submit', array(
				'label' => 'Save',
        'type' => 'submit',
        'ignore' => true,
        'decorators' => array('ViewHelper')
    ));
    $this->addDisplayGroup(array('submit'), 'buttons');
  }
}
