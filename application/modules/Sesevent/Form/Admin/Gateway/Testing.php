<?php
/**
 * SocialEngineSolutions
 *
 * @category   Application_Sesevent
 * @package    Sesevent
 * @copyright  Copyright 2015-2016 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: Testing.php 2016-07-26 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */
class Sesevent_Form_Admin_Gateway_Testing extends Payment_Form_Admin_Gateway_Abstract
{
  public function init()
  {
    parent::init();
    
    $this->setTitle('Payment Gateway: Testing');
    $this->setDescription('PAYMENT_FORM_ADMIN_GATEWAY_TESTING_DESCRIPTION');

    // Decorators
    $this->loadDefaultDecorators();
    $this->getDecorator('Description')->setOption('escape', false);
  }
}