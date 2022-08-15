<?php
/**
 * SocialEngineSolutions
 *
 * @category   Application_Sesevent
 * @package    Sesevent
 * @copyright  Copyright 2015-2016 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: Ticket.php 2016-07-26 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */
class Sesevent_Form_Dashboard_Ticket extends Engine_Form {
	 public function init() {
		$this->setTitle('Create New Ticket')
					->setAttrib('id', 'sesevent_ticket_submit_form')
					->setMethod("POST")
					->setAction(Zend_Controller_Front::getInstance()->getRouter()->assemble(array()));		
		//Event type
		$this->addElement('Radio', 'type', array(
		 		'label'=>'Ticket Type',
        'multiOptions' => array(
            'paid' => 'Paid Ticket',
            'free' => 'Free Ticket',
        ),
				'value'=>'paid'
    ));
		// Event Ticket Name
    $this->addElement('Text', 'name', array(
        'label' => 'Ticket Name',
				'placeholder'=>'Ticket Name',
        'allowEmpty' => false,
        'required' => true,
        'validators' => array(
            array('NotEmpty', true),
            array('StringLength', false, array(1, 255)),
        ),
        'filters' => array(
            'StripTags',
            new Engine_Filter_Censor(),
        ),
    ));
		$defaultCurrency = Engine_Api::_()->sesevent()->defaultCurrency();
		//Event Currency
		$this->addElement('Select', 'currency', array(
		 		'label'=>'Currency',
        'multiOptions' => array($defaultCurrency=>$defaultCurrency),
    ));
		// Event Price Email
    $this->addElement('Text', 'price', array(
        'label' => 'Price',
				'placeholder'=>"0.00",
    ));
		// Event Ticket Description
    $this->addElement('Textarea', 'description', array(
        'label' => 'Description',
    ));
		//Event Currency
		$this->addElement('Select', 'timezone', array(
		 		'label'=>'Timezone',
        'multiOptions' => array(),
    ));
		$ticket_id = Zend_Controller_Front::getInstance()->getRequest()->getParam('ticket_id');
    if ($ticket_id) {
      $ticket = Engine_Api::_()->getItem('sesevent_ticket', $ticket_id);
    }
		$event = Engine_Api::_()->core()->getSubject();
		if(isset($ticket) && empty($_POST)){
			 $event = Engine_Api::_()->getItem('sesevent_event', $ticket->event_id);
			 // Convert and re-populate times
      $start = strtotime($ticket->starttime);
      $end = strtotime($ticket->endtime);
      $oldTz = date_default_timezone_get();
      date_default_timezone_set($event->timezone);
     	$start_date = date('m/d/Y',($start));
			$start_time = date('H:i',($start));
			$endDate = date('Y-m-d H:i:s', ($end));
			$end_date = date('m/d/Y',strtotime($endDate));
			$end_time = date('H:i',strtotime($endDate));
      date_default_timezone_set($oldTz);
		}else if(empty($_POST)){
			$start = strtotime($event->starttime);
      $end = strtotime($event->endtime);
			$oldTz = date_default_timezone_get();
			date_default_timezone_set($event->timezone);
			$start_date = date('m/d/Y',($start));
			$start_time = date('H:i',($start));
			$end_date = date('m/d/Y',($end));
			$end_time = date('H:i',($end));
			date_default_timezone_set($oldTz);
		}else{
			$start_date = date('m/d/Y',strtotime($_POST['start_date']));
			$start_time = date('H:i',strtotime($_POST['start_date']));
			$endDate = date('Y-m-d h:i:s', strtotime($_POST['end_date']));
			$end_date = date('m/d/Y',strtotime($endDate));
			$end_time = date('H:i',strtotime($endDate));
		}
		$this->addElement('dummy', 'event_custom_datetimes', array(
			'decorators' => array(array('ViewScript', array(
									'viewScript' => 'application/modules/Sesevent/views/scripts/_customdates.tpl',
									'class' => 'form element',
									'start_date'=>$start_date,
									'end_date'=>$end_date,
									'start_time'=>$start_time,
									'end_time'=>$end_time,
									'start_time_check'=>0,
									'subject'=>isset($event) ? $event : ''
							)))
    ));
		
		$serviceTax = Engine_Api::_()->getApi('settings', 'core')->getSetting('sesevent.ticket.service.tax');
		$enterTax = Engine_Api::_()->getApi('settings', 'core')->getSetting('sesevent.ticket.entertainment.tax');
		$serviceTaxArray = array();	
		if(is_array($serviceTax) && count($serviceTax)){
			foreach($serviceTax as $sTax){
				$serviceTaxArray[$sTax]	= $sTax.'%';
			}
		}
		$enterTaxArray = array();	
		if(is_array($enterTax) && count($enterTax)){
			foreach($enterTax as $eTax){
				$enterTaxArray[$eTax]	= $eTax.'%';
			}
		}
		
    $this->addElement('Text', 'total', array(
        'label' => 'Total Tickets',
				'description'=>'Enter the number of tickets to be sold.'
    ));
		$this->total->getDecorator("Description")->setOption("placement", "append");
    $this->addElement('Text', 'min_quantity', array(
        'label' => 'Minimum Purchased Tickets',
				'description'=>'Enter the minimum number of tickets to be purchased by the buyers of this ticket.'
    ));
		$this->min_quantity->getDecorator("Description")->setOption("placement", "append");
    $this->addElement('Text', 'max_quantity', array(
        'label' => 'Maximum Purchased Tickets',
				'description'=>'Enter the maximum number of tickets that buyers of this ticket can purchase.'
    ));
		$this->max_quantity->getDecorator("Description")->setOption("placement", "append");
		
		
		if(count($serviceTaxArray) || count($enterTaxArray)){
			// Tax
			$this->addElement('Checkbox', 'tax', array(
					'label' => 'Yes, enable taxes on this ticket',
					'value' => 0
			));
			if(count($serviceTaxArray)){
				$this->addElement('Checkbox', 'service_tax_checkbox', array(
						'label' => 'Yes, enable Service tax. (If Yes, then you can choose the tax amount.)',
						'class'=>'sesevent_tax',
						'value' => 0
				));
				//Service Tax
				$this->addElement('Select', 'service_tax', array(
							'class'=>'sesevent_tax',
						//'multiOptions' => array('3.09'=>'3.09%','12.36'=>'12.36%','12.5'=>'12.5%','14'=>'14%','14.5'=>'14.5%','17.42'=>'17.42%','17.5'=>'17.5%','20'=>'20%','25'=>'25%','25.5'=>'25.5%'),
						'multiOptions'=>$serviceTaxArray
				));
			}
			if(count($enterTaxArray)){
				$this->addElement('Checkbox', 'entertainment_tax_checkbox', array(
						'class'=>'sesevent_tax',
						'label' => 'Yes, enable Entertainment tax. (If Yes, then you can choose the tax amount.)',
						'value' => 0
				));
				//Service Tax
				$this->addElement('Select', 'entertainment_tax', array(
						'class'=>'sesevent_tax',
						//'multiOptions' => array('10'=>'10%','14'=>'14%','20'=>'20%','25'=>'25%'),
						'multiOptions'=>$enterTaxArray,
				));
			}
		}
		
		$this->addElement('Button', 'submit', array(
        'label' => 'Create',
        'type' => 'submit',
        'ignore' => true,
        'decorators' => array(
            'ViewHelper',
        ),
    ));
    $this->addElement('Cancel', 'cancel', array(
        'label' => 'cancel',
        'link' => true,
				'onclick'=>'cancelTicketCreate();return false;',
        'prependText' => ' or ',
        'decorators' => array(
            'ViewHelper',
        ),
    ));
    $this->addDisplayGroup(array('submit', 'cancel'), 'buttons', array(
        'decorators' => array(
            'FormElements',
            'DivDivDivWrapper',
        ),
    ));
	 }
}