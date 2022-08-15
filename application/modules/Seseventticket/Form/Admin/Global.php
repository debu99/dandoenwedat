<?php
/**
 * SocialEngineSolutions
 *
 * @category   Application_Seseventticket
 * @package    Seseventticket
 * @copyright  Copyright 2015-2016 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: Global.php 2016-03-18 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */
class Seseventticket_Form_Admin_Global extends Engine_Form {

  public function init() {

    $settings = Engine_Api::_()->getApi('settings', 'core');

    $this->setTitle('Global Settings')
            ->setDescription('These settings affect all members in your community.');

		$view = Zend_Registry::isRegistered('Zend_View') ? Zend_Registry::get('Zend_View') : null;
		$supportTicket = '<a href="http://www.socialenginesolutions.com/tickets" target="_blank">Support Ticket</a>';
		$sesSite = '<a href="http://www.socialenginesolutions.com" target="_blank">SocialEngineSolutions website</a>';
		$descriptionLicense = sprintf('Enter the your license key that is provided to you when you purchased this plugin. If you do not know your license key, please drop us a line from the %s section on %s. (Key Format: XXXX-XXXX-XXXX-XXXX)',$supportTicket,$sesSite);
		$this->addElement('Text', "seseventticket_licensekey", array(
		'label' => 'Enter License key',
		'description' => $descriptionLicense,
		'allowEmpty' => false,
		'required' => true,
		'value' => $settings->getSetting('seseventticket.licensekey'),
		));
		$this->getElement('seseventticket_licensekey')->getDecorator('Description')->setOptions(array('placement' => 'PREPEND', 'escape' => false));
    if ($settings->getSetting('seseventticket.pluginactivated')) {      
			$serviceTax = Engine_Api::_()->getApi('settings', 'core')->getSetting('seseventticket.service.tax.values',false);
			$serviceTaxA = array();
			if($serviceTax){
				$serviceTax = explode(',',$serviceTax);
				foreach($serviceTax as $val){
					$serviceTaxA[$val] = $val.'%';
				}
			}
			//Service Tax
			$this->addElement('MultiCheckbox', 'sesevent_ticket_service_tax', array(
						'label'=>'Service Tax',
					//'multiOptions' => array('3.09'=>'3.09%','12.36'=>'12.36%','12.5'=>'12.5%','14'=>'14%','14.5'=>'14.5%','17.42'=>'17.42%','17.5'=>'17.5%','20'=>'20%','25'=>'25%','25.5'=>'25.5%'),
					'multiOptions' => $serviceTaxA,
					'value' => $settings->getSetting('sesevent.ticket.service.tax'),
			));
			$entertainmentTax = Engine_Api::_()->getApi('settings', 'core')->getSetting('seseventticket.entertainment.tax.values',false);
			$entertainmentTaxA = array();
			if($entertainmentTax){
				$entertainmentTax = explode(',',$entertainmentTax);
				foreach($entertainmentTax as $val){
					$entertainmentTaxA[$val] = $val.'%';
				}
			}
			//Service Tax
			$this->addElement('MultiCheckbox', 'sesevent_ticket_entertainment_tax', array(
					'label'=>'Entertainment Tax',
					//'multiOptions' => array('10'=>'10%','14'=>'14%','20'=>'20%','25'=>'25%'),
					'multiOptions'=>$entertainmentTaxA,
					'value' => $settings->getSetting('sesevent.ticket.entertainment.tax'),
			));
      
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