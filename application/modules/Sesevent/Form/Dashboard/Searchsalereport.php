<?php
/**
 * SocialEngineSolutions
 *
 * @category   Application_Sesevent
 * @package    Sesevent
 * @copyright  Copyright 2015-2016 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: Searchsalereport.php 2016-07-26 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */
class Sesevent_Form_Dashboard_Searchsalereport extends Engine_Form {
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
		$eventTicketDetails = Engine_Api::_()->getDbtable('tickets', 'sesevent')->getTicket(array('event_id'=>$event->event_id));
		$ticketArray = array();
		$ticketArray[''] = 'Choose Ticket';
		if(count($eventTicketDetails)){
		  foreach($eventTicketDetails as $valueTicket){ 
				$ticketArray[$valueTicket['ticket_id']] = $valueTicket['name'];
			}
		}
		$this->addElement('Select', 'eventTicketId', array(
          'label' => 'Select Ticket',
          'multiOptions' => $ticketArray,
    ));
		$this->addElement('Select', 'type', array(
          'label' => 'Duration',
          'multiOptions' => array('month'=>'Month Wise','day'=>'Day Wise'),
					'value'=>'day',
    ));
		$this->addElement('Hidden', 'csv', array(
          'value'=>'',
					'order'=>10000
    ));
		$this->addElement('Hidden', 'excel', array(
          'value'=>'',
					'order'=>10001
    ));
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