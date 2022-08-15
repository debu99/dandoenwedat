<?php
/**
 * SocialEngineSolutions
 *
 * @category   Application_Sesevent
 * @package    Sesevent
 * @copyright  Copyright 2015-2016 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: Searchticket.php 2016-07-26 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */
class Sesevent_Form_Searchticket extends Engine_Form {
  public function init() {
    $this
            ->setMethod('POST')
            ->setAction($_SERVER['REQUEST_URI'])
						 ->setAttribs(array(
                'id' => 'manage_order_search_form',
                'class' => 'global_form_box search_ticket',
            ));
    $this->addElement('Text', 'order_id', array(
        'label'=>'Order Id',
    ));
		$this->addElement('Text', 'registration_number', array(
        'label'=>'Registration Number',
    ));
		$this->addElement('Text', 'buyer_name', array(
        'label'=>'Buyer Name',
    ));
		$this->addElement('Text', 'email', array(
        'label'=>'Email',
    ));
		$this->addElement('Text', 'mobile', array(
        'label'=>'Mobile',
    ));
		$this->addElement('Text', 'creation_date', array(
        'label'=>'Ordered Date Ex (yyyy-mm-dd)',
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
