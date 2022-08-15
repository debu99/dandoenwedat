<?php
/**
 * SocialEngineSolutions
 *
 * @category   Application_Seseventreview
 * @package    Seseventreview
 * @copyright  Copyright 2015-2016 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: Level.php 2016-07-26 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */
class Seseventreview_Form_Admin_Manage_Level extends Authorization_Form_Admin_Level_Abstract {

  public function init() {

    parent::init();

    $this->setTitle('Member Level Settings')
            ->setDescription('These settings are applied on a per member level basis. Start by selecting the member level you want to modify, then adjust the settings for that level below.');


  
	   // Element: view
		$this->addElement('Radio', 'view', array(
			'label' => 'Allow Viewing of Reviews?',
			'description' => 'Do you want to let members view reviews on events?',
			'multiOptions' => array(
				1 => 'Yes, allow viewing  of reviews.',
				0 => 'No, do not allow reviewes to be viewed.',
			),
			'value' => 1 ,
		));
		if (!$this->isPublic()) {	  
      $this->addElement('Radio', 'create', array(
          'label' => 'Allow to Write Reviews?',
          'description' => 'Do you want to let members write reviews on events?',
          'multiOptions' => array(
              1 => 'Yes, allow members to write reviews.',
              0 => 'No, do not allow members to write reviews.'
          ),
          'value' => 1,
      ));


      // Element: edit
      $this->addElement('Radio', 'edit', array(
          'label' => 'Do you want to let members edit reviews?',
          'description' => 'Do you want to let members edit  reviews?',
          'multiOptions' => array(
              1 => "Yes, allow members to edit their own reviews.",
              0 => "No, do not allow reviews to be edited.",
          ),
          'value' => 1,
      ));
      
      //Element: delete
      $this->addElement('Radio', 'delete', array(
          'label' => 'Allow Deletion of Reviews?',
          'description' => 'Do you want to let members delete reviews? If set to no, some other settings on this page may not apply.',
          'multiOptions' => array(
              1 => 'Yes, allow members to delete their own reviews.',
              0 => 'No, do not allow members to delete their reviews.',
          ),
          'value' =>1,
      ));
     

      //Element: comment
      $this->addElement('Radio', 'comment', array(
          'label' => 'Allow Commenting on Reviews?',
          'description' => 'Do you want to let members comment on Reviews?',
          'multiOptions' => array(
              1 => 'Yes, allow members to comment on reviews.',
              0 => 'No, do not allow commenting on reviews.',
          ),
          'value' => 1,
      ));
    }
  }

}
