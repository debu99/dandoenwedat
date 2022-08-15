<?php
/**
 * SocialEngineSolutions
 *
 * @category   Application_Sesevent
 * @package    Sesevent
 * @copyright  Copyright 2015-2016 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: ManageTickets.php 2016-07-26 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */
class Sesevent_Form_ManageTickets extends Engine_Form {

  public function init() {

    $this->setMethod('POST')
            ->setAction($_SERVER['REQUEST_URI'])
            ->setAttribs(array(
                'id' => 'manage_tickets_search_form',
                'class' => 'global_form_box',
    ));

    $this->addElement('Text', 'name', array(
        'label' => 'Ticket Name',
        'placeholder' => 'Enter Ticket Name'
    ));

    $this->addElement('Select', 'type', array(
        'label' => 'Type',
        'MultiOptions' => array('' => 'Choose Type', 'paid' => 'Paid', 'free' => 'Free')
    ));

    $this->addElement('Button', 'search', array(
        'label' => 'Search',
        'type' => 'submit',
    ));

    $this->addElement('Dummy', 'loading-img-sesevent', array(
        'content' => '<img src="application/modules/Core/externals/images/loading.gif" id="sesevent-search-order-img" alt="Loading" />',
    ));
  }

}
