<?php
/**
 * SocialEngineSolutions
 *
 * @category   Application_Sesevent
 * @package    Sesevent
 * @copyright  Copyright 2015-2016 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: Searchsponsorshipsalereport.php 2016-07-26 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */
class Sesevent_Form_Sponsorship_Searchsponsorshipsalereport extends Engine_Form {
 public function init() {
    if (Engine_Api::_()->core()->hasSubject('sesevent_event'))
      $event = Engine_Api::_()->core()->getSubject();
    //get current logged in user
    $user = Engine_Api::_()->user()->getViewer();
    $this->setTitle('')
            ->setAttrib('id', 'sesevent_search_form_sale_report')
						->setAttrib('class', 'global_form_box')
            ->setMethod("GET")
            ->setAction(Zend_Controller_Front::getInstance()->getRouter()->assemble(array()));
		$eventSponsorshipDetails = Engine_Api::_()->getDbtable('sponsorships', 'sesevent')->getSponsorship(array('event_id'=>$event->event_id));
		$sponsorshipArray = array();
		$sponsorshipArray[''] = 'Select Sponsorship';
		if(count($eventSponsorshipDetails)){
		  foreach($eventSponsorshipDetails as $valueSponsorship){ 
				$sponsorshipArray[$valueSponsorship['sponsorship_id']] = $valueSponsorship['title'];
			}
		}
		$this->addElement('Select', 'eventSponsorshipId', array(
          'label' => 'Select Ticket',
          'multiOptions' => $sponsorshipArray,
    ));
		$this->addElement('Select', 'type', array(
          'label' => 'Select Report Type',
          'multiOptions' => array('month'=>'Month Wise','day'=>'Day Wise'),
					'value'=>'day',
    ));
 		// Start time
   $this->addElement('Text', 'startdate', array(
        'label'=>'Start Date',
				'style'=>'width:70px;'
    ));
		$this->addElement('Text', 'enddate', array(
        'label'=>'End Date',
				'style'=>'width:70px;'
    ));
		// Buttons
    $this->addElement('Button', 'submit_form_sales_report', array(
        'label' => 'Save Changes',
        'type' => 'submit',
        'ignore' => true
    ));
 }
}