<?php
/**
 * SocialEngineSolutions
 *
 * @category   Application_Sesevent
 * @package    Sesevent
 * @copyright  Copyright 2015-2016 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: EventCreatePageSettings.php 2016-07-26 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */
class Sesevent_Form_Admin_EventCreatePageSettings extends Engine_Form {

  public function init() {

    $settings = Engine_Api::_()->getApi('settings', 'core');

    $this->setTitle('Event Create Page Settings')
            ->setDescription('Here, you can choose the settings to be shown / hidden on the event create page form. The hidden (disabled) settings will be shown on Edit Event Page (Dashboard).');
            
	    $this->addElement('Select', 'sesevent_eevecredescription', array(
	        'label' => 'Show Event Description',
	        'description' => 'Do you want to show description field on event create page?',
	        'multiOptions' => array(
	            '1' => 'Yes',
	            '0' => 'No',
	        ),
	        'value' => $settings->getSetting('sesevent.eevecredescription', 1),
	    ));
			
	   	$this->addElement('Select', 'sesevent_eevecretags', array(
	        'label' => 'Show Tags Option',
	        'description' => 'Do you want to show Tags option on event create page?',
	        'multiOptions' => array(
	            '1' => 'Yes',
	            '0' => 'No',
	        ),
	        'value' => $settings->getSetting('sesevent.eevecretags', 1),
	    )); 
	    $this->addElement('Select', 'sesevent_eevecremainphoto', array(
	        'label' => 'Show Main Photo of Events',
	        'description' => 'Do you want to show Main Photo of Events on event create page?',
	        'multiOptions' => array(
	            '1' => 'Yes',
	            '0' => 'No',
	        ),
	        'value' => $settings->getSetting('sesevent.eevecremainphoto', 1),
	    ));
	    
	          
      $this->addElement('Radio', 'sesevent_draft', array(
          'label' => 'Show Status Option',
          'description' => 'Do you want to show Status option for Events on event create page? [With this option, users will be able to choose to Save their events as Draft or Publish them.]',
          'multiOptions' => array(
              1 => 'Yes',
              0 => 'No'
          ),
          'value' => $settings->getSetting('sesevent.draft', 1),
      ));
	    // Add submit button
	    $this->addElement('Button', 'submit', array(
	        'label' => 'Save Changes',
	        'type' => 'submit',
	        'ignore' => true
	    ));
  }
}