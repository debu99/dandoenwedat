<?php

class Sesfeedbg_Form_Admin_Settings_Level extends Authorization_Form_Admin_Level_Abstract {

  public function init() {
  
    parent::init();

    // My stuff
    $this
      ->setTitle('Member Level Settings')
      ->setDescription("These settings are applied as per member level. Start by selecting the member level you want to modify, then adjust the settings for that level from below.");

    if( !$this->isPublic() ) {

      $this->addElement('Radio', 'enablefeedbg', array(
        'label' => 'Enable Background in Updates (Text Only Posts)',
        'description' => 'Do you want to enable the background images in the status updates and activity feeds of your website for the users of this member level? Users will be able to add background images in their status updates from the Member Home Page only. If Yes, then you can choose the order of the Backgrounds to be shown in the "SES - Advanced Activity Feed" widget placed on the Member Home page.',
        'multiOptions' => array(
            '1' => 'Yes',
            '0' => 'No',
        ),
        'value' => 1,
      ));
      
      // Element: max
      $this->addElement('Text', 'max', array(
        'label' => 'Number of Backgrounds',
        'description' => 'Enter the number of background images to be shown in the status box to the users of this member level. (Maximum 12 background images are recommended.)',
        'validators' => array(
          array('Int', true),
          new Engine_Validate_AtLeast(2),
        ),
        'value' => 12,
      ));
    }
  }
}