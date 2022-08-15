<?php
/**
 * SocialEngineSolutions
 *
 * @category   Application_Sesevent
 * @package    Sesevent
 * @copyright  Copyright 2015-2016 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: Level.php 2016-07-26 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */
class Sesevent_Form_Admin_Settings_Level extends Authorization_Form_Admin_Level_Abstract {

  public function init() {

    parent::init();

    // My stuff
    $this
            ->setTitle('Member Level Settings')
            ->setDescription('SESEVENT_FORM_ADMIN_LEVEL_DESCRIPTION');

    // Element: view
    $this->addElement('Radio', 'view', array(
        'label' => 'Allow Viewing of Events?',
        'description' => 'SESEVENT_FORM_ADMIN_LEVEL_VIEW_DESCRIPTION',
        'multiOptions' => array(
            2 => 'Yes, allow members to view all events, even private ones.',
            1 => 'Yes, allow members to view their own events.',
            0 => 'No, do not allow events to be viewed.',
        ),
        'value' => ( $this->isModerator() ? 2 : 1 ),
    ));
    if (!$this->isModerator()) {
      unset($this->view->options[2]);
    }

    if (!$this->isPublic()) {

      // Element: create
      $this->addElement('Radio', 'create', array(
          'label' => 'Allow Creation of Events?',
          'description' => 'SESEVENT_FORM_ADMIN_LEVEL_CREATE_DESCRIPTION',
          'multiOptions' => array(
              1 => 'Yes, allow creation of events.',
              0 => 'No, do not allow events to be created.',
          ),
          'value' => 1,
      ));

      // Element: edit
      $this->addElement('Radio', 'edit', array(
          'label' => 'Allow Editing of Events?',
          'description' => 'Do you want to let members edit and delete events?',
          'multiOptions' => array(
              2 => "Yes, allow members to edit everyone's events.",
              1 => "Yes, allow  members to edit their own events.",
              0 => "No, do not allow events to be edited.",
          ),
          'value' => ( $this->isModerator() ? 2 : 1 ),
      ));
      if (!$this->isModerator()) {
        unset($this->edit->options[2]);
      }

      // Element: delete
      $this->addElement('Radio', 'delete', array(
          'label' => 'Allow Deletion of Events?',
          'description' => 'Do you want to let members delete events? If set to no, some other settings on this page may not apply.',
          'multiOptions' => array(
              2 => 'Yes, allow members to delete all events.',
              1 => 'Yes, allow members to delete their own events.',
              0 => 'No, do not allow members to delete their events.',
          ),
          'value' => ( $this->isModerator() ? 2 : 1 ),
      ));
      if (!$this->isModerator()) {
        unset($this->delete->options[2]);
      }

      // Element: comment
      $this->addElement('Radio', 'comment', array(
          'label' => 'Allow Commenting on Events?',
          'description' => 'Do you want to let members of this level comment on events?',
          'multiOptions' => array(
              2 => 'Yes, allow members to comment on all events, including private ones.',
              1 => 'Yes, allow members to comment on events.',
              0 => 'No, do not allow members to comment on events.',
          ),
          'value' => ( $this->isModerator() ? 2 : 1 ),
      ));
      if (!$this->isModerator()) {
        unset($this->comment->options[2]);
      }

      // Element: auth_view
      $this->addElement('MultiCheckbox', 'auth_view', array(
          'label' => 'Event Privacy',
          'description' => 'SESEVENT_FORM_ADMIN_LEVEL_AUTHVIEW_DESCRIPTION',
          'multiOptions' => array(
              'everyone' => 'Everyone',
              'registered' => 'Registered Members',
              'owner_network' => 'Friends and Networks (user events only)',
              'owner_member_member' => 'Friends of Friends (user events only)',
              'owner_member' => 'Friends Only (user events only)',
              'parent_member' => 'Group Members (group events only)',
              'member' => "Event Guests Only",
          //'owner' => 'Just Me'
          )
      ));

      // Element: auth_comment
      $this->addElement('MultiCheckbox', 'auth_comment', array(
          'label' => 'Event Comment Options',
          'description' => 'SESEVENT_FORM_ADMIN_LEVEL_AUTHCOMMENT_DESCRIPTION',
          'multiOptions' => array(
              'registered' => 'Registered Members',
              'owner_network' => 'Friends and Networks (user events only)',
              'owner_member_member' => 'Friends of Friends (user events only)',
              'owner_member' => 'Friends Only (user events only)',
              'parent_member' => 'Group Members (group events only)',
              'member' => "Event Guests Only",
              'owner' => 'Just Me'
          )
      ));

      // Element: auth_photo
      $this->addElement('MultiCheckbox', 'auth_photo', array(
          'label' => 'Photo Upload Options',
          'description' => 'SESEVENT_FORM_ADMIN_LEVEL_AUTHUPHOTO_DESCRIPTION',
          'multiOptions' => array(
              'registered' => 'Registered Members',
              'owner_network' => 'Friends and Networks (user events only)',
              'owner_member_member' => 'Friends of Friends (user events only)',
              'owner_member' => 'Friends Only (user events only)',
              'parent_member' => 'Group Members (group events only)',
              'member' => "Event Guests Only",
              'owner' => 'Just Me'
          )
      ));
      if(Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled('seseventvideo')) {

        // Element: video
	      $this->addElement('Radio', 'event_video', array(
	          'label' => 'Allow Videos Upload in Events?',
	          'description' => 'Do you want to let members of this level upload videos in events?',
	          'multiOptions' => array(
	              2 => 'Yes, allow members to upload videos on events, including private ones.',
	              1 => 'Yes, allow members to upload videos in events.',
	              0 => 'No, do not allow members to upload videos in events.',
	          ),
	          'value' => ( $this->isModerator() ? 2 : 1 ),
	      ));
	      if (!$this->isModerator()) {
	        unset($this->event_video->options[2]);
	      }

				// Element: auth_photo
	      $this->addElement('MultiCheckbox', 'auth_video', array(
	          'label' => 'Video Upload Options',
	          'description' => 'Your users can choose from any of the options checked below when they decide who can upload videos to their events. If you do not check any options, settings will default to the last saved configuration. If you select only one option, members of this level will not have a choice.',
	          'multiOptions' => array(
	              'registered' => 'Registered Members [including & excluding Event guests]',
	              'owner_network' => 'Friends and Networks (including Event guests only)',
	              'owner_member_member' => 'Friends of Friends (including Event guests only)',
	              'owner_member' => 'Friends Only (including Event guests only)',
	              'parent_member' => 'Group Members (including Group\'s Events guests only)',
	              'member' => "Event Guests Only",
	              'owner' => 'Just Me'
	          )
	      ));
      }
      if(Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled('seseventmusic')) {

		    // Element: music
	      $this->addElement('Radio', 'event_music', array(
	          'label' => 'Allow Music in Events?',
	          'description' => 'Do you want to let members of this level create music in events?',
	          'multiOptions' => array(
	              2 => 'Yes, allow members to create music in all events.',
	              1 => 'Yes, allow members to create music in events.',
	              0 => 'No, do not allow members to create music in events.',
	          ),
	          'value' => ( $this->isModerator() ? 2 : 1 ),
	      ));
	      if (!$this->isModerator()) {
	        unset($this->event_music->options[2]);
	      }

				// Element: auth_music
	      $this->addElement('MultiCheckbox', 'auth_music', array(
	          'label' => 'MUsic Upload Options',
	          'description' => 'SESEVENT_FORM_ADMIN_LEVEL_AUTHUMUSIC_DESCRIPTION',
	          'multiOptions' => array(
	              'registered' => 'Registered Members',
	              'owner_network' => 'Friends and Networks (user events only)',
	              'owner_member_member' => 'Friends of Friends (user events only)',
	              'owner_member' => 'Friends Only (user events only)',
	              'parent_member' => 'Group Members (group events only)',
	              'member' => "Event Guests Only",
	              'owner' => 'Just Me'
	          )
	      ));
      }

			// Element: auth_photo
      $this->addElement('MultiCheckbox', 'auth_topic', array(
          'label' => 'Topic Post Options',
          'description' => 'SESEVENT_FORM_ADMIN_LEVEL_AUTHUPHOTO_DESCRIPTION',
          'multiOptions' => array(
              'registered' => 'Registered Members',
              'owner_network' => 'Friends and Networks (user events only)',
              'owner_member_member' => 'Friends of Friends (user events only)',
              'owner_member' => 'Friends Only (user events only)',
              'parent_member' => 'Group Members (group events only)',
              'member' => "Event Guests Only",
              'owner' => 'Just Me'
          )
      ));

     /* $this->addElement('Radio', 'style', array(
          'label' => 'Allow Profile Style',
          'required' => true,
          'multiOptions' => array(
              1 => 'Yes, allow custom profile styles.',
              0 => 'No, do not allow custom profile styles.'
          ),
          'value' => 1
      ));*/
			// Element: commentHtml
   /* $this->addElement('Text', 'commentHtml', array(
        'label' => 'Allow HTML in posts?',
        'description' => 'SESEVENT_FORM_ADMIN_LEVEL_CONTENTHTML_DESCRIPTION',
    ));*/

    //Element: auth_listadd
    $this->addElement('Radio', 'addlist_event', array(
        'label' => 'Allow Adding Events to List?',
        'description' => 'Do you want to let members add events to their lists?',
        'multiOptions' => array(
            1 => 'Yes, allow members to add events to their lists.',
            0 => 'No, do not allow members to add events to their lists.'
        ),
        'value' => 1,
    ));
		//Element: max
    $this->addElement('Text', 'addlist_maxevent', array(
        'label' => 'Maximum Allowed Lists',
        'description' => 'Enter the maximum number of lists a member can create. The field must contain an integer, use zero for unlimited.',
        'validators' => array(
            array('Int', true),
            new Engine_Validate_AtLeast(0),
        ),
				'value'=>0
    ));

		//Ekelent : auth cover photos
        //New File System Code
        $default_photos = array();
        $files = Engine_Api::_()->getDbTable('files', 'core')->getFiles(array('fetchAll' => 1, 'extension' => array('gif', 'jpg', 'jpeg', 'png')));
        foreach( $files as $file ) {
          $default_photos[$file->storage_path] = $file->name;
        }
		$default_photos = array_merge(array(''),$default_photos);
		$view = Zend_Registry::isRegistered('Zend_View') ? Zend_Registry::get('Zend_View') : null;
    $fileLink = $view->baseUrl() . '/admin/files/';
		//event main photo
    if (count($default_photos) > 0) {
      $this->addElement('Select', 'event_cover', array(
          'label' => 'Default Event Cover Photo',
          'description' => 'Choose default photo for the event covers on your website. [Note: You can add a new photo from the "File & Media Manager" section from here: <a target="_blank" href="' . $fileLink . '">File & Media Manager</a>. Leave the field blank if you do not want to change event cover default photo.]',
          'multiOptions' => $default_photos,
      ));
    } else {
      $description = "<div class='tip'><span>" . Zend_Registry::get('Zend_Translate')->_('There are currently no photo for cover photo. Photo to be chosen for cover photo should be first uploaded from the "Layout" >> "<a target="_blank" href="' . $fileLink . '">File & Media Manager</a>" section. => There are currently no photo in the File & Media Manager for the cover photo. Please upload the Photo to be chosen for cover photo from the "Layout" >> "<a target="_blank" href="' . $fileLink . '">File & Media Manage</a>" section.') . "</span></div>";
      //Add Element: Dummy
      $this->addElement('Dummy', 'event_cover', array(
          'label' => 'Default Event Cover Photo',
          'description' => $description,
      ));
    }
    $this->event_cover->addDecorator('Description', array('placement' => Zend_Form_Decorator_Abstract::PREPEND, 'escape' => false));

		//element for event approve
		$this->addElement('Radio', 'event_approve', array(
				'description' => 'Do you want events created by members of this level to be auto-approved?',
				'label' => 'Auto Approve Events',
				'multiOptions' => array(
						1=>'Yes, auto-approve events.',
						0=>'No, do not auto-approve events.'
				),
				'value' => 1,
     ));
		 //element for event featured
		$this->addElement('Radio', 'event_featured', array(
				'description' => 'Do you want events created by members of this level to be automatically marked as Featured?',
				'label' => 'Automatically Mark Events as Featured',
				'multiOptions' => array(
						1=>'Yes, automatically mark events as Featured',
						0=>'No, do not automatically mark events as Featured',
				),
				'value' => 0,
     ));
		 //element for event sponsored
		$this->addElement('Radio', 'event_sponsored', array(
				'description' => '“Do you want events created by members of this level to be automatically marked as Sponsored?”',
				'label' => 'Automatically Mark Events as Sponsored',
				'multiOptions' => array(
						1=>'Yes, automatically mark events as Sponsored',
						0=>'No, do not automatically mark events as Sponsored',
				),
				'value' => 0,
     ));
		 //element for event verified
		$this->addElement('Radio', 'event_verified', array(
				'description' => 'Do you want events created by members of this level to be automatically marked as Verified?',
				'label' => 'Automatically Mark Events as Verified',
				'multiOptions' => array(
						1=>'Yes, automatically mark events as Verified',
						0=>'No, do not automatically mark events as Verified',
				),
				'value' => 0,
     ));

      $this->addElement('Radio', 'allow_levels', array(
          'label' => 'Allow to choose "Event View Privacy Based on Member Levels"',
          'description' => 'Do you want to allow the members of this level to choose View privacy of their Events based on Member Levels on your website? If you choose Yes, then users will be able to choose the visibility of their Events to members of selected member levels only.',
          //'class' => $class,
          'multiOptions' => array(
              1 => 'Yes',
              0 => 'No',
          ),
          'value' => 0,
      ));

      $this->addElement('Radio', 'allow_network', array(
          'label' => 'Allow to choose "Event View Privacy Based on Networks"',
          'description' => 'Do you want to allow the members of this level to choose View privacy of their Events based on Networks on your website? If you choose Yes, then users will be able to choose the visibility of their Events to members who have joined selected networks only.',
          'class' => $class,
          'multiOptions' => array(
              1 => 'Yes',
              0 => 'No',
          ),
          'value' => 0,
      ));

		 //Element: max
    $this->addElement('Text', 'maxevent', array(
        'label' => 'Maximum Allowed Events',
        'description' => 'Enter the maximum number of events a member can create. The field must contain an integer, use zero for unlimited.',
        'validators' => array(
            array('Int', true),
            new Engine_Validate_AtLeast(0),
        ),
				'value'=>0
    ));

    if(Engine_Api::_()->getApi('settings', 'core')->getSetting('seseventticket.pluginactivated') && Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled('seseventticket')) {
			//commission
			$this->addElement('Select', 'event_admincomn', array(
	      'label' => 'Unit for Commission in Event Tickets',
	      'description' => 'Choose the unit for admin commission in events tickets.',
	      'multiOptions' => array(
						1 => 'Percentage',
						0 => 'Fixed'
	      ),
				'allowEmpty' => false,
	       'required' => true,
	      'value' => 1,
	    ));
			$this->addElement('Text', "event_commission", array(
	        'label' => 'Commission Value',
	        'description' => "Enter the value for commission according to the unit chosen in above setting. [If you have chosen Percentage, then value should be in range 1 to 100.]",
	        'allowEmpty' => true,
	        'required' => false,
	        'value' => 1,
	    ));
	    $this->addElement('Text', "event_threshold", array(
	        'label' => 'Threshold Amount for Releasing Payment',
	        'description' => "Enter the threshold amount which will be required before making request for releasing payment from admins.",
	        'allowEmpty' => false,
	        'required' => true,
	        'value' => 100,
	    ));
    }
    if(Engine_Api::_()->getApi('settings', 'core')->getSetting('seseventsponsorship.pluginactivated') && Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled('seseventsponsorship')){
			//sponsorship comission
			$this->addElement('Select', 'event_sponcommio', array(
	      'label' => 'Commission Unit For Sponsorship',
	      'description' => 'Choose the unit for commission of sponsorship of Event on your website.',
	      'multiOptions' => array(
						1 => '%age',
						0 => 'fixed'
	      ),
				'allowEmpty' => false,
	       'required' => true,
	      'value' => 1,
	    ));
			$this->addElement('Text', "sponcommi_value", array(
	        'label' => 'Enter Commission value of Sponsorship',
	        'description' => "If select %age in above field than value is between 1 to 100",
	        'allowEmpty' => true,
	        'required' => false,
	        'value' => 1,
	    ));
			$this->addElement('Text', "event_sponsothre", array(
	        'label' => 'Payment Threshold Amount For Sponsorship',
	        'description' => "Enter Threshold Payment Amount For Sponsorship",
	        'allowEmpty' => false,
	        'required' => true,
	        'value' => 100,
	    ));
			}
    }
	}
}
