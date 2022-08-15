<?php

class Sesbasic_Form_Admin_Tooltip_Contest extends Engine_Form {

  public function init() {
    $settings = Engine_Api::_()->getApi('settings', 'core');
    $this->addElement('MultiCheckbox', 'sescontest_contest_settings_tooltip', array(
        'label' => 'General Tooltip Settings',
        'required' => true,
        'empty' => false,
        'multiOptions' => array('title' => 'Title', 'description' => 'Description','media'=>'Media Type','mainphoto' => 'Main Photo', 'coverphoto' => 'Cover Photo', 'category' => 'Category', 'entries' => 'Entries', 'socialshare' => 'Social Share', 'joinNow' => 'Join Now', 'recentEntries' => 'Recent Entries','friendsParticipating' => 'Friends Participating'),
        'value' => $settings->getSetting('sescontest.contest.settings.tooltip', array('title' => 'Title', 'mainphoto' => 'Main Photo', 'coverphoto' => 'Cover Photo', 'category' => 'Category', 'entries' => 'Entries', 'socialshare' => 'Social Share', 'joinNow' => 'Join Now', 'recentEntries' => 'Recent Entries','friendsParticipating')),
    ));
    //Social Share Plugin work
    if (Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled('sessocialshare')) {

      $this->addElement('Select', "socialshare_enable_plusiconcontest", array(
          'label' => "Enable More Icon for social share buttons?",
          'multiOptions' => array(
              '1' => 'Yes',
              '0' => 'No',
          ),
          'value' => $settings->getSetting('socialshare.enable.plusiconcontest', 1),
      ));

      $this->addElement('Text', "socialshare_icon_limitcontest", array(
          'label' => 'Count (number of social sites to show). If you enable More Icon, then other social sharing icons will display on clicking this plus icon.',
          'value' => 2,
          'validators' => array(
              array('Int', true),
              array('GreaterThan', true, array(0)),
          ),
          'value' => $settings->getSetting('socialshare.icon.limitcontest', 1),
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
