<?php
class Sesbasic_Form_Admin_Tooltip_Member extends Engine_Form {
  public function init() {
		$settings = Engine_Api::_()->getApi('settings', 'core');
		$this->addElement('MultiCheckbox', 'user_settings_tooltip', array(
      'label' => 'General Tooltip Settings',
			'required'=>true,
			'empty'=>false,
      'multiOptions' => array('title'=>'Title','mainphoto'=>'Main Photo','coverphoto'=>'Cover Photo','location'=>'Location','socialshare'=>'Social Share', 'friendCount' => 'Total Friends', 'mutualFriendCount' => 'Mutual Friend', 'likeButton' => 'Like Button', 'message' => 'Message Button', 'view' => 'View Count', 'like' => 'Like Count', 'follow' => 'Follow Button', 'friendButton' => 'Friend Button', 'age' => 'Member Age', 'profileType' => 'Member profile Type', 'email' => 'Member Email', 'rating' => 'Member Rating'),
			'value' => $settings->getSetting('user.settings.tooltip', array('title','mainphoto','coverphoto', 'socialshare','location', 'friendCount', 'mutualFriendCount', 'likeButton', 'message', 'view', 'like', 'follow', 'friendButton', 'age', 'profileType', 'rating')),
    ));
    
    //Social Share Plugin work
    if(Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled('sessocialshare')) {
      
      $this->addElement('Select', "socialshare_enable_plusicon", array(
        'label' => "Enable More Icon for social share buttons?",
          'multiOptions' => array(
          '1' => 'Yes',
          '0' => 'No',
        ),
        'value' => $settings->getSetting('socialshare.enable.plusicon', 1),
      ));
      
      $this->addElement('Text', "socialshare_icon_limit", array(
          'label' => 'Count (number of social sites to show). If you enable More Icon, then other social sharing icons will display on clicking this plus icon.',
          'value' => 2,
          'validators' => array(
              array('Int', true),
              array('GreaterThan', true, array(0)),
          ),
          'value' => $settings->getSetting('socialshare.icon.limit', 1),
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