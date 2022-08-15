<?php
/**
 * SocialEngineSolutions
 *
 * @category   Application_Sesevent
 * @package    Sesevent
 * @copyright  Copyright 2015-2016 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: Searchsponsorshiporder.php 2016-07-26 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */
class Sesevent_Form_Searchsponsorshiporder extends Engine_Form {

  public function init() {

    $this
            ->setMethod('POST')
            ->setAction($_SERVER['REQUEST_URI'])
						 ->setAttribs(array(
                'id' => 'manage_order_search_form',
                'class' => 'global_form_box manage_sponsorship',
            ));
    $this->addElement('Text', 'order_id', array(
        'label'=>'Order Id',
    ));
		$this->addElement('Text', 'buyer_name', array(
        'label'=>'Buyer Name',
    ));
		//date
		$subform = new Engine_Form(array(
			'description' => 'Order Date (2015-12-29)',
			'elementsBelongTo'=> 'date',
			'decorators' => array(
				'FormElements',
				array('Description', array('placement' => 'PREPEND', 'tag' => 'label', 'class' => 'form-label')),
				array('HtmlTag', array('tag' => 'div', 'class' => 'form-wrapper', 'id' =>'integer-wrapper'))
			)
		));
		$subform->addElement('Text', 'date_to', array('placeholder'=>'to'));
		$subform->addElement('Text', 'date_from', array('placeholder'=>'from'));
		$this->addSubForm($subform, 'date');
		
		//order total
		$orderform = new Engine_Form(array(
			'description' => 'Order Total',
			'elementsBelongTo'=> 'order',
			'decorators' => array(
				'FormElements',
				array('Description', array('placement' => 'PREPEND', 'tag' => 'label', 'class' => 'form-label')),
				array('HtmlTag', array('tag' => 'div', 'class' => 'form-wrapper', 'id' =>'integer-wrapper'))
			)
		));
		$orderform->addElement('Text', 'order_min', array('placeholder'=>'min'));
		$orderform->addElement('Text', 'order_max', array('placeholder'=>'max'));
		$this->addSubForm($orderform, 'order');
		
		//commission
		$subform = new Engine_Form(array(
			'description' => 'Commision',
			'elementsBelongTo'=> 'commision',
			'decorators' => array(
				'FormElements',
				array('Description', array('placement' => 'PREPEND', 'tag' => 'label', 'class' => 'form-label')),
				array('HtmlTag', array('tag' => 'div', 'class' => 'form-wrapper', 'id' =>'integer-wrapper'))
			)
		));
		$subform->addElement('Text', 'commision_min', array('placeholder'=>'min'));
		$subform->addElement('Text', 'commision_max', array('placeholder'=>'max'));
		$this->addSubForm($subform, 'commision');
		$this->addElement('Select', 'gateway', array(
        'label'=>'Gateway',
				'MultiOptions'=>array(''=>'','Paypal'=>'Paypal','2Checkout'=>'2Checkout')
    ));
		$this->addElement('Button', 'search', array(
      'label' => 'Search',
      'type' => 'submit',
    ));
		$this->addElement('Dummy','loading-img-sesevent', array(
        'content' => '<img src="application/modules/Core/externals/images/loading.gif" id="sesevent-search-order-img" alt="Loading" />',
   ));
  }

}
