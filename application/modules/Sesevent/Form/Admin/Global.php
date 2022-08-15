<?php
/**
 * SocialEngineSolutions
 *
 * @category   Application_Sesevent
 * @package    Sesevent
 * @copyright  Copyright 2015-2016 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: Global.php 2016-07-26 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */
class Sesevent_Form_Admin_Global extends Engine_Form {

  public function init() {

    $settings = Engine_Api::_()->getApi('settings', 'core');

    $this->setTitle('Global Settings')
            ->setDescription('These settings affect all members in your community.');

    $view = Zend_Registry::isRegistered('Zend_View') ? Zend_Registry::get('Zend_View') : null;
    $supportTicket = '<a href="https://socialnetworking.solutions/tickets" target="_blank">Support Ticket</a>';
    $sesSite = '<a href="https://socialnetworking.solutions" target="_blank">SocialNetworking.Solutions website</a>';
    $descriptionLicense = sprintf('Enter your license key that is provided to you when you purchased this plugin. If you do not know your license key, please drop us a line from the %s section on %s. (Key Format: XXXX-XXXX-XXXX-XXXX)',$supportTicket,$sesSite);

    $this->addElement('Text', "sesevent_licensekey", array(
        'label' => 'Enter License key',
        'description' => $descriptionLicense,
        'allowEmpty' => false,
        'required' => true,
        'value' => $settings->getSetting('sesevent.licensekey'),
    ));
    $this->getElement('sesevent_licensekey')->getDecorator('Description')->setOptions(array('placement' => 'PREPEND', 'escape' => false));
            
    if ($settings->getSetting('sesevent.pluginactivated')) {
		if(!$settings->getSetting('sesevent.changelanding', 0)){
	    $this->addElement('Radio', 'sesevent_changelanding', array(
	        'label' => 'Set Welcome Page as Landing Page',
	        'description' => 'Do you want to set the Default Welcome Page of this plugin as Landing page of your website? [This is a one time setting, so if you choose ‘Yes’ and save changes, then later you can manually make changes in the Landing page from Layout Editor.]',
					'onclick' => 'confirmChangeLandingPage(this.value)',
	        'multiOptions' => array(
	            '1' => 'Yes',
	            '0' => 'No',
	        ),
	        'value' => $settings->getSetting('sesevent.changelanding', 0),
	    ));
		}
	    $this->addElement('Radio', 'sesevent_check_welcome', array(
	          'label' => 'Welcome Page Visibility',
	          'description' => 'Who all users do you want to see this "Welcome Page"?',
	          'multiOptions' => array(
	              0 => 'Only logged in users',
	              1 => 'Only non-logged in users',
	              2 => 'Both, logged-in and non-logged in users',
	          ),
	          'value' => $settings->getSetting('sesevent.check.welcome', 2),
	      ));
	    $this->addElement('Radio', 'sesevent_enable_welcome', array(
	          'label' => 'Event Main Menu Redirection',
	          'description' => 'Choose from below where do you want to redirect users when Event Menu item is clicked in the Main Navigation Menu Bar.',
	          'multiOptions' => array(
	              1 => 'Event Welcome Page',
	              0 => 'Event Home Page',
								2 => 'Event Browse Page'
	          ),
	          'value' => $settings->getSetting('sesevent.enable.welcome', 1),
	      ));

			$this->addElement('Text', 'sesevent_events_manifest', array(
          'label' => 'Plural "events" Text in URL',
          'description' => 'Enter the text which you want to show in place of "events" in the URLs of this plugin.',
          'value' => $settings->getSetting('sesevent.events.manifest', 'events'),
      ));
      $this->addElement('Text', 'sesevent_event_manifest', array(
          'label' => 'Singular "event" Text in URL',
          'description' => 'Enter the text which you want to show in place of "event" in the URLs of this plugin.',
          'value' => $settings->getSetting('sesevent.event.manifest', 'event'),
      ));
			$this->addElement('Radio', 'sesevent_enable_location', array(
	        'label' => 'Enable Location',
	        'description' => 'Do you want to enable location for events on your website?',
	        'multiOptions' => array(
	            '1' => 'Yes',
	            '0' => 'No',
	        ),
	        'value' => $settings->getSetting('sesevent.enable.location', 1),
	    ));
			$this->addElement('Radio', 'sesevent_search_type', array(
	      'label' => 'Proximity Search Unit',
	      'description' => 'Choose the unit for proximity search of location of events on your website.',
	      'multiOptions' => array(
					1 => 'Miles',
					0 => 'Kilometres'
	      ),
	      'value' => $settings->getSetting('sesevent.search.type', 1),
	    ));
      $this->addElement('Radio', 'sesevent_category_enable', array(
          'label' => 'Make Event Categories Mandatory',
          'description' => 'Do you want to make category field mandatory when users create or edit their events?',
          'multiOptions' => array(
              1 => 'Yes',
              0 => 'No'
          ),
          'value' => $settings->getSetting('sesevent.category.enable', 1),
      ));
      $this->addElement('Select', 'sesevent_autoopenpopup', array(
	        'label' => 'Auto-Open Advanced Share Popup',
	        'description' => 'Do you want the "Advanced Share Popup" to be auto-opened after the event is created? [Note: This setting will only work if you have placed Advanced Share widget on Event Profile page or Event Dashboard, wherever user is redirected just after event creation.]',
	        'multiOptions' => array(
	            '1' => 'Yes',
	            '0' => 'No',
	        ),
	        'value' => $settings->getSetting('sesevent.autoopenpopup', 1),
	    ));
			$this->addElement('Select', 'sesevent_eventcustom', array(
	        'label' => 'Show Custom Terms & Conditions',
	        'description' => 'Do you want to show Custom Terms & Conditions field on event create page?',
					'onclick' => 'hideTerm(this.value)',
	        'multiOptions' => array(
	            '1' => 'Yes',
	            '0' => 'No',
	        ),
	        'value' => $settings->getSetting('sesevent.eventcustom', 1),
	    ));
			$this->addElement('Select', 'sesevent_tinymce', array(
	        'label' => 'Enable WYSIWYG Editor for "Custom Terms & Conditions',
	        'description' => 'Do you want to enable WYSIWYG Editor for custom terms and conditions  while creating and editing events?',
	        'multiOptions' => array(
	            '1' => 'Yes',
	            '0' => 'No',
	        ),
	        'value' => $settings->getSetting('sesevent.tinymce', 1),
	    ));
			$this->addElement('Radio', 'sesevent_event_description', array(
          'label' => 'Make Event Description Mandatory',
          'description' => 'Do you want to make description field mandatory when users create or edit their events?',
          'multiOptions' => array(
              1 => 'Yes',
              0 => 'No'
          ),
          'value' => $settings->getSetting('sesevent.event.description', 1),
      ));

      $this->addElement('Radio', 'sesevent_mainphotomand', array(
          'label' => 'Make Event Main Photo Mandatory',
          'description' => 'Do you want to make main photo field mandatory when users create or edit their events?',
          'multiOptions' => array(
              1 => 'Yes',
              0 => 'No'
          ),
          'value' => $settings->getSetting('sesevent.mainphotomand', 0),
      ));

      $this->addElement('Radio', 'sesevent_rsvpevent', array(
          'label' => 'Show “People must be invited to RSVP for this event” Option',
          'description' => 'Do you want to show “People must be invited to RSVP for this event” option to users when they create or edit their events?',
          'multiOptions' => array(
              1 => 'Yes',
              0 => 'No'
          ),
          'onclick' => 'rsvpevent(this.value)',
          'value' => $settings->getSetting('sesevent.rsvpevent', 1),
      ));

      $this->addElement('Radio', 'sesevent_rsvpdefaultval', array(
          'label' => 'Default Value for “People must be invited to RSVP for this event” Option ',
          'description' => 'Choose the default value for “People must be invited to RSVP for this event”.',
          'multiOptions' => array(
              1 => 'People must always be invited to RSVP.',
              0 => 'People can Join Events immediately.'
          ),
          'value' => $settings->getSetting('sesevent.rsvpdefaultval', 1),
      ));


      $this->addElement('Radio', 'sesevent_inviteguest', array(
          'label' => 'Show “Invited guests can invite other people as well” Option',
          'description' => '“Do you want to show “Invited guests can invite other people as well” option to users when they create or edit their events?',
          'multiOptions' => array(
              1 => 'Yes',
              0 => 'No'
          ),
          'onclick' => 'guestevent(this.value)',
          'value' => $settings->getSetting('sesevent.inviteguest', 1),
      ));

      $this->addElement('Radio', 'sesevent_guestdefaultval', array(
          'label' => 'Default Value for “Invited guests can invite other people as well” Option',
          'description' => 'Choose the default value for “Invited guests can invite other people as well”.',
          'multiOptions' => array(
              1 => 'Yes, Invited guests can invite other people as well.',
              0 => 'No, Invited guests can not invite other people.'
          ),
          'value' => $settings->getSetting('sesevent.guestdefaultval', 1),
      ));


	    $this->addElement('Radio', 'sesevent_event_save', array(
	        'label' => 'Enable “Save This Event”',
	        'description' => 'Do you want to enable users to Save events to their saved list on your website? [If Yes, then users will be able to save the events from Event Profile Pages.]',
	        'multiOptions' => array(
	            '1' => 'Yes',
	            '0' => 'No',
	        ),
	        'value' => $settings->getSetting('sesevent.event.save', 1),
	    ));

	    $this->addElement('Radio', 'sesevent_followeventowner', array(
	        'label' => 'Allow to Follow Event Host',
	        'description' => 'Do you want to allow users to follow Event Hosts on your website?',
	        'multiOptions' => array(
	            '1' => 'Yes',
	            '0' => 'No',
	        ),
	        'value' => $settings->getSetting('sesevent.followeventowner', 1),
	    ));

	    /*$this->addElement('Radio', 'sesevent_promoteevent', array(
	        'label' => 'Allow Promote Event',
	        'description' => 'Do you want to allow promote to event on your website?',
	        'multiOptions' => array(
	            '1' => 'Yes',
	            '0' => 'No',
	        ),
	        'value' => $settings->getSetting('sesevent.promoteevent', 0),
	    ));*/


			//user gateway for event ticket and sponsorship
			if( !$settings->getSetting('sesevent.userGateway')){
			if(($settings->getSetting('seseventticket.pluginactivated') && Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled('seseventticket')) || ($settings->getSetting('seseventsponsorship.pluginactivated') && Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled('seseventsponsorship'))) {
				$this->addElement('Radio', 'sesevent_userGateway', array(
					'label' => 'User gateway for event tickets and sponsorship',
					'description' => 'Choose the type for user gateway for their payment request to admin',
					'multiOptions' => array(
							'paypal' => 'Paypal',
					),
					'value' => $settings->getSetting('sesevent.userGateway', 'paypal'),
				));
		  }
			}
		 $this->addElement('Radio', 'sesevent_enable_addeventshortcut', array(
          'label' => 'Show “Create New Event” Icon',
          'description' => 'Do you want to show “Create New Event” icon in the bottom right side of all pages of this plugin?',
          'multiOptions' => array(
              1 => 'Yes',
              0 => 'No'
          ),
          'value' => $settings->getSetting('sesevent.enable.addeventshortcut', 1),
      ));
		  $this->addElement('Radio', 'sesevent_editeventhost', array(
	      'label' => 'Allow to Edit Host',
	      'description' => 'Do you want to allow event owners to edit host of their events?',
	      'multiOptions' => array(
					1 => 'Yes',
					0 => 'No'
	      ),
	      'value' => $settings->getSetting('sesevent.editeventhost', 1),
	    ));

	   $this->addElement('Radio', 'sesevent_sitehostredirect', array(
	      'label' => 'Redirection of Host (Site Member)',
	      'description' => 'Choose from below where do you want to redirect users when they click on Hosts who are members of your website.',
	      'multiOptions' => array(
					1 => 'Website’s Member Profile Page',
					0 => 'Event Plugin’s Host Profile Page'
	      ),
	      'value' => $settings->getSetting('sesevent.sitehostredirect', 1),
	    ));
		$this->addElement('Select', 'sesevent_redirect', array(
				'label' => 'Redirection After Event Creation',
				'description' => 'Choose from below where do you want to redirect users after an event is successfully created.',
				'multiOptions' => array(
						'0' => 'On Event Dashboard Page',
						'1' => 'On Event Profile Page',
				),
				'value' => $settings->getSetting('sesevent.redirect', 1),
		));

		//default photos
        //New File System Code
        $default_photos_main = array();
        $files = Engine_Api::_()->getDbTable('files', 'core')->getFiles(array('fetchAll' => 1, 'extension' => array('gif', 'jpg', 'jpeg', 'png')));
        foreach( $files as $file ) {
          $default_photos_main[$file->storage_path] = $file->name;
        }
		$view = Zend_Registry::isRegistered('Zend_View') ? Zend_Registry::get('Zend_View') : null;
    $fileLink = $view->baseUrl() . '/admin/files/';
		//event main photo
    if (count($default_photos_main) > 0) {
			$default_photos = array_merge(array('application/modules/Sesevent/externals/images/nophoto_event_thumb_profile.png'=>''),$default_photos_main);
      $this->addElement('Select', 'sesevent_event_default_photo', array(
          'label' => 'Main Default Photo for Events',
          'description' => 'Choose Main default photo for the events on your website. [Note: You can add a new photo from the "File & Media Manager" section from here: <a target="_blank" href="' . $fileLink . '">File & Media Manager</a>. Leave the field blank if you do not want to change event default photo.]',
          'multiOptions' => $default_photos,
          'value' => $settings->getSetting('sesevent.event.default.photo'),
      ));
    } else {
      $description = "<div class='tip'><span>" . Zend_Registry::get('Zend_Translate')->_('There are currently no photo for main photo. Photo to be chosen for main photo should be first uploaded from the "Layout" >> "<a target="_blank" href="' . $fileLink . '">File & Media Manager</a>" section. => There are currently no photo in the File & Media Manager for the main photo. Please upload the Photo to be chosen for main photo from the "Layout" >> "<a target="_blank" href="' . $fileLink . '">File & Media Manager</a>" section.') . "</span></div>";
      //Add Element: Dummy
      $this->addElement('Dummy', 'sesevent_event_default_photo', array(
          'label' => 'Main Default Photo for Events',
          'description' => $description,
      ));
    }
    $this->sesevent_event_default_photo->addDecorator('Description', array('placement' => Zend_Form_Decorator_Abstract::PREPEND, 'escape' => false));

		//no event default photo
    if (count($default_photos_main) > 0) {
			$default_photos = array_merge(array('application/modules/Sesevent/externals/images/event-icon.png'=>''),$default_photos_main);
      $this->addElement('Select', 'sesevent_event_no_photo', array(
          'label' => 'Default Photo for No Event Tip',
          'description' => 'Choose a default photo for No events tip on your website. [Note: You can add a new photo from the "File & Media Manager" section from here: <a target="_blank" href="' . $fileLink . '">File & Media Manager</a>. Leave the field blank if you do not want to change this default photo.]',
          'multiOptions' => $default_photos,
          'value' => $settings->getSetting('sesevent.event.no.photo'),
      ));
    } else {
      $description = "<div class='tip'><span>" . Zend_Registry::get('Zend_Translate')->_('There are currently no photo for no photo. Photo to be chosen for no photo should be first uploaded from the "Layout" >> "<a target="_blank" href="' . $fileLink . '">File & Media Manager</a>" section. => There are currently no photo in the File & Media Manager for the main photo. Please upload the Photo to be chosen for no photo from the "Layout" >> "<a target="_blank" href="' . $fileLink . '">File & Media Manage</a>" section.') . "</span></div>";
      //Add Element: Dummy
      $this->addElement('Dummy', 'sesevent_event_no_photo', array(
          'label' => 'Event Default No Event Photo',
          'description' => $description,
      ));
    }
    $this->sesevent_event_no_photo->addDecorator('Description', array('placement' => Zend_Form_Decorator_Abstract::PREPEND, 'escape' => false));

		//host default photo
		 if (count($default_photos_main) > 0) {
			 $default_photos = array_merge(array('application/modules/Sesevent/externals/images/nophoto_host_thumb_icon.png'=>''),$default_photos_main);
      $this->addElement('Select', 'sesevent_host_default_photo', array(
          'label' => 'Main Default Photo Hosts',
          'description' => 'Choose a main default photo for the hosts on your website. [Note: You can add a new photo from the "File & Media Manager" section from here: <a target="_blank" href="' . $fileLink . '">File & Media Manager</a>. Leave the field blank if you do not want to change host default photo.]',
          'multiOptions' => $default_photos,
          'value' => $settings->getSetting('sesevent.host.default.photo'),
      ));
    } else {
      $description = "<div class='tip'><span>" . Zend_Registry::get('Zend_Translate')->_('There are currently no photo for host photo. Photo to be chosen for host photo should be first uploaded from the "Layout" >> "<a target="_blank" href="' . $fileLink . '">File & Media Manager</a>" section. => There are currently no photo in the File & Media Manager for the main photo. Please upload the Photo to be chosen for host photo from the "Layout" >> "<a target="_blank" href="' . $fileLink . '">File & Media Manage</a>" section.') . "</span></div>";
      //Add Element: Dummy
      $this->addElement('Dummy', 'sesevent_host_default_photo', array(
          'label' => 'Main Default Photo Hosts',
          'description' => $description,
      ));
    }
    $this->sesevent_host_default_photo->addDecorator('Description', array('placement' => Zend_Form_Decorator_Abstract::PREPEND, 'escape' => false));

	if( ($settings->getSetting('seseventsponsorship.pluginactivated') && Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled('seseventsponsorship'))) {
		//sponsor default photo
		 if (count($default_photos_main) > 0) {
			 $default_photos = array_merge(array('application/modules/Sesevent/externals/images/nophoto_sponsor_thumb_icon.png'=>''),$default_photos_main);
      $this->addElement('Select', 'sesevent_sponsor_default_photo', array(
          'label' => 'Event Default Sponsor Photo',
          'description' => 'Choose below the photo to be shown for a event sponsor photo. [Note: You can add a new photo from the "File & Media Manager" section from here: <a target="_blank" href="' . $fileLink . '">File & Media Manager</a>.]',
          'multiOptions' => $default_photos,
          'value' => $settings->getSetting('sesevent.sponsor.default.photo'),
      ));
    } else {
      $description = "<div class='tip'><span>" . Zend_Registry::get('Zend_Translate')->_('There are currently no photo for sponsor photo. Photo to be chosen for sponsor photo should be first uploaded from the "Layout" >> "<a target="_blank" href="' . $fileLink . '">File & Media Manager</a>" section. => There are currently no photo in the File & Media Manager for the sponsor photo. Please upload the Photo to be chosen for sponsor photo from the "Layout" >> "<a target="_blank" href="' . $fileLink . '">File & Media Manage</a>" section.') . "</span></div>";
      //Add Element: Dummy
      $this->addElement('Dummy', 'sesevent_sponsor_default_photo', array(
          'label' => 'Event Default Sponsor Photo',
          'description' => $description,
      ));
    }
    $this->sesevent_sponsor_default_photo->addDecorator('Description', array('placement' => Zend_Form_Decorator_Abstract::PREPEND, 'escape' => false));
	}
	if( ($settings->getSetting('Seseventspeaker.pluginactivated') && Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled('Seseventspeaker'))) {
		//speaker default photo
		 if (count($default_photos_main) > 0) {
			 $default_photos = array_merge(array('application/modules/Sesevent/externals/images/nophoto_speaker_thumb_icon.png'=>''),$default_photos_main);
      $this->addElement('Select', 'sesevent_speaker_default_photo', array(
          'label' => 'Event Default Speaker Photo',
          'description' => 'Choose below the photo to be shown for a event speaker photo. [Note: You can add a new photo from the "File & Media Manager" section from here: <a target="_blank" href="' . $fileLink . '">File & Media Manager</a>.]',
          'multiOptions' => $default_photos,
          'value' => $settings->getSetting('sesevent.speaker.default.photo'),
      ));
    } else {
      $description = "<div class='tip'><span>" . Zend_Registry::get('Zend_Translate')->_('There are currently no photo for speaker photo. Photo to be chosen for speaker photo should be first uploaded from the "Layout" >> "<a target="_blank" href="' . $fileLink . '">File & Media Manager</a>" section. => There are currently no photo in the File & Media Manager for the speaker photo. Please upload the Photo to be chosen for speaker photo from the "Layout" >> "<a target="_blank" href="' . $fileLink . '">File & Media Manage</a>" section.') . "</span></div>";
      //Add Element: Dummy
      $this->addElement('Dummy', 'sesevent_speaker_default_photo', array(
          'label' => 'Event Default Speaker Photo',
          'description' => $description,
      ));
    }
    $this->sesevent_speaker_default_photo->addDecorator('Description', array('placement' => Zend_Form_Decorator_Abstract::PREPEND, 'escape' => false));
	}


			 $this->addElement('Select', 'sesevent_taboptions', array(
          'label' => 'Menu Items Count in Main Navigation',
          'description' => 'How many menu items do you want to show in the Main Navigation Menu of this plugin?',
          'multiOptions' => array(
              0 => 0,
              1 => 1,
              2 => 2,
              3 => 3,
              4 => 4,
              5 => 5,
              6 => 6,
              7 => 7,
              8 => 8,
              9 => 9,
          ),
          'value' => $settings->getSetting('sesevent.taboptions', 6),
      ));

        $this->addElement('Radio', "sesevent_allowfavourite", array(
            'label' => 'Allow to Favorite Events',
            'description' => "Do you want to allow members to add Events on your website to Favorites?",
            'multiOptions' => array(
                '1' => 'Yes',
                '0' => 'No',
            ),
            'value' => $settings->getSetting('sesevent.allowfavourite', 1),
        ));
        
      $this->addElement('Radio', "sesevent_other_moduleevents", array(
          'label' => 'Events Created in Content Visibility',
          'description' => "Choose the visibility of the events created in a content to only that content (module) or show in Home page, Browse page and other places of this plugin as well? (To enable users to create events in a content or module, place the widget \"Content Profile Events\" on the profile page of the desired content.)",
          'multiOptions' => array(
              '1' => 'Yes',
              '0' => 'No',
          ),
          'value' => $settings->getSetting('sesevent.other.moduleevents', 1),
      ));
        $this->addElement('Text', "sesevent_limit_change_title", array(
            'label' => 'Times of title changes',
            'description' => 'Number of times the owner can change their event title!',
            'allowEmpty' => false,
            'required' => true,
            'value' => $settings->getSetting('sesevent.limit.change.title', 2),
            'validators' => array(
                array('NotEmpty', true),
                array('Int', true),
                new Engine_Validate_AtLeast(1)
            )
        ));
        $this->getElement('sesevent_limit_change_title')->getDecorator('Description')->setOptions(array('placement' => 'PREPEND', 'escape' => false));
	    // Add submit button
	    $this->addElement('Button', 'submit', array(
	        'label' => 'Save Changes',
	        'type' => 'submit',
	        'ignore' => true
	    ));
	  } else {
      //Add submit button
      $this->addElement('Button', 'submit', array(
          'label' => 'Activate your plugin',
          'type' => 'submit',
          'ignore' => true
      ));
    }
  }
}
